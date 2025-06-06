<?php
/**
 * Handle form submissions from the front-end.
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Form_Handler {

    /**
     * Process the form submission.
     *
     * @since    1.0.0
     */
    public function process_submission() {
        // Check if form is submitted
        if (!isset($_POST['gpfs_submit_post']) || !isset($_POST['gpfs_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['gpfs_nonce'], 'gpfs_submit_post_nonce')) {
            wp_die(__('Security check failed. Please try again.', 'guest-post-frontend-submitter'), __('Error', 'guest-post-frontend-submitter'), array('response' => 403));
        }

        // Check honeypot field to prevent spam
        if (!empty($_POST['gpfs_website'])) {
            // This is likely a spam submission as the honeypot field was filled
            wp_redirect(add_query_arg('gpfs_error', 'spam', wp_get_referer()));
            exit;
        }
        
        // Check submission limits if enabled
        $this->check_submission_limits();

        // Verify CAPTCHA if enabled
        $options = get_option('gpfs_options', array());
        $enable_captcha = isset($options['enable_captcha']) ? $options['enable_captcha'] : 1;
        
        if ($enable_captcha) {
            if (!isset($_POST['gpfs_captcha']) || empty($_POST['gpfs_captcha'])) {
                wp_redirect(add_query_arg('gpfs_error', 'captcha_missing', wp_get_referer()));
                exit;
            }
            
            // Start session if not already started
            if (!session_id()) {
                session_start();
            }
            
            // Check if the CAPTCHA answer is correct
            $captcha_answer = isset($_SESSION['gpfs_captcha_answer']) ? intval($_SESSION['gpfs_captcha_answer']) : 0;
            $user_answer = intval($_POST['gpfs_captcha']);
            
            if ($user_answer !== $captcha_answer) {
                wp_redirect(add_query_arg('gpfs_error', 'captcha_invalid', wp_get_referer()));
                exit;
            }
            
            // Clear the CAPTCHA session variable
            unset($_SESSION['gpfs_captcha_answer']);
        }

        // Validate required fields
        $required_fields = array('gpfs_title', 'gpfs_content', 'gpfs_author_name', 'gpfs_author_email', 'gpfs_category');
        $errors = array();

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = $field;
            }
        }

        // Validate email format
        if (!empty($_POST['gpfs_author_email']) && !is_email($_POST['gpfs_author_email'])) {
            $errors[] = 'gpfs_author_email_invalid';
        }

        if (!empty($errors)) {
            wp_redirect(add_query_arg('gpfs_error', 'required', wp_get_referer()));
            exit;
        }

        // Sanitize input data
        $title = sanitize_text_field($_POST['gpfs_title']);
        $content = wp_kses_post($_POST['gpfs_content']);
        $excerpt = isset($_POST['gpfs_excerpt']) ? sanitize_textarea_field($_POST['gpfs_excerpt']) : '';
        $author_name = sanitize_text_field($_POST['gpfs_author_name']);
        $author_email = sanitize_email($_POST['gpfs_author_email']);
        $author_bio = isset($_POST['gpfs_author_bio']) ? sanitize_textarea_field($_POST['gpfs_author_bio']) : '';
        $category_id = absint($_POST['gpfs_category']);
        
        // Get settings
        $settings = get_option('gpfs_settings', array());
        
        // Determine post status based on moderation setting
        $enable_moderation = isset($settings['enable_moderation']) ? $settings['enable_moderation'] : true;
        $post_status = $enable_moderation ? 'pending' : 'publish';
        
        // Use default category if none selected
        if (empty($category_id) && !empty($settings['default_category'])) {
            $category_id = absint($settings['default_category']);
        }
        
        // Prepare post data
        $post_data = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_excerpt'  => $excerpt,
            'post_status'   => $post_status,
            'post_type'     => 'post',
            'post_author'   => 1, // Default to admin user
        );
        
        // Set category if specified
        if (!empty($category_id)) {
            $post_data['post_category'] = array($category_id);
        }
        
        // Allow filtering of post data before insertion
        $post_data = apply_filters('gpfs_pre_insert_post_data', $post_data, $_POST);
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_redirect(add_query_arg('gpfs_error', 'insert', wp_get_referer()));
            exit;
        }
        
        // Save author metadata
        update_post_meta($post_id, '_gpfs_author_name', $author_name);
        update_post_meta($post_id, '_gpfs_author_email', $author_email);
        update_post_meta($post_id, '_gpfs_author_bio', $author_bio);
        
        // Save IP address for submission limits
        update_post_meta($post_id, '_gpfs_author_ip', $this->get_user_ip());
        
        // Handle featured image if uploaded
        if (!empty($_FILES['gpfs_featured_image']['name'])) {
            $this->handle_featured_image($post_id);
        }
        
        // Handle custom fields if any
        $this->handle_custom_fields($post_id);
        
        // Fire action after successful submission
        do_action('gpfs_after_post_submission', $post_id, $_POST);
        
        // Redirect to success page
        wp_redirect(add_query_arg('gpfs_success', '1', wp_get_referer()));
        exit;
    }
    
    /**
     * Handle featured image upload.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    private function handle_featured_image($post_id) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        if (!function_exists('media_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
        }
        
        // Check file type
        $file = $_FILES['gpfs_featured_image'];
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        
        if (!in_array($file['type'], $allowed_types)) {
            // Log error or set a message
            return;
        }
        
        // Check file size (limit to 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            // Log error or set a message
            return;
        }
        
        // Handle the upload
        $attachment_id = media_handle_upload('gpfs_featured_image', $post_id);
        
        if (!is_wp_error($attachment_id)) {
            // Set as featured image
            set_post_thumbnail($post_id, $attachment_id);
            
            // Add attribution metadata if needed
            update_post_meta($attachment_id, '_gpfs_uploaded_by', get_post_meta($post_id, '_gpfs_author_name', true));
            update_post_meta($attachment_id, '_gpfs_uploaded_email', get_post_meta($post_id, '_gpfs_author_email', true));
        }
    }
    
    /**
     * Handle custom fields.
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    private function handle_custom_fields($post_id) {
        // Get custom fields from options
        $options = get_option('gpfs_options', array());
        $custom_fields = isset($options['custom_fields']) ? $options['custom_fields'] : array();
        
        if (empty($custom_fields)) {
            return;
        }
        
        foreach ($custom_fields as $field) {
            $field_name = 'gpfs_cf_' . sanitize_key($field['name']);
            
            if (isset($_POST[$field_name])) {
                $field_value = sanitize_text_field($_POST[$field_name]);
                update_post_meta($post_id, $field['name'], $field_value);
            }
        }
    }
    
    /**
     * Check submission limits based on IP address.
     *
     * @since    1.0.0
     */
    private function check_submission_limits() {
        // Get settings
        $settings = get_option('gpfs_settings', array());
        $enable_limits = isset($settings['enable_submission_limits']) ? $settings['enable_submission_limits'] : false;
        
        if (!$enable_limits) {
            return;
        }
        
        $max_submissions = isset($settings['max_submissions_per_day']) ? absint($settings['max_submissions_per_day']) : 3;
        if ($max_submissions < 1) {
            $max_submissions = 1;
        }
        
        // Get user IP address
        $user_ip = $this->get_user_ip();
        
        // Get submissions from this IP today
        $submissions_today = $this->count_submissions_by_ip($user_ip);
        
        // Check if limit exceeded
        if ($submissions_today >= $max_submissions) {
            wp_redirect(add_query_arg('gpfs_error', 'limit_exceeded', wp_get_referer()));
            exit;
        }
    }
    
    /**
     * Get the user's IP address.
     *
     * @since    1.0.0
     * @return   string    The user's IP address.
     */
    private function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Count submissions from an IP address today.
     *
     * @since    1.0.0
     * @param    string    $ip    The IP address.
     * @return   int              The number of submissions.
     */
    private function count_submissions_by_ip($ip) {
        global $wpdb;
        
        // Get today's date in the site's timezone
        $today = current_time('Y-m-d');
        
        // Query posts with this IP address from today
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'post'
            AND p.post_date >= %s
            AND pm.meta_key = '_gpfs_author_ip'
            AND pm.meta_value = %s",
            $today . ' 00:00:00',
            $ip
        ));
        
        return absint($count);
    }
}

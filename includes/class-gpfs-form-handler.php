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
        $category_id = absint($_POST['gpfs_category']);
        
        // Always save as draft regardless of plugin settings
        $post_status = 'draft';
        
        // Prepare post data
        $post_data = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_excerpt'  => $excerpt,
            'post_status'   => $post_status,
            'post_type'     => 'post',
            'post_author'   => 1, // Default to admin user
        );
        
        // Set selected category
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
            return;
        }
        
        // Handle the upload
        $attachment_id = media_handle_upload('gpfs_featured_image', $post_id);
        
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
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
}

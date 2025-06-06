<?php
/**
 * Register and handle the shortcode for the guest post submission form.
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Shortcode {

    /**
     * Register the shortcode.
     *
     * @since    1.0.0
     */
    public function register_shortcode() {
        add_shortcode('guest_post_form', array($this, 'render_form'));
    }

    /**
     * Render the submission form.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The HTML output for the form.
     */
    public function render_form($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(array(
            'title' => __('Submit a Guest Post', 'guest-post-frontend-submitter'),
            'success_message' => __('Thank you! Your post has been submitted successfully.', 'guest-post-frontend-submitter'),
            'button_text' => __('Submit Post', 'guest-post-frontend-submitter'),
            'show_excerpt' => 'yes',
            'show_featured_image' => 'yes',
            'redirect' => '',
        ), $atts, 'guest_post_form');
        
        // Check if user is logged in (if required)
        $options = get_option('gpfs_options', array());
        $require_login = isset($options['require_login']) ? $options['require_login'] : false;
        
        if ($require_login && !is_user_logged_in()) {
            return sprintf(
                '<div class="gpfs-login-required">%s</div>',
                __('You must be logged in to submit a post. Please <a href="%s">login</a> or <a href="%s">register</a>.', 'guest-post-frontend-submitter'),
                wp_login_url(get_permalink()),
                wp_registration_url()
            );
        }
        
        // Check for form submission status
        $output = '';
        
        if (isset($_GET['gpfs_success']) && $_GET['gpfs_success'] == '1') {
            $output .= sprintf(
                '<div class="gpfs-success-message">%s</div>',
                esc_html($atts['success_message'])
            );
        }
        
        if (isset($_GET['gpfs_error'])) {
            $error_type = sanitize_text_field($_GET['gpfs_error']);
            $error_message = $this->get_error_message($error_type);
            
            $output .= sprintf(
                '<div class="gpfs-error-message">%s</div>',
                esc_html($error_message)
            );
        }
        
        // Start building the form
        $output .= sprintf('<h3>%s</h3>', esc_html($atts['title']));
        
        $output .= '<form id="gpfs-submission-form" class="gpfs-form" method="post" enctype="multipart/form-data">';
        
        // Title field
        $output .= '<div class="gpfs-form-field">';
        $output .= '<label for="gpfs_title">' . __('Post Title', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        $output .= '<input type="text" id="gpfs_title" name="gpfs_title" required>';
        $output .= '</div>';
        
        // Content field with rich text editor
        $output .= '<div class="gpfs-form-field">';
        $output .= '<label for="gpfs_content">' . __('Post Content', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        
        // Initialize WordPress editor
        ob_start();
        $editor_settings = array(
            'textarea_name' => 'gpfs_content',
            'textarea_rows' => 10,
            'media_buttons' => false,
            'teeny'         => true,
            'quicktags'     => true,
        );
        wp_editor('', 'gpfs_content', $editor_settings);
        $editor_content = ob_get_clean();
        $output .= $editor_content;
        
        $output .= '</div>';
        
        // Author Name field
        $output .= '<div class="gpfs-form-field">';
        $output .= '<label for="gpfs_author_name">' . __('Author Name', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        $output .= '<input type="text" id="gpfs_author_name" name="gpfs_author_name" required>';
        $output .= '</div>';
        
        // Author Email field
        $output .= '<div class="gpfs-form-field">';
        $output .= '<label for="gpfs_author_email">' . __('Author Email', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        $output .= '<input type="email" id="gpfs_author_email" name="gpfs_author_email" required>';
        $output .= '</div>';
        
        // Category dropdown
        $output .= '<div class="gpfs-form-field">';
        $output .= '<label for="gpfs_category">' . __('Post Category', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        
        // Get all categories
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));
        
        $output .= '<select id="gpfs_category" name="gpfs_category" required>';
        $output .= '<option value="">' . __('Select a category', 'guest-post-frontend-submitter') . '</option>';
        
        foreach ($categories as $category) {
            $output .= '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
        }
        
        $output .= '</select>';
        $output .= '</div>';
        
        // Excerpt field (optional)
        if ($atts['show_excerpt'] === 'yes') {
            $output .= '<div class="gpfs-form-field">';
            $output .= '<label for="gpfs_excerpt">' . __('Excerpt', 'guest-post-frontend-submitter') . '</label>';
            $output .= '<textarea id="gpfs_excerpt" name="gpfs_excerpt" rows="3"></textarea>';
            $output .= '</div>';
        }
        
        // Featured image field (optional)
        if ($atts['show_featured_image'] === 'yes') {
            $output .= '<div class="gpfs-form-field">';
            $output .= '<label for="gpfs_featured_image">' . __('Featured Image', 'guest-post-frontend-submitter') . '</label>';
            $output .= '<input type="file" id="gpfs_featured_image" name="gpfs_featured_image" accept="image/*">';
            $output .= '</div>';
        }
        
        // Add custom fields if configured
        $custom_fields = isset($options['custom_fields']) ? $options['custom_fields'] : array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $field) {
                $field_id = 'gpfs_cf_' . sanitize_key($field['name']);
                $required = !empty($field['required']) ? ' <span class="required">*</span>' : '';
                $required_attr = !empty($field['required']) ? ' required' : '';
                
                $output .= '<div class="gpfs-form-field">';
                $output .= '<label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . $required . '</label>';
                
                switch ($field['type']) {
                    case 'text':
                        $output .= '<input type="text" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '"' . $required_attr . '>';
                        break;
                    case 'textarea':
                        $output .= '<textarea id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" rows="3"' . $required_attr . '></textarea>';
                        break;
                    case 'select':
                        $output .= '<select id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '"' . $required_attr . '>';
                        $options = explode(',', $field['options']);
                        foreach ($options as $option) {
                            $option = trim($option);
                            $output .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                        }
                        $output .= '</select>';
                        break;
                }
                
                $output .= '</div>';
            }
        }
        
        // Honeypot field to prevent spam
        $output .= '<div class="gpfs-honeypot">';
        $output .= '<input type="text" name="gpfs_website" value="" tabindex="-1" autocomplete="off">';
        $output .= '</div>';
        
        // Nonce field for security
        $output .= wp_nonce_field('gpfs_submit_post_nonce', 'gpfs_nonce', true, false);
        
        // Submit button
        $output .= '<div class="gpfs-form-field">';
        $output .= '<input type="submit" name="gpfs_submit_post" value="' . esc_attr($atts['button_text']) . '">';
        $output .= '</div>';
        
        $output .= '</form>';
        
        // Add script to initialize TinyMCE if it's not already initialized
        $output .= '<script type="text/javascript">
            jQuery(document).ready(function($) {
                if (typeof tinyMCE !== "undefined") {
                    tinyMCE.init({
                        selector: "#gpfs_content",
                        plugins: "lists link image",
                        menubar: false,
                        toolbar: "bold italic | bullist numlist | link"
                    });
                }
            });
        </script>';
        
        return $output;
    }
    
    /**
     * Get error message based on error type.
     *
     * @since    1.0.0
     * @param    string    $error_type    The type of error.
     * @return   string                   The error message.
     */
    private function get_error_message($error_type) {
        switch ($error_type) {
            case 'required':
                return __('Please fill in all required fields.', 'guest-post-frontend-submitter');
            case 'insert':
                return __('There was an error submitting your post. Please try again.', 'guest-post-frontend-submitter');
            case 'spam':
                return __('Your submission was flagged as spam. Please try again.', 'guest-post-frontend-submitter');
            case 'email':
                return __('Please enter a valid email address.', 'guest-post-frontend-submitter');
            default:
                return __('An unknown error occurred. Please try again.', 'guest-post-frontend-submitter');
        }
    }
}

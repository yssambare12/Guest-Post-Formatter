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
        
        // Register the React form shortcode
        add_shortcode('guest_post_react_form', array($this, 'render_react_form'));
    }

    /**
     * Render the form.
     *
     * @since    1.0.0
     * @param    array    $atts       Shortcode attributes.
     * @param    string   $content    Shortcode content.
     * @return   string               HTML output.
     */
    public function render_form($atts, $content = null) {
        // Extract shortcode attributes
        $atts = shortcode_atts(array(
            'title' => __('Submit a Guest Post', 'guest-post-frontend-submitter'),
            'success_message' => __('Thank you! Your post has been submitted successfully.', 'guest-post-frontend-submitter'),
            'button_text' => __('Submit Post', 'guest-post-frontend-submitter'),
            'show_excerpt' => 'yes',
            'show_featured_image' => 'yes',
        ), $atts, 'guest_post_form');
        
        // Check if user is logged in and login is required
        $options = get_option('gpfs_options', array());
        $require_login = isset($options['require_login']) ? $options['require_login'] : 0;
        
        if ($require_login && !is_user_logged_in()) {
            return '<div class="gpfs-login-required">' . __('You must be logged in to submit a post.', 'guest-post-frontend-submitter') . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('Log in', 'guest-post-frontend-submitter') . '</a></div>';
        }
        
        // Check for form submission status
        $form_success = isset($_GET['gpfs_success']) && $_GET['gpfs_success'] == '1';
        $form_error = null;
        
        if (isset($_GET['gpfs_error'])) {
            $error_type = sanitize_text_field($_GET['gpfs_error']);
            $form_error = $this->get_error_message($error_type);
        }
        
        // Start output buffering
        ob_start();
        
        // Display success message if form was submitted successfully
        if ($form_success) {
            echo '<div class="gpfs-success-message">' . esc_html($atts['success_message']) . '</div>';
        }
        
        // Display error message if there was an error
        if ($form_error) {
            echo '<div class="gpfs-error-message">' . $form_error . '</div>';
        }
        
        // Start form
        echo '<div class="gpfs-form-container">';
        echo '<h3 class="gpfs-form-title">' . esc_html($atts['title']) . '</h3>';
        echo '<form id="gpfs-submission-form" class="gpfs-form" method="post" enctype="multipart/form-data">';
        
        // Title field
        echo '<div class="gpfs-form-field">';
        echo '<label for="gpfs_title">' . __('Post Title', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        echo '<input type="text" id="gpfs_title" name="gpfs_title" required>';
        echo '</div>';
        
        // Content field
        echo '<div class="gpfs-form-field">';
        echo '<label for="gpfs_content">' . __('Post Content', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        
        // Use WordPress editor if available
        if (function_exists('wp_editor')) {
            $editor_settings = array(
                'textarea_name' => 'gpfs_content',
                'textarea_rows' => 10,
                'media_buttons' => false,
                'teeny' => true,
                'quicktags' => array('buttons' => 'strong,em,link,ul,ol,li,code'),
            );
            wp_editor('', 'gpfs_content', $editor_settings);
        } else {
            echo '<textarea id="gpfs_content" name="gpfs_content" rows="10" required></textarea>';
        }
        
        echo '</div>';
        
        // Author Name field
        echo '<div class="gpfs-form-field">';
        echo '<label for="gpfs_author_name">' . __('Author Name', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        echo '<input type="text" id="gpfs_author_name" name="gpfs_author_name" required>';
        echo '</div>';
        
        // Author Email field
        echo '<div class="gpfs-form-field">';
        echo '<label for="gpfs_author_email">' . __('Author Email', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        echo '<input type="email" id="gpfs_author_email" name="gpfs_author_email" required>';
        echo '</div>';
        
        // Author Bio field
        echo '<div class="gpfs-form-field">';
        echo '<label for="gpfs_author_bio">' . __('Author Bio', 'guest-post-frontend-submitter') . '</label>';
        echo '<textarea id="gpfs_author_bio" name="gpfs_author_bio" rows="4" placeholder="' . esc_attr__('Tell us about yourself (optional)', 'guest-post-frontend-submitter') . '"></textarea>';
        echo '</div>';
        
        // Category dropdown
        echo '<div class="gpfs-form-field">';
        echo '<label for="gpfs_category">' . __('Post Category', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
        
        // Get categories
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        
        echo '<select id="gpfs_category" name="gpfs_category" required>';
        echo '<option value="">' . __('Select a category', 'guest-post-frontend-submitter') . '</option>';
        
        foreach ($categories as $category) {
            echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        // Excerpt field (optional)
        if ($atts['show_excerpt'] === 'yes') {
            echo '<div class="gpfs-form-field">';
            echo '<label for="gpfs_excerpt">' . __('Excerpt', 'guest-post-frontend-submitter') . '</label>';
            echo '<textarea id="gpfs_excerpt" name="gpfs_excerpt" rows="3"></textarea>';
            echo '</div>';
        }
        
        // Featured image field (optional)
        if ($atts['show_featured_image'] === 'yes') {
            echo '<div class="gpfs-form-field">';
            echo '<label for="gpfs_featured_image">' . __('Featured Image', 'guest-post-frontend-submitter') . '</label>';
            echo '<input type="file" id="gpfs_featured_image" name="gpfs_featured_image" accept="image/*">';
            echo '<p class="gpfs-field-description">' . __('Upload an image to be used as the featured image for your post. Allowed formats: JPEG, PNG, GIF.', 'guest-post-frontend-submitter') . '</p>';
            echo '<div id="gpfs-image-preview-container" class="gpfs-image-preview-container"></div>';
            echo '</div>';
        }
        
        // CAPTCHA field (if enabled)
        $enable_captcha = isset($options['enable_captcha']) ? $options['enable_captcha'] : 1;
        
        if ($enable_captcha) {
            // Generate CAPTCHA
            $num1 = wp_rand(1, 10);
            $num2 = wp_rand(1, 10);
            $captcha_answer = $num1 + $num2;
            
            // Store the answer in a session
            if (!session_id()) {
                session_start();
            }
            $_SESSION['gpfs_captcha_answer'] = $captcha_answer;
            
            // Store the answer in a cookie as backup
            setcookie('gpfs_captcha_answer', $captcha_answer, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
            
            echo '<div class="gpfs-form-field gpfs-captcha-field">';
            echo '<label for="gpfs_captcha">' . __('Security Question', 'guest-post-frontend-submitter') . ' <span class="required">*</span></label>';
            echo '<div class="gpfs-captcha-question">' . sprintf(__('What is %d + %d? (Enter the number %d)', 'guest-post-frontend-submitter'), $num1, $num2, $captcha_answer) . '</div>';
            echo '<input type="number" id="gpfs_captcha" name="gpfs_captcha" value="' . $captcha_answer . '" required>';
            echo '<input type="hidden" name="gpfs_captcha_check" value="' . esc_attr($captcha_answer) . '">';
            echo '</div>';
        }
        
        // Honeypot field to prevent spam
        echo '<div class="gpfs-honeypot">';
        echo '<input type="text" name="gpfs_website" tabindex="-1" autocomplete="off">';
        echo '</div>';
        
        // WordPress nonce field
        wp_nonce_field('gpfs_submit_post_nonce', 'gpfs_nonce');
        
        // Submit button
        echo '<div class="gpfs-form-field">';
        echo '<button type="submit" name="gpfs_submit_post" value="1" class="gpfs-submit-button">' . esc_html($atts['button_text']) . '</button>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        
        // Get the buffered content
        $output = ob_get_clean();
        
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
            case 'captcha_missing':
                return __('Please answer the security question.', 'guest-post-frontend-submitter');
            case 'captcha_invalid':
                return __('The security answer is incorrect. Please try again.', 'guest-post-frontend-submitter');
            case 'limit_exceeded':
                return __('You have reached the maximum number of submissions allowed per day. Please try again tomorrow.', 'guest-post-frontend-submitter');
            default:
                return __('An unknown error occurred. Please try again.', 'guest-post-frontend-submitter');
        }
    }
    
    /**
     * Render the React form.
     *
     * @since    1.0.0
     * @param    array    $atts       Shortcode attributes.
     * @param    string   $content    Shortcode content.
     * @return   string               HTML output.
     */
    public function render_react_form($atts, $content = null) {
        // Extract shortcode attributes
        $atts = shortcode_atts(array(
            'title' => __('Submit a Guest Post', 'guest-post-frontend-submitter'),
            'success_message' => __('Thank you! Your submission has been received. We\'ll review it and get back to you soon.', 'guest-post-frontend-submitter'),
            'button_text' => __('Submit Post', 'guest-post-frontend-submitter'),
            'show_excerpt' => 'yes',
            'show_featured_image' => 'yes',
            'redirect' => '',
        ), $atts, 'guest_post_react_form');
        
        // Check for form submission status
        $form_success = isset($_GET['gpfs_success']) && $_GET['gpfs_success'] == '1';
        $form_error = null;
        
        if (isset($_GET['gpfs_error'])) {
            $error_type = sanitize_text_field($_GET['gpfs_error']);
            $form_error = $this->get_error_message($error_type);
        }
        
        // Get all categories
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));
        
        $categories_array = array();
        foreach ($categories as $category) {
            $categories_array[] = array(
                'id' => $category->term_id,
                'name' => $category->name
            );
        }
        
        // Check if CAPTCHA is enabled
        $options = get_option('gpfs_options', array());
        $enable_captcha = isset($options['enable_captcha']) ? $options['enable_captcha'] : 1;
        
        // Generate CAPTCHA question if enabled
        $captcha_question = '';
        if ($enable_captcha) {
            $num1 = wp_rand(1, 10);
            $num2 = wp_rand(1, 10);
            $captcha_answer = $num1 + $num2;
            
            // Store the answer in a session
            if (!session_id()) {
                session_start();
            }
            $_SESSION['gpfs_captcha_answer'] = $captcha_answer;
            
            $captcha_question = sprintf(__('What is %d + %d?', 'guest-post-frontend-submitter'), $num1, $num2);
        }
        
        // Prepare form configuration
        $form_config = array(
            'formTitle' => $atts['title'],
            'successMessage' => $atts['success_message'],
            'formSuccess' => $form_success,
            'formError' => $form_error,
            'showExcerpt' => $atts['show_excerpt'] === 'yes',
            'showFeaturedImage' => $atts['show_featured_image'] === 'yes',
            'captchaEnabled' => $enable_captcha,
            'captchaQuestion' => $captcha_question,
            'nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'categories' => $categories_array,
            'labels' => array(
                'title' => __('Post Title', 'guest-post-frontend-submitter'),
                'content' => __('Post Content', 'guest-post-frontend-submitter'),
                'authorName' => __('Author Name', 'guest-post-frontend-submitter'),
                'authorEmail' => __('Author Email', 'guest-post-frontend-submitter'),
                'authorBio' => __('Author Bio', 'guest-post-frontend-submitter'),
                'category' => __('Post Category', 'guest-post-frontend-submitter'),
                'excerpt' => __('Excerpt', 'guest-post-frontend-submitter'),
                'featuredImage' => __('Featured Image', 'guest-post-frontend-submitter'),
                'captcha' => __('Security Question', 'guest-post-frontend-submitter'),
                'submit' => $atts['button_text'],
                'submitting' => __('Submitting...', 'guest-post-frontend-submitter')
            ),
            'placeholders' => array(
                'authorBio' => __('Tell us about yourself (optional)', 'guest-post-frontend-submitter'),
                'category' => __('Select a category', 'guest-post-frontend-submitter')
            ),
            'descriptions' => array(
                'authorBio' => __('Share a brief bio that will be displayed with your post.', 'guest-post-frontend-submitter'),
                'excerpt' => __('A short summary of your post. If left empty, an excerpt will be generated from your content.', 'guest-post-frontend-submitter'),
                'featuredImage' => __('Upload an image to be used as the featured image for your post. Allowed formats: JPEG, PNG, GIF.', 'guest-post-frontend-submitter')
            )
        );
        
        // Create container with data attributes
        $output = '<div class="gpfs-react-form-container" data-form-config="' . esc_attr(json_encode($form_config)) . '"></div>';
        
        // Add a fallback for non-JS users
        $output .= '<noscript>';
        $output .= '<div class="gpfs-error-message">';
        $output .= __('JavaScript is required to use this form. Please enable JavaScript in your browser settings.', 'guest-post-frontend-submitter');
        $output .= '</div>';
        $output .= '</noscript>';
        
        return $output;
    }
}

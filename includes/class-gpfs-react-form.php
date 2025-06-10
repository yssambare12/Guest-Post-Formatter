<?php
/**
 * React Form Handler for Guest Post Frontend Submitter
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_React_Form {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only enqueue scripts when the shortcode is present
        $queried = get_queried_object();
        if ( $queried instanceof WP_Post &&
            ( has_shortcode( $queried->post_content, 'guest_post_form' ) ||
              has_shortcode( $queried->post_content, 'guest_post_react_form' ) )
        ) {
            // Enqueue React and ReactDOM from CDN
            wp_enqueue_script(
                'react',
                'https://unpkg.com/react@18/umd/react.production.min.js',
                array(),
                '18.2.0',
                true
            );
            
            wp_enqueue_script(
                'react-dom',
                'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js',
                array('react'),
                '18.2.0',
                true
            );
            
            // Enqueue TailwindCSS from CDN
            wp_enqueue_style(
                'tailwindcss',
                'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
                array(),
                '2.2.19'
            );
            
            // Enqueue our custom TailwindCSS styles
            wp_enqueue_style(
                'gpfs-tailwind',
                GPFS_PLUGIN_URL . 'assets/css/gpfs-tailwind.css',
                array('tailwindcss'),
                GPFS_VERSION
            );
            
            // Enqueue our React form script
            wp_enqueue_script(
                'gpfs-react-form',
                GPFS_PLUGIN_URL . 'assets/js/gpfs-react-form.js',
                array('react', 'react-dom', 'jquery'),
                GPFS_VERSION,
                true
            );
        }
    }

    /**
     * Render the React form container.
     *
     * @since    1.0.0
     * @param    array    $atts       Shortcode attributes.
     * @param    string   $content    Shortcode content.
     * @return   string               HTML output.
     */
    public function render_form_container($atts, $content = null) {
        // Extract shortcode attributes
        $atts = shortcode_atts(array(
            'title' => __('Submit a Guest Post', 'guest-post-frontend-submitter'),
            'success_message' => __('Thank you! Your submission has been received. We\'ll review it and get back to you soon.', 'guest-post-frontend-submitter'),
            'button_text' => __('Submit Post', 'guest-post-frontend-submitter'),
            'show_excerpt' => 'yes',
            'show_featured_image' => 'yes',
            'redirect' => '',
        ), $atts, 'guest_post_form');
        
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
            
            // Store the answer in a cookie as backup
            setcookie('gpfs_captcha_answer', $captcha_answer, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
            
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
}

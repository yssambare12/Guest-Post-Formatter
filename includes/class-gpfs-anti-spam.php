<?php
/**
 * Anti-spam functionality for Guest Post Frontend Submitter
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Anti_Spam {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_filter('gpfs_pre_insert_post_data', array($this, 'check_content_for_spam'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_recaptcha_scripts'));
    }

    /**
     * Enqueue reCAPTCHA scripts if enabled.
     *
     * @since    1.0.0
     */
    public function enqueue_recaptcha_scripts() {
        // Only enqueue on pages with our shortcode
        global $post;
        if (!is_a($post, 'WP_Post') || (!has_shortcode($post->post_content, 'guest_post_form') && !has_shortcode($post->post_content, 'guest_post_react_form'))) {
            return;
        }
        
        $settings = get_option('gpfs_settings', array());
        $recaptcha_enabled = isset($settings['enable_recaptcha']) ? $settings['enable_recaptcha'] : false;
        $recaptcha_site_key = isset($settings['recaptcha_site_key']) ? $settings['recaptcha_site_key'] : '';
        
        if ($recaptcha_enabled && !empty($recaptcha_site_key)) {
            wp_enqueue_script(
                'gpfs-recaptcha',
                'https://www.google.com/recaptcha/api.js',
                array(),
                null,
                true
            );
            
            wp_localize_script(
                'gpfs-recaptcha',
                'gpfs_recaptcha',
                array(
                    'site_key' => $recaptcha_site_key
                )
            );
        }
    }

    /**
     * Check if the submission is spam.
     *
     * @since    1.0.0
     * @param    array    $post_data    The post data.
     * @param    array    $form_data    The form data.
     * @return   array                  The filtered post data.
     */
    public function check_content_for_spam($post_data, $form_data) {
        $settings = get_option('gpfs_settings', array());
        
        // Check reCAPTCHA if enabled
        if (isset($settings['enable_recaptcha']) && $settings['enable_recaptcha']) {
            $this->verify_recaptcha();
        }
        
        // Check blocked domains
        if (isset($settings['blocked_domains']) && !empty($settings['blocked_domains'])) {
            $this->check_blocked_domains($form_data);
        }
        
        // Check blocked keywords
        if (isset($settings['blocked_keywords']) && !empty($settings['blocked_keywords'])) {
            $this->check_blocked_keywords($post_data);
        }
        
        // Check with OpenAI Moderation API if enabled
        if (isset($settings['enable_openai_moderation']) && $settings['enable_openai_moderation']) {
            $this->check_openai_moderation($post_data);
        }
        
        return $post_data;
    }

    /**
     * Verify reCAPTCHA response.
     *
     * @since    1.0.0
     */
    private function verify_recaptcha() {
        $settings = get_option('gpfs_settings', array());
        $recaptcha_secret = isset($settings['recaptcha_secret_key']) ? $settings['recaptcha_secret_key'] : '';
        
        if (empty($recaptcha_secret)) {
            return;
        }
        
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        if (empty($recaptcha_response)) {
            wp_die(__('reCAPTCHA verification failed. Please go back and try again.', 'guest-post-frontend-submitter'), __('Verification Failed', 'guest-post-frontend-submitter'), array('response' => 403));
        }
        
        $verify_response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));
        
        if (is_wp_error($verify_response)) {
            return; // Skip verification if there's an error
        }
        
        $response_body = wp_remote_retrieve_body($verify_response);
        $response_data = json_decode($response_body, true);
        
        if (!isset($response_data['success']) || !$response_data['success']) {
            wp_die(__('reCAPTCHA verification failed. Please go back and try again.', 'guest-post-frontend-submitter'), __('Verification Failed', 'guest-post-frontend-submitter'), array('response' => 403));
        }
    }

    /**
     * Check if the email domain is blocked.
     *
     * @since    1.0.0
     * @param    array    $form_data    The form data.
     */
    private function check_blocked_domains($form_data) {
        if (!isset($form_data['gpfs_author_email'])) {
            return;
        }
        
        $email = $form_data['gpfs_author_email'];
        $domain = substr(strrchr($email, "@"), 1);
        
        $settings = get_option('gpfs_settings', array());
        $blocked_domains = isset($settings['blocked_domains']) ? $settings['blocked_domains'] : '';
        
        if (empty($blocked_domains)) {
            return;
        }
        
        $domains_array = array_map('trim', explode("\n", $blocked_domains));
        
        foreach ($domains_array as $blocked_domain) {
            if (empty($blocked_domain)) {
                continue;
            }
            
            if ($domain === $blocked_domain || (substr($blocked_domain, 0, 1) === '.' && strpos($domain, $blocked_domain) !== false)) {
                wp_die(__('Submissions from this email domain are not allowed.', 'guest-post-frontend-submitter'), __('Submission Blocked', 'guest-post-frontend-submitter'), array('response' => 403));
            }
        }
    }

    /**
     * Check if the content contains blocked keywords.
     *
     * @since    1.0.0
     * @param    array    $post_data    The post data.
     */
    private function check_blocked_keywords($post_data) {
        $settings = get_option('gpfs_settings', array());
        $blocked_keywords = isset($settings['blocked_keywords']) ? $settings['blocked_keywords'] : '';
        
        if (empty($blocked_keywords)) {
            return;
        }
        
        $keywords_array = array_map('trim', explode("\n", $blocked_keywords));
        $content = strtolower($post_data['post_title'] . ' ' . $post_data['post_content']);
        
        foreach ($keywords_array as $keyword) {
            if (empty($keyword)) {
                continue;
            }
            
            if (strpos($content, strtolower($keyword)) !== false) {
                wp_die(__('Your submission contains content that is not allowed.', 'guest-post-frontend-submitter'), __('Submission Blocked', 'guest-post-frontend-submitter'), array('response' => 403));
            }
        }
    }

    /**
     * Check content with OpenAI Moderation API.
     *
     * @since    1.0.0
     * @param    array    $post_data    The post data.
     */
    private function check_openai_moderation($post_data) {
        $settings = get_option('gpfs_settings', array());
        $openai_api_key = isset($settings['openai_api_key']) ? $settings['openai_api_key'] : '';
        
        if (empty($openai_api_key)) {
            return;
        }
        
        $content = $post_data['post_title'] . "\n\n" . $post_data['post_content'];
        
        $response = wp_remote_post('https://api.openai.com/v1/moderations', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $openai_api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'input' => $content
            ))
        ));
        
        if (is_wp_error($response)) {
            // Log error but allow submission
            error_log('OpenAI Moderation API Error: ' . $response->get_error_message());
            return;
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if (isset($response_data['results'][0]['flagged']) && $response_data['results'][0]['flagged']) {
            // Get the categories that were flagged
            $flagged_categories = array();
            foreach ($response_data['results'][0]['categories'] as $category => $flagged) {
                if ($flagged) {
                    $flagged_categories[] = $category;
                }
            }
            
            $message = __('Your submission was flagged by our content moderation system for the following reasons:', 'guest-post-frontend-submitter');
            $message .= ' ' . implode(', ', $flagged_categories);
            $message .= '. ' . __('Please review our content guidelines and submit again.', 'guest-post-frontend-submitter');
            
            wp_die($message, __('Submission Blocked', 'guest-post-frontend-submitter'), array('response' => 403));
        }
    }
}

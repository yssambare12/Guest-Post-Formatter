<?php
/**
 * Email Marketing Integration for Guest Post Frontend Submitter
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Email_Marketing {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_filter('gpfs_form_fields', array($this, 'add_consent_checkbox'));
        add_action('gpfs_after_post_submission', array($this, 'process_email_subscription'), 10, 2);
    }

    /**
     * Add consent checkbox to the form.
     *
     * @since    1.0.0
     * @param    array    $fields    The form fields.
     * @return   array               The modified form fields.
     */
    public function add_consent_checkbox($fields) {
        $settings = get_option('gpfs_settings', array());
        $enable_email_marketing = isset($settings['enable_email_marketing']) ? $settings['enable_email_marketing'] : false;
        
        if (!$enable_email_marketing) {
            return $fields;
        }
        
        $consent_text = isset($settings['consent_text']) ? $settings['consent_text'] : __('Yes, I would like to receive updates and newsletters.', 'guest-post-frontend-submitter');
        
        $fields['consent'] = array(
            'type' => 'checkbox',
            'label' => $consent_text,
            'required' => false,
        );
        
        return $fields;
    }

    /**
     * Process email subscription if consent was given.
     *
     * @since    1.0.0
     * @param    int      $post_id     The post ID.
     * @param    array    $form_data   The form data.
     */
    public function process_email_subscription($post_id, $form_data) {
        $settings = get_option('gpfs_settings', array());
        $enable_email_marketing = isset($settings['enable_email_marketing']) ? $settings['enable_email_marketing'] : false;
        
        if (!$enable_email_marketing) {
            return;
        }
        
        // Check if consent was given
        $consent = isset($form_data['gpfs_consent']) ? true : false;
        
        if (!$consent) {
            return;
        }
        
        // Save consent to post meta
        update_post_meta($post_id, '_gpfs_marketing_consent', 'yes');
        
        // Get author information
        $author_name = get_post_meta($post_id, '_gpfs_author_name', true);
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);
        $author_bio = get_post_meta($post_id, '_gpfs_author_bio', true);
        
        // Determine which service to use
        $service = isset($settings['email_marketing_service']) ? $settings['email_marketing_service'] : '';
        
        switch ($service) {
            case 'mailchimp':
                $this->subscribe_to_mailchimp($author_email, $author_name, $post_id);
                break;
                
            case 'convertkit':
                $this->subscribe_to_convertkit($author_email, $author_name, $post_id);
                break;
                
            default:
                // No service selected or custom service
                do_action('gpfs_email_marketing_subscription', $author_email, $author_name, $post_id, $form_data);
                break;
        }
    }

    /**
     * Subscribe to Mailchimp.
     *
     * @since    1.0.0
     * @param    string   $email      The email address.
     * @param    string   $name       The subscriber name.
     * @param    int      $post_id    The post ID.
     */
    private function subscribe_to_mailchimp($email, $name, $post_id) {
        $settings = get_option('gpfs_settings', array());
        $api_key = isset($settings['mailchimp_api_key']) ? $settings['mailchimp_api_key'] : '';
        $list_id = isset($settings['mailchimp_list_id']) ? $settings['mailchimp_list_id'] : '';
        
        if (empty($api_key) || empty($list_id)) {
            return;
        }
        
        // Parse API key to get data center
        $data_center = substr(strstr($api_key, '-'), 1);
        
        // Prepare data
        $name_parts = explode(' ', $name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        
        $data = array(
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => array(
                'FNAME' => $first_name,
                'LNAME' => $last_name
            )
        );
        
        // Add tags if configured
        $tags = isset($settings['mailchimp_tags']) ? $settings['mailchimp_tags'] : '';
        if (!empty($tags)) {
            $tags_array = array_map('trim', explode(',', $tags));
            $data['tags'] = $tags_array;
        }
        
        // Make API request
        $url = "https://{$data_center}.api.mailchimp.com/3.0/lists/{$list_id}/members/";
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data)
        ));
        
        // Log response for debugging
        if (is_wp_error($response)) {
            error_log('Mailchimp API Error: ' . $response->get_error_message());
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log('Mailchimp API Error: ' . wp_remote_retrieve_body($response));
            }
        }
    }

    /**
     * Subscribe to ConvertKit.
     *
     * @since    1.0.0
     * @param    string   $email      The email address.
     * @param    string   $name       The subscriber name.
     * @param    int      $post_id    The post ID.
     */
    private function subscribe_to_convertkit($email, $name, $post_id) {
        $settings = get_option('gpfs_settings', array());
        $api_key = isset($settings['convertkit_api_key']) ? $settings['convertkit_api_key'] : '';
        $form_id = isset($settings['convertkit_form_id']) ? $settings['convertkit_form_id'] : '';
        
        if (empty($api_key) || empty($form_id)) {
            return;
        }
        
        // Prepare data
        $data = array(
            'api_key' => $api_key,
            'email' => $email,
            'first_name' => $name
        );
        
        // Add tags if configured
        $tags = isset($settings['convertkit_tags']) ? $settings['convertkit_tags'] : '';
        if (!empty($tags)) {
            $tags_array = array_map('trim', explode(',', $tags));
            $data['tags'] = $tags_array;
        }
        
        // Make API request
        $url = "https://api.convertkit.com/v3/forms/{$form_id}/subscribe";
        
        $response = wp_remote_post($url, array(
            'body' => $data
        ));
        
        // Log response for debugging
        if (is_wp_error($response)) {
            error_log('ConvertKit API Error: ' . $response->get_error_message());
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log('ConvertKit API Error: ' . wp_remote_retrieve_body($response));
            }
        }
    }
}

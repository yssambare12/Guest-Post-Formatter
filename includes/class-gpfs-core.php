<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      GPFS_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->loader = new GPFS_Loader();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Add admin menu, settings, etc.
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Initialize shortcode
        $shortcode = new GPFS_Shortcode();
        $this->loader->add_action('init', $shortcode, 'register_shortcode');
        
        // Initialize form handler
        $form_handler = new GPFS_Form_Handler();
        $this->loader->add_action('init', $form_handler, 'process_submission');
        
        // Initialize notification system
        $notification = new GPFS_Notification();
        $this->loader->add_action('gpfs_after_post_submission', $notification, 'send_admin_notification', 10, 2);
        
        // Register AJAX handlers for approve/reject actions
        $this->loader->add_action('wp_ajax_gpfs_approve_post', $notification, 'ajax_approve_post');
        $this->loader->add_action('wp_ajax_nopriv_gpfs_approve_post', $notification, 'ajax_approve_post');
        $this->loader->add_action('wp_ajax_gpfs_reject_post', $notification, 'ajax_reject_post');
        $this->loader->add_action('wp_ajax_nopriv_gpfs_reject_post', $notification, 'ajax_reject_post');
    }

    /**
     * Add admin menu for the plugin.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_options_page(
            __('Guest Post Frontend Submitter', 'guest-post-frontend-submitter'),
            __('Guest Post Submitter', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-frontend-submitter',
            array($this, 'display_admin_page')
        );
    }

    /**
     * Register settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'gpfs_settings',
            'gpfs_options',
            array($this, 'validate_settings')
        );

        // General Settings Section
        add_settings_section(
            'gpfs_general_settings',
            __('General Settings', 'guest-post-frontend-submitter'),
            array($this, 'general_settings_callback'),
            'guest-post-frontend-submitter'
        );

        add_settings_field(
            'post_status',
            __('Default Post Status', 'guest-post-frontend-submitter'),
            array($this, 'post_status_callback'),
            'guest-post-frontend-submitter',
            'gpfs_general_settings'
        );

        add_settings_field(
            'post_category',
            __('Default Category', 'guest-post-frontend-submitter'),
            array($this, 'post_category_callback'),
            'guest-post-frontend-submitter',
            'gpfs_general_settings'
        );
        
        // Email Notification Settings Section
        add_settings_section(
            'gpfs_email_settings',
            __('Email Notification Settings', 'guest-post-frontend-submitter'),
            array($this, 'email_settings_callback'),
            'guest-post-frontend-submitter'
        );
        
        add_settings_field(
            'enable_notifications',
            __('Email Notifications', 'guest-post-frontend-submitter'),
            array($this, 'notifications_callback'),
            'guest-post-frontend-submitter',
            'gpfs_email_settings'
        );
        
        add_settings_field(
            'notification_email',
            __('Notification Email', 'guest-post-frontend-submitter'),
            array($this, 'notification_email_callback'),
            'guest-post-frontend-submitter',
            'gpfs_email_settings'
        );
        
        add_settings_field(
            'email_subject',
            __('Email Subject Template', 'guest-post-frontend-submitter'),
            array($this, 'email_subject_callback'),
            'guest-post-frontend-submitter',
            'gpfs_email_settings'
        );
        
        add_settings_field(
            'email_message',
            __('Email Message Template', 'guest-post-frontend-submitter'),
            array($this, 'email_message_callback'),
            'guest-post-frontend-submitter',
            'gpfs_email_settings'
        );
        
        // Anti-Spam Settings Section
        add_settings_section(
            'gpfs_spam_settings',
            __('Anti-Spam Settings', 'guest-post-frontend-submitter'),
            array($this, 'spam_settings_callback'),
            'guest-post-frontend-submitter'
        );
        
        add_settings_field(
            'enable_captcha',
            __('CAPTCHA Protection', 'guest-post-frontend-submitter'),
            array($this, 'enable_captcha_callback'),
            'guest-post-frontend-submitter',
            'gpfs_spam_settings'
        );
    }

    /**
     * Display the admin page.
     *
     * @since    1.0.0
     */
    public function display_admin_page() {
        include_once GPFS_PLUGIN_DIR . 'admin/partials/admin-display.php';
    }

    /**
     * General settings section callback.
     *
     * @since    1.0.0
     */
    public function general_settings_callback() {
        echo '<p>' . __('Configure the general settings for the guest post submission form.', 'guest-post-frontend-submitter') . '</p>';
    }

    /**
     * Post status field callback.
     *
     * @since    1.0.0
     */
    public function post_status_callback() {
        $options = get_option('gpfs_options');
        $post_status = isset($options['post_status']) ? $options['post_status'] : 'pending';
        ?>
        <select name="gpfs_options[post_status]">
            <option value="pending" <?php selected($post_status, 'pending'); ?>><?php _e('Pending', 'guest-post-frontend-submitter'); ?></option>
            <option value="draft" <?php selected($post_status, 'draft'); ?>><?php _e('Draft', 'guest-post-frontend-submitter'); ?></option>
            <option value="publish" <?php selected($post_status, 'publish'); ?>><?php _e('Published', 'guest-post-frontend-submitter'); ?></option>
        </select>
        <p class="description"><?php _e('Select the default status for submitted posts.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Post category field callback.
     *
     * @since    1.0.0
     */
    public function post_category_callback() {
        $options = get_option('gpfs_options');
        $post_category = isset($options['post_category']) ? $options['post_category'] : '';
        
        wp_dropdown_categories(array(
            'name' => 'gpfs_options[post_category]',
            'selected' => $post_category,
            'show_option_none' => __('Select Category', 'guest-post-frontend-submitter'),
            'option_none_value' => '',
        ));
        
        echo '<p class="description">' . __('Select the default category for submitted posts.', 'guest-post-frontend-submitter') . '</p>';
    }
    
    /**
     * Notifications field callback.
     *
     * @since    1.0.0
     */
    public function notifications_callback() {
        $options = get_option('gpfs_options');
        $enable_notifications = isset($options['enable_notifications']) ? $options['enable_notifications'] : 1;
        ?>
        <label>
            <input type="checkbox" name="gpfs_options[enable_notifications]" value="1" <?php checked(1, $enable_notifications); ?>>
            <?php _e('Send email notifications when new guest posts are submitted', 'guest-post-frontend-submitter'); ?>
        </label>
        <p class="description"><?php _e('Emails include links to approve or reject the submission.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }
    
    /**
     * Notification email field callback.
     *
     * @since    1.0.0
     */
    public function notification_email_callback() {
        $options = get_option('gpfs_options');
        $notification_email = isset($options['notification_email']) ? $options['notification_email'] : get_option('admin_email');
        ?>
        <input type="email" name="gpfs_options[notification_email]" value="<?php echo esc_attr($notification_email); ?>" class="regular-text">
        <p class="description"><?php _e('Email address to receive notifications. Default is the admin email.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }
    
    /**
     * Email settings section callback.
     *
     * @since    1.0.0
     */
    public function email_settings_callback() {
        echo '<p>' . __('Configure email notification settings for guest post submissions.', 'guest-post-frontend-submitter') . '</p>';
    }
    
    /**
     * Spam settings section callback.
     *
     * @since    1.0.0
     */
    public function spam_settings_callback() {
        echo '<p>' . __('Configure anti-spam settings to protect your form from unwanted submissions.', 'guest-post-frontend-submitter') . '</p>';
    }
    
    /**
     * Enable CAPTCHA field callback.
     *
     * @since    1.0.0
     */
    public function enable_captcha_callback() {
        $options = get_option('gpfs_options');
        $enable_captcha = isset($options['enable_captcha']) ? $options['enable_captcha'] : 1;
        ?>
        <label>
            <input type="checkbox" name="gpfs_options[enable_captcha]" value="1" <?php checked(1, $enable_captcha); ?>>
            <?php _e('Enable math CAPTCHA to prevent spam submissions', 'guest-post-frontend-submitter'); ?>
        </label>
        <p class="description"><?php _e('Adds a simple math question that users must answer correctly.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }
    
    /**
     * Email subject template field callback.
     *
     * @since    1.0.0
     */
    public function email_subject_callback() {
        $options = get_option('gpfs_options');
        $default_subject = __('[%site_name%] New Guest Post Submission: "%post_title%"', 'guest-post-frontend-submitter');
        $email_subject = isset($options['email_subject']) ? $options['email_subject'] : $default_subject;
        ?>
        <input type="text" name="gpfs_options[email_subject]" value="<?php echo esc_attr($email_subject); ?>" class="large-text">
        <p class="description">
            <?php _e('Available placeholders:', 'guest-post-frontend-submitter'); ?><br>
            <code>%site_name%</code> - <?php _e('Your website name', 'guest-post-frontend-submitter'); ?><br>
            <code>%post_title%</code> - <?php _e('The submitted post title', 'guest-post-frontend-submitter'); ?>
        </p>
        <?php
    }
    
    /**
     * Email message template field callback.
     *
     * @since    1.0.0
     */
    public function email_message_callback() {
        $options = get_option('gpfs_options');
        $default_message = __('A new guest post has been submitted to your site %site_name%.

Post Details:
-------------
Title: %post_title%
Author: %author_name% (%author_email%)
Author Bio: %author_bio%
Submission Date: %submission_date%

You can view the full post in your WordPress admin:
%edit_link%

Quick Actions:
-------------
Approve: %approve_link%
Reject: %reject_link%

This email was sent from your website %site_url%.', 'guest-post-frontend-submitter');
        
        $email_message = isset($options['email_message']) ? $options['email_message'] : $default_message;
        ?>
        <textarea name="gpfs_options[email_message]" rows="15" class="large-text code"><?php echo esc_textarea($email_message); ?></textarea>
        <p class="description">
            <?php _e('Available placeholders:', 'guest-post-frontend-submitter'); ?><br>
            <code>%site_name%</code> - <?php _e('Your website name', 'guest-post-frontend-submitter'); ?><br>
            <code>%site_url%</code> - <?php _e('Your website URL', 'guest-post-frontend-submitter'); ?><br>
            <code>%post_title%</code> - <?php _e('The submitted post title', 'guest-post-frontend-submitter'); ?><br>
            <code>%author_name%</code> - <?php _e('The author\'s name', 'guest-post-frontend-submitter'); ?><br>
            <code>%author_email%</code> - <?php _e('The author\'s email', 'guest-post-frontend-submitter'); ?><br>
            <code>%author_bio%</code> - <?php _e('The author\'s bio', 'guest-post-frontend-submitter'); ?><br>
            <code>%submission_date%</code> - <?php _e('The date and time of submission', 'guest-post-frontend-submitter'); ?><br>
            <code>%edit_link%</code> - <?php _e('Link to edit the post in admin', 'guest-post-frontend-submitter'); ?><br>
            <code>%approve_link%</code> - <?php _e('Link to approve the post', 'guest-post-frontend-submitter'); ?><br>
            <code>%reject_link%</code> - <?php _e('Link to reject the post', 'guest-post-frontend-submitter'); ?>
        </p>
        <?php
    }

    /**
     * Validate settings.
     *
     * @since    1.0.0
     * @param    array    $input    The input options.
     * @return   array              The validated options.
     */
    public function validate_settings($input) {
        $output = array();
        
        if (isset($input['post_status'])) {
            $output['post_status'] = sanitize_text_field($input['post_status']);
        }
        
        if (isset($input['post_category'])) {
            $output['post_category'] = absint($input['post_category']);
        }
        
        $output['enable_notifications'] = isset($input['enable_notifications']) ? 1 : 0;
        $output['enable_captcha'] = isset($input['enable_captcha']) ? 1 : 0;
        
        if (isset($input['notification_email']) && is_email($input['notification_email'])) {
            $output['notification_email'] = sanitize_email($input['notification_email']);
        } else {
            $output['notification_email'] = get_option('admin_email');
        }
        
        if (isset($input['email_subject'])) {
            $output['email_subject'] = wp_kses_post($input['email_subject']);
        }
        
        if (isset($input['email_message'])) {
            $output['email_message'] = wp_kses_post($input['email_message']);
        }
        
        return $output;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'guest-post-frontend-submitter',
            GPFS_PLUGIN_URL . 'assets/css/guest-post-frontend-submitter-public.css',
            array(),
            GPFS_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'guest-post-frontend-submitter',
            GPFS_PLUGIN_URL . 'assets/js/guest-post-frontend-submitter-public.js',
            array('jquery'),
            GPFS_VERSION,
            false
        );
        
        wp_localize_script(
            'guest-post-frontend-submitter',
            'gpfs_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gpfs_nonce'),
                'strings' => array(
                    'invalid_file_type' => __('Invalid file type. Please upload a JPEG, PNG, or GIF image.', 'guest-post-frontend-submitter'),
                    'file_too_large' => __('File is too large. Maximum size is 5MB.', 'guest-post-frontend-submitter'),
                    'remove_image' => __('Remove image', 'guest-post-frontend-submitter')
                )
            )
        );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
}

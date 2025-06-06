<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/admin
 * @author     Your Name <email@example.com>
 */
class GPFS_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Enqueue admin scripts and styles.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_scripts($hook) {
        // Only enqueue on our plugin pages
        if (strpos($hook, 'guest-post') !== false) {
            wp_enqueue_style('gpfs-admin-css', GPFS_PLUGIN_URL . 'admin/css/gpfs-admin.css', array(), GPFS_VERSION);
            wp_enqueue_script('gpfs-admin-js', GPFS_PLUGIN_URL . 'admin/js/gpfs-admin.js', array('jquery'), GPFS_VERSION, true);
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main settings page
        add_menu_page(
            __('Guest Post Submitter', 'guest-post-frontend-submitter'),
            __('Guest Post Submitter', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-submitter',
            array($this, 'display_dashboard_page'),
            'dashicons-welcome-write-blog',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'guest-post-submitter',
            __('Dashboard', 'guest-post-frontend-submitter'),
            __('Dashboard', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-submitter',
            array($this, 'display_dashboard_page')
        );
        
        // Submissions submenu
        add_submenu_page(
            'guest-post-submitter',
            __('Submissions', 'guest-post-frontend-submitter'),
            __('Submissions', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-submissions',
            array($this, 'display_submissions_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'guest-post-submitter',
            __('Settings', 'guest-post-frontend-submitter'),
            __('Settings', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-settings',
            array($this, 'display_plugin_admin_page')
        );
        
        // Documentation submenu
        add_submenu_page(
            'guest-post-submitter',
            __('Documentation', 'guest-post-frontend-submitter'),
            __('Documentation', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-documentation',
            array($this, 'display_documentation_page')
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once('partials/gpfs-admin-display.php');
    }

    /**
     * Display the dashboard page.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        include_once('partials/gpfs-dashboard-display.php');
    }
    
    /**
     * Display the submissions page.
     *
     * @since    1.0.0
     */
    public function display_submissions_page() {
        include_once('partials/gpfs-submissions-display.php');
    }
    
    /**
     * Display the documentation page.
     *
     * @since    1.0.0
     */
    public function display_documentation_page() {
        include_once('partials/gpfs-documentation-display.php');
    }
    
    /**
     * Add dashboard widget for recent submissions.
     *
     * @since    1.0.0
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'gpfs_recent_submissions',
            __('Recent Guest Post Submissions', 'guest-post-frontend-submitter'),
            array($this, 'display_dashboard_widget')
        );
    }
    
    /**
     * Display the dashboard widget content.
     *
     * @since    1.0.0
     */
    public function display_dashboard_widget() {
        // Get recent submissions (pending posts with guest post meta)
        $recent_submissions = get_posts(array(
            'post_type' => 'post',
            'post_status' => array('pending', 'draft'),
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_gpfs_author_name',
                    'compare' => 'EXISTS',
                ),
            ),
        ));
        
        if (empty($recent_submissions)) {
            echo '<p>' . __('No recent guest post submissions.', 'guest-post-frontend-submitter') . '</p>';
            return;
        }
        
        echo '<ul class="gpfs-recent-submissions">';
        
        foreach ($recent_submissions as $post) {
            $author_name = get_post_meta($post->ID, '_gpfs_author_name', true);
            $author_email = get_post_meta($post->ID, '_gpfs_author_email', true);
            
            // Create nonces for quick actions
            $approve_nonce = wp_create_nonce('gpfs_approve_post_' . $post->ID);
            $reject_nonce = wp_create_nonce('gpfs_reject_post_' . $post->ID);
            
            // Create action URLs
            $admin_url = admin_url('admin-ajax.php');
            $approve_url = add_query_arg(array(
                'action' => 'gpfs_approve_post',
                'post_id' => $post->ID,
                'nonce' => $approve_nonce,
                'redirect' => 'dashboard'
            ), $admin_url);
            
            $reject_url = add_query_arg(array(
                'action' => 'gpfs_reject_post',
                'post_id' => $post->ID,
                'nonce' => $reject_nonce,
                'redirect' => 'dashboard'
            ), $admin_url);
            
            echo '<li>';
            echo '<strong><a href="' . get_edit_post_link($post->ID) . '">' . esc_html($post->post_title) . '</a></strong>';
            echo '<div class="submission-meta">';
            echo '<span class="author">' . esc_html($author_name) . ' (' . esc_html($author_email) . ')</span>';
            echo '<span class="date">' . get_the_date('', $post->ID) . '</span>';
            echo '</div>';
            echo '<div class="row-actions">';
            echo '<span class="approve"><a href="' . esc_url($approve_url) . '">' . __('Approve', 'guest-post-frontend-submitter') . '</a> | </span>';
            echo '<span class="reject"><a href="' . esc_url($reject_url) . '">' . __('Reject', 'guest-post-frontend-submitter') . '</a> | </span>';
            echo '<span class="edit"><a href="' . get_edit_post_link($post->ID) . '">' . __('Edit', 'guest-post-frontend-submitter') . '</a></span>';
            echo '</div>';
            echo '</li>';
        }
        
        echo '</ul>';
        
        // Add link to all submissions
        echo '<p class="gpfs-view-all"><a href="' . admin_url('admin.php?page=guest-post-submissions') . '">' . __('View all submissions', 'guest-post-frontend-submitter') . '</a></p>';
        
        // Add some basic styling
        echo '<style>
            .gpfs-recent-submissions {
                margin: 0;
                padding: 0;
            }
            .gpfs-recent-submissions li {
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 1px solid #eee;
            }
            .gpfs-recent-submissions li:last-child {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }
            .submission-meta {
                color: #777;
                font-size: 12px;
                margin: 3px 0;
            }
            .submission-meta .author {
                margin-right: 10px;
            }
            .row-actions {
                font-size: 12px;
                visibility: hidden;
            }
            li:hover .row-actions {
                visibility: visible;
            }
            .gpfs-view-all {
                margin: 10px 0 0;
                text-align: right;
            }
        </style>';
    }

    /**
     * Register all settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register settings
        register_setting(
            'gpfs_settings_group',
            'gpfs_settings',
            array($this, 'sanitize_settings')
        );

        // General Settings Section
        add_settings_section(
            'gpfs_general_settings',
            __('General Settings', 'guest-post-frontend-submitter'),
            array($this, 'general_settings_section_callback'),
            'guest-post-submissions'
        );

        // Default Category Setting
        add_settings_field(
            'default_category',
            __('Default Category', 'guest-post-frontend-submitter'),
            array($this, 'default_category_callback'),
            'guest-post-submissions',
            'gpfs_general_settings'
        );

        // Moderation Toggle Setting
        add_settings_field(
            'enable_moderation',
            __('Enable Moderation', 'guest-post-frontend-submitter'),
            array($this, 'enable_moderation_callback'),
            'guest-post-submissions',
            'gpfs_general_settings'
        );

        // Submission Limits Section
        add_settings_section(
            'gpfs_submission_limits',
            __('Submission Limits', 'guest-post-frontend-submitter'),
            array($this, 'submission_limits_section_callback'),
            'guest-post-submissions'
        );

        // Enable Submission Limits
        add_settings_field(
            'enable_submission_limits',
            __('Enable Submission Limits', 'guest-post-frontend-submitter'),
            array($this, 'enable_submission_limits_callback'),
            'guest-post-submissions',
            'gpfs_submission_limits'
        );

        // Max Submissions Per Day
        add_settings_field(
            'max_submissions_per_day',
            __('Max Submissions Per Day', 'guest-post-frontend-submitter'),
            array($this, 'max_submissions_per_day_callback'),
            'guest-post-submissions',
            'gpfs_submission_limits'
        );

        // Email Templates Section
        add_settings_section(
            'gpfs_email_templates',
            __('Email Templates', 'guest-post-frontend-submitter'),
            array($this, 'email_templates_section_callback'),
            'guest-post-submissions'
        );

        // Admin Notification Email Subject
        add_settings_field(
            'admin_notification_subject',
            __('Admin Notification Subject', 'guest-post-frontend-submitter'),
            array($this, 'admin_notification_subject_callback'),
            'guest-post-submissions',
            'gpfs_email_templates'
        );

        // Admin Notification Email Body
        add_settings_field(
            'admin_notification_body',
            __('Admin Notification Body', 'guest-post-frontend-submitter'),
            array($this, 'admin_notification_body_callback'),
            'guest-post-submissions',
            'gpfs_email_templates'
        );

        // Approval Email Subject
        add_settings_field(
            'approval_email_subject',
            __('Approval Email Subject', 'guest-post-frontend-submitter'),
            array($this, 'approval_email_subject_callback'),
            'guest-post-submissions',
            'gpfs_email_templates'
        );

        // Approval Email Body
        add_settings_field(
            'approval_email_body',
            __('Approval Email Body', 'guest-post-frontend-submitter'),
            array($this, 'approval_email_body_callback'),
            'guest-post-submissions',
            'gpfs_email_templates'
        );

        // Rejection Email Subject
        add_settings_field(
            'rejection_email_subject',
            __('Rejection Email Subject', 'guest-post-frontend-submitter'),
            array($this, 'rejection_email_subject_callback'),
            'guest-post-submissions',
            'gpfs_email_templates'
        );

        // Rejection Email Body
        add_settings_field(
            'rejection_email_body',
            __('Rejection Email Body', 'guest-post-frontend-submitter'),
            array($this, 'rejection_email_body_callback'),
            'guest-post-submissions',
            'gpfs_email_templates'
        );
    }

    /**
     * Sanitize each setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys.
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();

        // General Settings
        if (isset($input['default_category'])) {
            $sanitized_input['default_category'] = absint($input['default_category']);
        }

        if (isset($input['enable_moderation'])) {
            $sanitized_input['enable_moderation'] = (bool) $input['enable_moderation'];
        } else {
            $sanitized_input['enable_moderation'] = false;
        }

        // Submission Limits
        if (isset($input['enable_submission_limits'])) {
            $sanitized_input['enable_submission_limits'] = (bool) $input['enable_submission_limits'];
        } else {
            $sanitized_input['enable_submission_limits'] = false;
        }

        if (isset($input['max_submissions_per_day'])) {
            $sanitized_input['max_submissions_per_day'] = absint($input['max_submissions_per_day']);
            if ($sanitized_input['max_submissions_per_day'] < 1) {
                $sanitized_input['max_submissions_per_day'] = 1;
            }
        }

        // Email Templates
        if (isset($input['admin_notification_subject'])) {
            $sanitized_input['admin_notification_subject'] = sanitize_text_field($input['admin_notification_subject']);
        }

        if (isset($input['admin_notification_body'])) {
            $sanitized_input['admin_notification_body'] = wp_kses_post($input['admin_notification_body']);
        }

        if (isset($input['approval_email_subject'])) {
            $sanitized_input['approval_email_subject'] = sanitize_text_field($input['approval_email_subject']);
        }

        if (isset($input['approval_email_body'])) {
            $sanitized_input['approval_email_body'] = wp_kses_post($input['approval_email_body']);
        }

        if (isset($input['rejection_email_subject'])) {
            $sanitized_input['rejection_email_subject'] = sanitize_text_field($input['rejection_email_subject']);
        }

        if (isset($input['rejection_email_body'])) {
            $sanitized_input['rejection_email_body'] = wp_kses_post($input['rejection_email_body']);
        }

        return $sanitized_input;
    }

    /**
     * General Settings section callback.
     */
    public function general_settings_section_callback() {
        echo '<p>' . __('Configure general settings for guest post submissions.', 'guest-post-frontend-submitter') . '</p>';
    }

    /**
     * Default Category field callback.
     */
    public function default_category_callback() {
        $options = get_option('gpfs_settings');
        $default_category = isset($options['default_category']) ? $options['default_category'] : '';

        wp_dropdown_categories(array(
            'name' => 'gpfs_settings[default_category]',
            'selected' => $default_category,
            'show_option_none' => __('Select a category', 'guest-post-frontend-submitter'),
            'option_none_value' => '',
            'hide_empty' => 0,
        ));
        echo '<p class="description">' . __('Choose a default category for guest posts if none is selected in the form.', 'guest-post-frontend-submitter') . '</p>';
    }

    /**
     * Enable Moderation field callback.
     */
    public function enable_moderation_callback() {
        $options = get_option('gpfs_settings');
        $enable_moderation = isset($options['enable_moderation']) ? $options['enable_moderation'] : true;
        ?>
        <label>
            <input type="checkbox" name="gpfs_settings[enable_moderation]" value="1" <?php checked(1, $enable_moderation); ?>>
            <?php _e('Enable moderation for guest posts', 'guest-post-frontend-submitter'); ?>
        </label>
        <p class="description"><?php _e('If enabled, guest posts will be saved as pending for review. If disabled, posts will be published directly.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Submission Limits section callback.
     */
    public function submission_limits_section_callback() {
        echo '<p>' . __('Configure limits for guest post submissions.', 'guest-post-frontend-submitter') . '</p>';
    }

    /**
     * Enable Submission Limits field callback.
     */
    public function enable_submission_limits_callback() {
        $options = get_option('gpfs_settings');
        $enable_submission_limits = isset($options['enable_submission_limits']) ? $options['enable_submission_limits'] : false;
        ?>
        <label>
            <input type="checkbox" name="gpfs_settings[enable_submission_limits]" value="1" <?php checked(1, $enable_submission_limits); ?>>
            <?php _e('Enable submission limits by IP address', 'guest-post-frontend-submitter'); ?>
        </label>
        <p class="description"><?php _e('If enabled, users will be limited to a maximum number of submissions per day.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Max Submissions Per Day field callback.
     */
    public function max_submissions_per_day_callback() {
        $options = get_option('gpfs_settings');
        $max_submissions = isset($options['max_submissions_per_day']) ? $options['max_submissions_per_day'] : 3;
        ?>
        <input type="number" name="gpfs_settings[max_submissions_per_day]" value="<?php echo esc_attr($max_submissions); ?>" min="1" step="1">
        <p class="description"><?php _e('Maximum number of submissions allowed per IP address per day.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Email Templates section callback.
     */
    public function email_templates_section_callback() {
        echo '<p>' . __('Customize email templates for notifications. You can use the following merge tags:', 'guest-post-frontend-submitter') . '</p>';
        echo '<ul class="gpfs-merge-tags">';
        echo '<li><code>{site_name}</code> - ' . __('Your website name', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{site_url}</code> - ' . __('Your website URL', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{post_title}</code> - ' . __('The submitted post title', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{post_content}</code> - ' . __('The submitted post content', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{author_name}</code> - ' . __('The author\'s name', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{author_email}</code> - ' . __('The author\'s email', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{author_bio}</code> - ' . __('The author\'s bio', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{submission_date}</code> - ' . __('The date and time of submission', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{edit_link}</code> - ' . __('Link to edit the post in admin', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{post_link}</code> - ' . __('Link to view the published post', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{approve_link}</code> - ' . __('Link to approve the post (admin notification only)', 'guest-post-frontend-submitter') . '</li>';
        echo '<li><code>{reject_link}</code> - ' . __('Link to reject the post (admin notification only)', 'guest-post-frontend-submitter') . '</li>';
        echo '</ul>';
    }

    /**
     * Admin Notification Subject field callback.
     */
    public function admin_notification_subject_callback() {
        $options = get_option('gpfs_settings');
        $default_subject = '[{site_name}] New Guest Post Submission: "{post_title}"';
        $subject = isset($options['admin_notification_subject']) ? $options['admin_notification_subject'] : $default_subject;
        ?>
        <input type="text" name="gpfs_settings[admin_notification_subject]" value="<?php echo esc_attr($subject); ?>" class="large-text">
        <p class="description"><?php _e('Subject line for the email sent to admin when a new guest post is submitted.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Admin Notification Body field callback.
     */
    public function admin_notification_body_callback() {
        $options = get_option('gpfs_settings');
        $default_body = "A new guest post has been submitted to your site {site_name}.\n\n";
        $default_body .= "Post Details:\n";
        $default_body .= "-------------\n";
        $default_body .= "Title: {post_title}\n";
        $default_body .= "Author: {author_name} ({author_email})\n";
        $default_body .= "Author Bio: {author_bio}\n";
        $default_body .= "Submission Date: {submission_date}\n\n";
        $default_body .= "You can view the full post in your WordPress admin:\n";
        $default_body .= "{edit_link}\n\n";
        $default_body .= "Quick Actions:\n";
        $default_body .= "-------------\n";
        $default_body .= "Approve: {approve_link}\n";
        $default_body .= "Reject: {reject_link}\n\n";
        $default_body .= "This email was sent from your website {site_url}.";

        $body = isset($options['admin_notification_body']) ? $options['admin_notification_body'] : $default_body;
        ?>
        <textarea name="gpfs_settings[admin_notification_body]" rows="15" class="large-text code"><?php echo esc_textarea($body); ?></textarea>
        <p class="description"><?php _e('Email body for the notification sent to admin when a new guest post is submitted.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Approval Email Subject field callback.
     */
    public function approval_email_subject_callback() {
        $options = get_option('gpfs_settings');
        $default_subject = '[{site_name}] Your Guest Post Has Been Approved';
        $subject = isset($options['approval_email_subject']) ? $options['approval_email_subject'] : $default_subject;
        ?>
        <input type="text" name="gpfs_settings[approval_email_subject]" value="<?php echo esc_attr($subject); ?>" class="large-text">
        <p class="description"><?php _e('Subject line for the email sent to the author when their post is approved.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Approval Email Body field callback.
     */
    public function approval_email_body_callback() {
        $options = get_option('gpfs_settings');
        $default_body = "Hello {author_name},\n\n";
        $default_body .= "Great news! Your guest post \"{post_title}\" has been approved and published on {site_name}.\n\n";
        $default_body .= "You can view your published post here:\n";
        $default_body .= "{post_link}\n\n";
        $default_body .= "Thank you for your contribution!\n\n";
        $default_body .= "Regards,\n";
        $default_body .= "{site_name} Team";

        $body = isset($options['approval_email_body']) ? $options['approval_email_body'] : $default_body;
        ?>
        <textarea name="gpfs_settings[approval_email_body]" rows="10" class="large-text code"><?php echo esc_textarea($body); ?></textarea>
        <p class="description"><?php _e('Email body for the notification sent to the author when their post is approved.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Rejection Email Subject field callback.
     */
    public function rejection_email_subject_callback() {
        $options = get_option('gpfs_settings');
        $default_subject = '[{site_name}] About Your Guest Post Submission';
        $subject = isset($options['rejection_email_subject']) ? $options['rejection_email_subject'] : $default_subject;
        ?>
        <input type="text" name="gpfs_settings[rejection_email_subject]" value="<?php echo esc_attr($subject); ?>" class="large-text">
        <p class="description"><?php _e('Subject line for the email sent to the author when their post is rejected.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Rejection Email Body field callback.
     */
    public function rejection_email_body_callback() {
        $options = get_option('gpfs_settings');
        $default_body = "Hello {author_name},\n\n";
        $default_body .= "Thank you for submitting your guest post \"{post_title}\" to {site_name}.\n\n";
        $default_body .= "After review, we regret to inform you that we are unable to publish your submission at this time.\n\n";
        $default_body .= "We encourage you to review our content guidelines and consider submitting again in the future.\n\n";
        $default_body .= "Regards,\n";
        $default_body .= "{site_name} Team";

        $body = isset($options['rejection_email_body']) ? $options['rejection_email_body'] : $default_body;
        ?>
        <textarea name="gpfs_settings[rejection_email_body]" rows="10" class="large-text code"><?php echo esc_textarea($body); ?></textarea>
        <p class="description"><?php _e('Email body for the notification sent to the author when their post is rejected.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }
}

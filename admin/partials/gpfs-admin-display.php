<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="gpfs-admin-container">
        <div class="gpfs-admin-main">
            <form method="post" action="options.php">
                <?php
                settings_fields('gpfs_settings_group');
                ?>

                <div class="gpfs-admin-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#general-settings" class="nav-tab nav-tab-active"><?php _e('General Settings', 'guest-post-frontend-submitter'); ?></a>
                        <a href="#submission-limits" class="nav-tab"><?php _e('Submission Limits', 'guest-post-frontend-submitter'); ?></a>
                        <a href="#email-templates" class="nav-tab"><?php _e('Email Templates', 'guest-post-frontend-submitter'); ?></a>
                        <a href="#anti-spam" class="nav-tab"><?php _e('Anti-Spam', 'guest-post-frontend-submitter'); ?></a>
                        <a href="#email-marketing" class="nav-tab"><?php _e('Email Marketing', 'guest-post-frontend-submitter'); ?></a>
                        <a href="#form-style" class="nav-tab"><?php _e('Form Style', 'guest-post-frontend-submitter'); ?></a>
                    </nav>

                    <div id="general-settings" class="gpfs-tab-content active">
                        <h2><?php _e('General Settings', 'guest-post-frontend-submitter'); ?></h2>
                        <table class="form-table">
                            <?php do_settings_fields('guest-post-submissions', 'gpfs_general_settings'); ?>
                        </table>
                    </div>

                    <div id="submission-limits" class="gpfs-tab-content">
                        <h2><?php _e('Submission Limits', 'guest-post-frontend-submitter'); ?></h2>
                        <table class="form-table">
                            <?php do_settings_fields('guest-post-submissions', 'gpfs_submission_limits'); ?>
                        </table>
                    </div>

                    <div id="anti-spam" class="gpfs-tab-content">
                        <h2><?php _e('Anti-Spam Settings', 'guest-post-frontend-submitter'); ?></h2>
                        
                        <h3><?php _e('reCAPTCHA Integration', 'guest-post-frontend-submitter'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Enable reCAPTCHA', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $enable_recaptcha = isset($options['enable_recaptcha']) ? $options['enable_recaptcha'] : false;
                                    ?>
                                    <label>
                                        <input type="checkbox" name="gpfs_settings[enable_recaptcha]" value="1" <?php checked(1, $enable_recaptcha); ?>>
                                        <?php _e('Enable Google reCAPTCHA v2', 'guest-post-frontend-submitter'); ?>
                                    </label>
                                    <p class="description"><?php _e('Adds reCAPTCHA verification to the submission form to prevent spam.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Site Key', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $recaptcha_site_key = isset($options['recaptcha_site_key']) ? $options['recaptcha_site_key'] : '';
                                    ?>
                                    <input type="text" name="gpfs_settings[recaptcha_site_key]" value="<?php echo esc_attr($recaptcha_site_key); ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter your reCAPTCHA site key. You can get this from the <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin Console</a>.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Secret Key', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $recaptcha_secret_key = isset($options['recaptcha_secret_key']) ? $options['recaptcha_secret_key'] : '';
                                    ?>
                                    <input type="password" name="gpfs_settings[recaptcha_secret_key]" value="<?php echo esc_attr($recaptcha_secret_key); ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter your reCAPTCHA secret key.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Blocked Email Domains', 'guest-post-frontend-submitter'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Blocked Domains', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $blocked_domains = isset($options['blocked_domains']) ? $options['blocked_domains'] : '';
                                    ?>
                                    <textarea name="gpfs_settings[blocked_domains]" rows="5" class="large-text code"><?php echo esc_textarea($blocked_domains); ?></textarea>
                                    <p class="description"><?php _e('Enter one domain per line. Submissions from these domains will be blocked. For example: spam.com', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Blocked Keywords', 'guest-post-frontend-submitter'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Blocked Keywords', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $blocked_keywords = isset($options['blocked_keywords']) ? $options['blocked_keywords'] : '';
                                    ?>
                                    <textarea name="gpfs_settings[blocked_keywords]" rows="5" class="large-text code"><?php echo esc_textarea($blocked_keywords); ?></textarea>
                                    <p class="description"><?php _e('Enter one keyword per line. Submissions containing these keywords will be blocked. For example: casino, viagra', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('OpenAI Moderation API', 'guest-post-frontend-submitter'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Enable OpenAI Moderation', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $enable_openai_moderation = isset($options['enable_openai_moderation']) ? $options['enable_openai_moderation'] : false;
                                    ?>
                                    <label>
                                        <input type="checkbox" name="gpfs_settings[enable_openai_moderation]" value="1" <?php checked(1, $enable_openai_moderation); ?>>
                                        <?php _e('Enable OpenAI Moderation API', 'guest-post-frontend-submitter'); ?>
                                    </label>
                                    <p class="description"><?php _e('Uses OpenAI\'s Moderation API to automatically detect and block harmful content.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('API Key', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $openai_api_key = isset($options['openai_api_key']) ? $options['openai_api_key'] : '';
                                    ?>
                                    <input type="password" name="gpfs_settings[openai_api_key]" value="<?php echo esc_attr($openai_api_key); ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter your OpenAI API key. You can get this from the <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI Dashboard</a>.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="email-marketing" class="gpfs-tab-content">
                        <h2><?php _e('Email Marketing Integration', 'guest-post-frontend-submitter'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Enable Email Marketing', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $enable_email_marketing = isset($options['enable_email_marketing']) ? $options['enable_email_marketing'] : false;
                                    ?>
                                    <label>
                                        <input type="checkbox" name="gpfs_settings[enable_email_marketing]" value="1" <?php checked(1, $enable_email_marketing); ?>>
                                        <?php _e('Enable email marketing integration', 'guest-post-frontend-submitter'); ?>
                                    </label>
                                    <p class="description"><?php _e('Adds a consent checkbox to the form and integrates with email marketing services.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Consent Text', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $consent_text = isset($options['consent_text']) ? $options['consent_text'] : __('Yes, I would like to receive updates and newsletters.', 'guest-post-frontend-submitter');
                                    ?>
                                    <input type="text" name="gpfs_settings[consent_text]" value="<?php echo esc_attr($consent_text); ?>" class="large-text">
                                    <p class="description"><?php _e('Text displayed next to the consent checkbox.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Email Marketing Service', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $email_marketing_service = isset($options['email_marketing_service']) ? $options['email_marketing_service'] : '';
                                    ?>
                                    <select name="gpfs_settings[email_marketing_service]" id="gpfs_email_marketing_service">
                                        <option value=""><?php _e('Select a service', 'guest-post-frontend-submitter'); ?></option>
                                        <option value="mailchimp" <?php selected('mailchimp', $email_marketing_service); ?>><?php _e('Mailchimp', 'guest-post-frontend-submitter'); ?></option>
                                        <option value="convertkit" <?php selected('convertkit', $email_marketing_service); ?>><?php _e('ConvertKit', 'guest-post-frontend-submitter'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        
                        <div id="mailchimp-settings" class="service-settings" style="<?php echo $email_marketing_service === 'mailchimp' ? 'display: block;' : 'display: none;'; ?>">
                            <h3><?php _e('Mailchimp Settings', 'guest-post-frontend-submitter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('API Key', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $mailchimp_api_key = isset($options['mailchimp_api_key']) ? $options['mailchimp_api_key'] : '';
                                        ?>
                                        <input type="password" name="gpfs_settings[mailchimp_api_key]" value="<?php echo esc_attr($mailchimp_api_key); ?>" class="regular-text">
                                        <p class="description"><?php _e('Enter your Mailchimp API key.', 'guest-post-frontend-submitter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('List ID', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $mailchimp_list_id = isset($options['mailchimp_list_id']) ? $options['mailchimp_list_id'] : '';
                                        ?>
                                        <input type="text" name="gpfs_settings[mailchimp_list_id]" value="<?php echo esc_attr($mailchimp_list_id); ?>" class="regular-text">
                                        <p class="description"><?php _e('Enter your Mailchimp list/audience ID.', 'guest-post-frontend-submitter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Tags', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $mailchimp_tags = isset($options['mailchimp_tags']) ? $options['mailchimp_tags'] : '';
                                        ?>
                                        <input type="text" name="gpfs_settings[mailchimp_tags]" value="<?php echo esc_attr($mailchimp_tags); ?>" class="regular-text">
                                        <p class="description"><?php _e('Enter tags to apply to subscribers, separated by commas. For example: guest author, website', 'guest-post-frontend-submitter'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div id="convertkit-settings" class="service-settings" style="<?php echo $email_marketing_service === 'convertkit' ? 'display: block;' : 'display: none;'; ?>">
                            <h3><?php _e('ConvertKit Settings', 'guest-post-frontend-submitter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('API Key', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $convertkit_api_key = isset($options['convertkit_api_key']) ? $options['convertkit_api_key'] : '';
                                        ?>
                                        <input type="password" name="gpfs_settings[convertkit_api_key]" value="<?php echo esc_attr($convertkit_api_key); ?>" class="regular-text">
                                        <p class="description"><?php _e('Enter your ConvertKit API key.', 'guest-post-frontend-submitter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Form ID', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $convertkit_form_id = isset($options['convertkit_form_id']) ? $options['convertkit_form_id'] : '';
                                        ?>
                                        <input type="text" name="gpfs_settings[convertkit_form_id]" value="<?php echo esc_attr($convertkit_form_id); ?>" class="regular-text">
                                        <p class="description"><?php _e('Enter your ConvertKit form ID.', 'guest-post-frontend-submitter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Tags', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $convertkit_tags = isset($options['convertkit_tags']) ? $options['convertkit_tags'] : '';
                                        ?>
                                        <input type="text" name="gpfs_settings[convertkit_tags]" value="<?php echo esc_attr($convertkit_tags); ?>" class="regular-text">
                                        <p class="description"><?php _e('Enter tags to apply to subscribers, separated by commas.', 'guest-post-frontend-submitter'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="form-style" class="gpfs-tab-content">
                        <h2><?php _e('Form Style Settings', 'guest-post-frontend-submitter'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Form Theme', 'guest-post-frontend-submitter'); ?></th>
                                <td>
                                    <?php
                                    $form_theme = isset($options['form_theme']) ? $options['form_theme'] : 'light';
                                    ?>
                                    <select name="gpfs_settings[form_theme]" id="gpfs_form_theme">
                                        <option value="light" <?php selected('light', $form_theme); ?>><?php _e('Light', 'guest-post-frontend-submitter'); ?></option>
                                        <option value="dark" <?php selected('dark', $form_theme); ?>><?php _e('Dark', 'guest-post-frontend-submitter'); ?></option>
                                        <option value="custom" <?php selected('custom', $form_theme); ?>><?php _e('Custom', 'guest-post-frontend-submitter'); ?></option>
                                    </select>
                                    <p class="description"><?php _e('Select the theme for the submission form.', 'guest-post-frontend-submitter'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <div id="custom-theme-settings" style="<?php echo $form_theme === 'custom' ? 'display: block;' : 'display: none;'; ?>">
                            <h3><?php _e('Custom Theme Settings', 'guest-post-frontend-submitter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Background Color', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $background_color = isset($options['background_color']) ? $options['background_color'] : '#ffffff';
                                        ?>
                                        <input type="color" name="gpfs_settings[background_color]" value="<?php echo esc_attr($background_color); ?>" class="color-picker">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Text Color', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $text_color = isset($options['text_color']) ? $options['text_color'] : '#333333';
                                        ?>
                                        <input type="color" name="gpfs_settings[text_color]" value="<?php echo esc_attr($text_color); ?>" class="color-picker">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Button Color', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $button_color = isset($options['button_color']) ? $options['button_color'] : '#0073aa';
                                        ?>
                                        <input type="color" name="gpfs_settings[button_color]" value="<?php echo esc_attr($button_color); ?>" class="color-picker">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Button Text Color', 'guest-post-frontend-submitter'); ?></th>
                                    <td>
                                        <?php
                                        $button_text_color = isset($options['button_text_color']) ? $options['button_text_color'] : '#ffffff';
                                        ?>
                                        <input type="color" name="gpfs_settings[button_text_color]" value="<?php echo esc_attr($button_text_color); ?>" class="color-picker">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <h3><?php _e('Form Preview', 'guest-post-frontend-submitter'); ?></h3>
                        <div id="form-preview" class="gpfs-form-preview">
                            <div class="gpfs-card">
                                <div class="gpfs-card-header">
                                    <h3><?php _e('Submit a Guest Post', 'guest-post-frontend-submitter'); ?></h3>
                                </div>
                                <div class="gpfs-form-row">
                                    <label class="gpfs-label"><?php _e('Post Title', 'guest-post-frontend-submitter'); ?> <span class="required">*</span></label>
                                    <input type="text" class="gpfs-input" placeholder="<?php _e('Enter post title', 'guest-post-frontend-submitter'); ?>">
                                </div>
                                <div class="gpfs-form-row">
                                    <label class="gpfs-label"><?php _e('Post Content', 'guest-post-frontend-submitter'); ?> <span class="required">*</span></label>
                                    <textarea class="gpfs-textarea" rows="3" placeholder="<?php _e('Enter post content', 'guest-post-frontend-submitter'); ?>"></textarea>
                                </div>
                                <div class="gpfs-form-row">
                                    <button type="button" class="gpfs-submit-button"><?php _e('Submit Post', 'guest-post-frontend-submitter'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <div class="gpfs-admin-sidebar">
            <div class="gpfs-admin-box">
                <h3><?php _e('About This Plugin', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Guest Post Frontend Submitter allows your visitors to submit posts from the front-end of your website.', 'guest-post-frontend-submitter'); ?></p>
                <p><?php _e('For support or feature requests, please contact us.', 'guest-post-frontend-submitter'); ?></p>
            </div>
            
            <div class="gpfs-admin-box">
                <h3><?php _e('Shortcode Usage', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Use this shortcode to display the submission form:', 'guest-post-frontend-submitter'); ?></p>
                <code>[guest_post_form]</code>
                
                <p><?php _e('With custom attributes:', 'guest-post-frontend-submitter'); ?></p>
                <code>[guest_post_form title="Submit Your Post" button_text="Send Post"]</code>
            </div>
        </div>
    </div>
</div>

<style>
    .gpfs-admin-container {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .gpfs-admin-main {
        flex: 1;
        min-width: 600px;
        margin-right: 20px;
    }
    
    .gpfs-admin-sidebar {
        width: 280px;
    }
    
    .gpfs-admin-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin-bottom: 20px;
        padding: 15px;
    }
    
    .gpfs-admin-box h3 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .gpfs-tab-content {
        display: none;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-top: none;
        padding: 20px;
    }
    
    .gpfs-tab-content.active {
        display: block;
    }
    
    .gpfs-merge-tags {
        background: #f9f9f9;
        border-left: 4px solid #0073aa;
        padding: 10px 15px;
        margin-bottom: 20px;
    }
    
    .gpfs-merge-tags li {
        margin-bottom: 5px;
    }
    
    @media screen and (max-width: 782px) {
        .gpfs-admin-main {
            margin-right: 0;
            min-width: 100%;
        }
        
        .gpfs-admin-sidebar {
            width: 100%;
            margin-top: 20px;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Tab functionality
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and content
            $('.nav-tab').removeClass('nav-tab-active');
            $('.gpfs-tab-content').removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('nav-tab-active');
            
            // Show corresponding content
            $($(this).attr('href')).addClass('active');
        });
    });
</script>
                    <div id="email-templates" class="gpfs-tab-content">
                        <?php include_once('email-templates-tab.php'); ?>
                    </div>

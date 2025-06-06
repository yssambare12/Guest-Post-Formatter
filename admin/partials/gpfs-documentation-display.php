<?php
/**
 * Provide a admin area view for the documentation page
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
    
    <div class="gpfs-documentation">
        <div class="gpfs-documentation-nav">
            <ul>
                <li><a href="#introduction" class="active"><?php _e('Introduction', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#installation"><?php _e('Installation', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#shortcodes"><?php _e('Shortcodes', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#settings"><?php _e('Settings', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#moderation"><?php _e('Moderation', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#anti-spam"><?php _e('Anti-Spam', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#email-marketing"><?php _e('Email Marketing', 'guest-post-frontend-submitter'); ?></a></li>
                <li><a href="#faq"><?php _e('FAQ', 'guest-post-frontend-submitter'); ?></a></li>
            </ul>
        </div>
        
        <div class="gpfs-documentation-content">
            <section id="introduction" class="gpfs-doc-section active">
                <h2><?php _e('Introduction', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('Guest Post Frontend Submitter allows visitors to submit posts from the front-end of your WordPress site. It\'s perfect for blogs that accept guest contributions, community websites, or any site that wants to encourage user-generated content.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Features', 'guest-post-frontend-submitter'); ?></h3>
                <ul>
                    <li><?php _e('Simple shortcode to display the submission form anywhere on your site', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Customizable form fields including title, content, excerpt, and featured image', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Security features to prevent spam submissions', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Admin settings to control post status and category', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Email notifications with one-click approve/reject actions', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Responsive design that works on all devices', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Lightweight and optimized for performance', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Developer-friendly with hooks and filters for customization', 'guest-post-frontend-submitter'); ?></li>
                </ul>
            </section>
            
            <section id="installation" class="gpfs-doc-section">
                <h2><?php _e('Installation', 'guest-post-frontend-submitter'); ?></h2>
                <ol>
                    <li><?php _e('Upload the <code>guest-post-frontend-submitter</code> folder to the <code>/wp-content/plugins/</code> directory', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Activate the plugin through the \'Plugins\' menu in WordPress', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Go to Guest Post Submitter > Settings to configure the plugin', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Add the shortcode <code>[guest_post_form]</code> to any page or post where you want the submission form to appear', 'guest-post-frontend-submitter'); ?></li>
                </ol>
            </section>
            
            <section id="shortcodes" class="gpfs-doc-section">
                <h2><?php _e('Shortcodes', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('The plugin provides two shortcodes for displaying the submission form:', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Standard Form', 'guest-post-frontend-submitter'); ?></h3>
                <p><code>[guest_post_form]</code></p>
                
                <h3><?php _e('React-based Form', 'guest-post-frontend-submitter'); ?></h3>
                <p><code>[guest_post_react_form]</code></p>
                
                <h3><?php _e('Shortcode Attributes', 'guest-post-frontend-submitter'); ?></h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Attribute', 'guest-post-frontend-submitter'); ?></th>
                            <th><?php _e('Description', 'guest-post-frontend-submitter'); ?></th>
                            <th><?php _e('Default', 'guest-post-frontend-submitter'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>title</code></td>
                            <td><?php _e('Form title', 'guest-post-frontend-submitter'); ?></td>
                            <td><?php _e('Submit a Guest Post', 'guest-post-frontend-submitter'); ?></td>
                        </tr>
                        <tr>
                            <td><code>success_message</code></td>
                            <td><?php _e('Message displayed after successful submission', 'guest-post-frontend-submitter'); ?></td>
                            <td><?php _e('Thank you! Your post has been submitted successfully.', 'guest-post-frontend-submitter'); ?></td>
                        </tr>
                        <tr>
                            <td><code>button_text</code></td>
                            <td><?php _e('Submit button text', 'guest-post-frontend-submitter'); ?></td>
                            <td><?php _e('Submit Post', 'guest-post-frontend-submitter'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_excerpt</code></td>
                            <td><?php _e('Show excerpt field', 'guest-post-frontend-submitter'); ?></td>
                            <td>yes</td>
                        </tr>
                        <tr>
                            <td><code>show_featured_image</code></td>
                            <td><?php _e('Show featured image upload field', 'guest-post-frontend-submitter'); ?></td>
                            <td>yes</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3><?php _e('Example', 'guest-post-frontend-submitter'); ?></h3>
                <p><code>[guest_post_form title="Share Your Story" button_text="Submit Your Post" show_excerpt="no"]</code></p>
            </section>
            
            <section id="settings" class="gpfs-doc-section">
                <h2><?php _e('Settings', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('The plugin settings are organized into several tabs:', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('General Settings', 'guest-post-frontend-submitter'); ?></h3>
                <ul>
                    <li><?php _e('<strong>Default Category</strong>: Choose a default category for guest posts if none is selected in the form.', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('<strong>Enable Moderation</strong>: If enabled, guest posts will be saved as pending for review. If disabled, posts will be published directly.', 'guest-post-frontend-submitter'); ?></li>
                </ul>
                
                <h3><?php _e('Submission Limits', 'guest-post-frontend-submitter'); ?></h3>
                <ul>
                    <li><?php _e('<strong>Enable Submission Limits</strong>: If enabled, users will be limited to a maximum number of submissions per day.', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('<strong>Max Submissions Per Day</strong>: Maximum number of submissions allowed per IP address per day.', 'guest-post-frontend-submitter'); ?></li>
                </ul>
                
                <h3><?php _e('Email Templates', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Customize the email templates for admin notifications and author notifications.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Anti-Spam', 'guest-post-frontend-submitter'); ?></h3>
                <ul>
                    <li><?php _e('<strong>Enable reCAPTCHA</strong>: Adds reCAPTCHA verification to the submission form to prevent spam.', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('<strong>Blocked Domains</strong>: Block submissions from specific email domains.', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('<strong>Blocked Keywords</strong>: Block submissions containing specific keywords.', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('<strong>Enable OpenAI Moderation</strong>: Uses OpenAI\'s Moderation API to automatically detect and block harmful content.', 'guest-post-frontend-submitter'); ?></li>
                </ul>
                
                <h3><?php _e('Email Marketing', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Integrate with email marketing services like Mailchimp and ConvertKit.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Form Style', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Customize the appearance of the submission form.', 'guest-post-frontend-submitter'); ?></p>
            </section>
            
            <section id="moderation" class="gpfs-doc-section">
                <h2><?php _e('Moderation', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('The plugin provides several ways to moderate guest post submissions:', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Email Moderation', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('When a new guest post is submitted, an email notification is sent to the admin with links to approve or reject the post directly from the email.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Dashboard Moderation', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('The plugin adds a dashboard widget showing recent submissions with quick action links to approve or reject posts.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Submissions Page', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('The Submissions page provides a comprehensive view of all guest post submissions with filtering options and bulk actions.', 'guest-post-frontend-submitter'); ?></p>
            </section>
            
            <section id="anti-spam" class="gpfs-doc-section">
                <h2><?php _e('Anti-Spam', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('The plugin includes several anti-spam features:', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('reCAPTCHA', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Integrate Google reCAPTCHA v2 to prevent bot submissions.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Honeypot', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('The form includes a hidden honeypot field to catch automated spam submissions.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Domain Blocking', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Block submissions from specific email domains known for spam.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Keyword Filtering', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Block submissions containing specific keywords associated with spam.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('OpenAI Moderation', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Use OpenAI\'s Moderation API to automatically detect and block harmful content.', 'guest-post-frontend-submitter'); ?></p>
            </section>
            
            <section id="email-marketing" class="gpfs-doc-section">
                <h2><?php _e('Email Marketing', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('The plugin can integrate with popular email marketing services:', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Mailchimp', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Add guest post authors to your Mailchimp lists with their consent.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('ConvertKit', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Add guest post authors to your ConvertKit forms with their consent.', 'guest-post-frontend-submitter'); ?></p>
                
                <h3><?php _e('Consent Checkbox', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('The plugin adds a consent checkbox to the submission form to comply with privacy regulations.', 'guest-post-frontend-submitter'); ?></p>
            </section>
            
            <section id="faq" class="gpfs-doc-section">
                <h2><?php _e('Frequently Asked Questions', 'guest-post-frontend-submitter'); ?></h2>
                
                <div class="gpfs-faq-item">
                    <h3><?php _e('Can I customize the submission form?', 'guest-post-frontend-submitter'); ?></h3>
                    <p><?php _e('Yes, you can customize the form using shortcode attributes and the Form Style settings. You can also use CSS to further customize the appearance.', 'guest-post-frontend-submitter'); ?></p>
                </div>
                
                <div class="gpfs-faq-item">
                    <h3><?php _e('How do I moderate submissions?', 'guest-post-frontend-submitter'); ?></h3>
                    <p><?php _e('You can moderate submissions through email notifications, the dashboard widget, or the Submissions page. You can approve or reject posts with a single click.', 'guest-post-frontend-submitter'); ?></p>
                </div>
                
                <div class="gpfs-faq-item">
                    <h3><?php _e('Can I limit the number of submissions?', 'guest-post-frontend-submitter'); ?></h3>
                    <p><?php _e('Yes, you can limit the number of submissions per IP address per day in the Submission Limits settings.', 'guest-post-frontend-submitter'); ?></p>
                </div>
                
                <div class="gpfs-faq-item">
                    <h3><?php _e('How do I prevent spam submissions?', 'guest-post-frontend-submitter'); ?></h3>
                    <p><?php _e('The plugin includes several anti-spam features: reCAPTCHA, honeypot, domain blocking, keyword filtering, and OpenAI Moderation.', 'guest-post-frontend-submitter'); ?></p>
                </div>
                
                <div class="gpfs-faq-item">
                    <h3><?php _e('Can I add guest post authors to my email list?', 'guest-post-frontend-submitter'); ?></h3>
                    <p><?php _e('Yes, the plugin integrates with Mailchimp and ConvertKit to add guest post authors to your email list with their consent.', 'guest-post-frontend-submitter'); ?></p>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
    .gpfs-documentation {
        display: flex;
        margin-top: 20px;
    }
    
    .gpfs-documentation-nav {
        width: 200px;
        margin-right: 30px;
    }
    
    .gpfs-documentation-nav ul {
        margin: 0;
        padding: 0;
        list-style: none;
        position: sticky;
        top: 32px;
    }
    
    .gpfs-documentation-nav li {
        margin-bottom: 5px;
    }
    
    .gpfs-documentation-nav a {
        display: block;
        padding: 8px 12px;
        text-decoration: none;
        border-left: 3px solid transparent;
        color: #23282d;
    }
    
    .gpfs-documentation-nav a:hover,
    .gpfs-documentation-nav a.active {
        border-left-color: #0073aa;
        background-color: #f0f0f1;
    }
    
    .gpfs-documentation-content {
        flex: 1;
        max-width: 800px;
    }
    
    .gpfs-doc-section {
        display: none;
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin-bottom: 20px;
    }
    
    .gpfs-doc-section.active {
        display: block;
    }
    
    .gpfs-doc-section h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .gpfs-doc-section h3 {
        margin-top: 20px;
        margin-bottom: 10px;
    }
    
    .gpfs-faq-item {
        margin-bottom: 20px;
    }
    
    .gpfs-faq-item h3 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    @media screen and (max-width: 782px) {
        .gpfs-documentation {
            flex-direction: column;
        }
        
        .gpfs-documentation-nav {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
        
        .gpfs-documentation-nav ul {
            position: static;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Documentation navigation
        $('.gpfs-documentation-nav a').on('click', function(e) {
            e.preventDefault();
            
            // Update active nav item
            $('.gpfs-documentation-nav a').removeClass('active');
            $(this).addClass('active');
            
            // Show corresponding section
            var target = $(this).attr('href');
            $('.gpfs-doc-section').removeClass('active');
            $(target).addClass('active');
        });
    });
</script>

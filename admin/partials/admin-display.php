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
    
    <div class="gpfs-admin-content">
        <div class="gpfs-admin-main">
            <form method="post" action="options.php">
                <?php
                settings_fields('gpfs_settings');
                do_settings_sections('guest-post-frontend-submitter');
                submit_button();
                ?>
            </form>
            
            <div class="gpfs-shortcode-info">
                <h2><?php _e('Shortcode Usage', 'guest-post-frontend-submitter'); ?></h2>
                <p><?php _e('Use the following shortcode to display the guest post submission form on any page or post:', 'guest-post-frontend-submitter'); ?></p>
                <code>[guest_post_form]</code>
                
                <h3><?php _e('Available Attributes', 'guest-post-frontend-submitter'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">title</th>
                        <td><?php _e('Form title', 'guest-post-frontend-submitter'); ?></td>
                        <td><code>[guest_post_form title="Submit Your Post"]</code></td>
                    </tr>
                    <tr>
                        <th scope="row">success_message</th>
                        <td><?php _e('Message displayed after successful submission', 'guest-post-frontend-submitter'); ?></td>
                        <td><code>[guest_post_form success_message="Thanks for your submission!"]</code></td>
                    </tr>
                    <tr>
                        <th scope="row">button_text</th>
                        <td><?php _e('Submit button text', 'guest-post-frontend-submitter'); ?></td>
                        <td><code>[guest_post_form button_text="Send Post"]</code></td>
                    </tr>
                    <tr>
                        <th scope="row">show_excerpt</th>
                        <td><?php _e('Show excerpt field (yes/no)', 'guest-post-frontend-submitter'); ?></td>
                        <td><code>[guest_post_form show_excerpt="no"]</code></td>
                    </tr>
                    <tr>
                        <th scope="row">show_featured_image</th>
                        <td><?php _e('Show featured image upload field (yes/no)', 'guest-post-frontend-submitter'); ?></td>
                        <td><code>[guest_post_form show_featured_image="no"]</code></td>
                    </tr>
                </table>
                
                <h3><?php _e('Form Fields', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('The form includes the following fields:', 'guest-post-frontend-submitter'); ?></p>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li><?php _e('Post Title (required)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Post Content with rich text editor (required)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Author Name (required)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Author Email (required)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Author Bio (optional)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Post Category (required)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Excerpt (optional)', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Featured Image (optional)', 'guest-post-frontend-submitter'); ?></li>
                </ul>
                
                <h3><?php _e('Post Submission Process', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('When a user submits a post:', 'guest-post-frontend-submitter'); ?></p>
                <ol style="list-style-type: decimal; margin-left: 20px;">
                    <li><?php _e('The form validates all required fields', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('The post is saved as a draft', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Author information is saved as post metadata', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('The post is assigned to the selected category', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('An email notification is sent to the admin with approve/reject links', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('A success message is displayed to the user', 'guest-post-frontend-submitter'); ?></li>
                </ol>
                
                <h3><?php _e('Email Notifications', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('When a new guest post is submitted, an email notification is sent with:', 'guest-post-frontend-submitter'); ?></p>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li><?php _e('Post title and submission date', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Author name and email', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('Link to edit the post in the admin area', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('One-click "Approve" link to publish the post', 'guest-post-frontend-submitter'); ?></li>
                    <li><?php _e('One-click "Reject" link to trash the post', 'guest-post-frontend-submitter'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="gpfs-admin-sidebar">
            <div class="gpfs-admin-box">
                <h3><?php _e('About This Plugin', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Guest Post Frontend Submitter allows your visitors to submit posts from the front-end of your website.', 'guest-post-frontend-submitter'); ?></p>
                <p><?php _e('For support or feature requests, please contact us.', 'guest-post-frontend-submitter'); ?></p>
            </div>
        </div>
    </div>
</div>

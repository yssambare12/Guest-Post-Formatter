<?php
/**
 * Email Templates Tab Content
 *
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$options = get_option('gpfs_settings', array());
?>

<div id="email-templates" class="gpfs-tab-content">
    <h2><?php _e('Email Templates', 'guest-post-frontend-submitter'); ?></h2>
    
    <h3><?php _e('Admin Notification', 'guest-post-frontend-submitter'); ?></h3>
    <p><?php _e('This email is sent to the admin when a new guest post is submitted.', 'guest-post-frontend-submitter'); ?></p>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Subject', 'guest-post-frontend-submitter'); ?></th>
            <td>
                <?php
                $default_subject = '[{site_name}] New Guest Post Submission: "{post_title}"';
                $subject = isset($options['admin_notification_subject']) ? $options['admin_notification_subject'] : $default_subject;
                ?>
                <input type="text" name="gpfs_settings[admin_notification_subject]" value="<?php echo esc_attr($subject); ?>" class="large-text">
                <p class="description"><?php _e('Subject line for the email sent to admin when a new guest post is submitted.', 'guest-post-frontend-submitter'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Body', 'guest-post-frontend-submitter'); ?></th>
            <td>
                <?php
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
            </td>
        </tr>
    </table>
    
    <h3><?php _e('Approval Email', 'guest-post-frontend-submitter'); ?></h3>
    <p><?php _e('This email is sent to the author when their post is approved.', 'guest-post-frontend-submitter'); ?></p>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Subject', 'guest-post-frontend-submitter'); ?></th>
            <td>
                <?php
                $default_subject = '[{site_name}] Your Guest Post Has Been Approved';
                $subject = isset($options['approval_email_subject']) ? $options['approval_email_subject'] : $default_subject;
                ?>
                <input type="text" name="gpfs_settings[approval_email_subject]" value="<?php echo esc_attr($subject); ?>" class="large-text">
                <p class="description"><?php _e('Subject line for the email sent to the author when their post is approved.', 'guest-post-frontend-submitter'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Body', 'guest-post-frontend-submitter'); ?></th>
            <td>
                <?php
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
            </td>
        </tr>
    </table>
    
    <h3><?php _e('Rejection Email', 'guest-post-frontend-submitter'); ?></h3>
    <p><?php _e('This email is sent to the author when their post is rejected.', 'guest-post-frontend-submitter'); ?></p>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Subject', 'guest-post-frontend-submitter'); ?></th>
            <td>
                <?php
                $default_subject = '[{site_name}] About Your Guest Post Submission';
                $subject = isset($options['rejection_email_subject']) ? $options['rejection_email_subject'] : $default_subject;
                ?>
                <input type="text" name="gpfs_settings[rejection_email_subject]" value="<?php echo esc_attr($subject); ?>" class="large-text">
                <p class="description"><?php _e('Subject line for the email sent to the author when their post is rejected.', 'guest-post-frontend-submitter'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Body', 'guest-post-frontend-submitter'); ?></th>
            <td>
                <?php
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
            </td>
        </tr>
    </table>
    
    <h3><?php _e('Available Merge Tags', 'guest-post-frontend-submitter'); ?></h3>
    <p><?php _e('You can use the following merge tags in your email templates:', 'guest-post-frontend-submitter'); ?></p>
    <ul class="gpfs-merge-tags">
        <li><code>{site_name}</code> - <?php _e('Your website name', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{site_url}</code> - <?php _e('Your website URL', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{post_title}</code> - <?php _e('The submitted post title', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{post_content}</code> - <?php _e('The submitted post content', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{author_name}</code> - <?php _e('The author\'s name', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{author_email}</code> - <?php _e('The author\'s email', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{author_bio}</code> - <?php _e('The author\'s bio', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{submission_date}</code> - <?php _e('The date and time of submission', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{edit_link}</code> - <?php _e('Link to edit the post in admin', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{post_link}</code> - <?php _e('Link to view the published post', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{approve_link}</code> - <?php _e('Link to approve the post (admin notification only)', 'guest-post-frontend-submitter'); ?></li>
        <li><code>{reject_link}</code> - <?php _e('Link to reject the post (admin notification only)', 'guest-post-frontend-submitter'); ?></li>
    </ul>
</div>

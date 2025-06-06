<?php
/**
 * Handle email notifications for guest post submissions.
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Notification {

    /**
     * Send notification email to admin when a new guest post is submitted.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    array     $post_data  The submitted post data.
     */
    public function send_admin_notification($post_id, $post_data) {
        // Get settings
        $settings = get_option('gpfs_settings', array());
        
        // Get the post
        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        // Get author information
        $author_name = get_post_meta($post_id, '_gpfs_author_name', true);
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);
        $author_bio = get_post_meta($post_id, '_gpfs_author_bio', true);

        // Get notification email (from settings or default to admin email)
        $notification_email = get_option('admin_email');

        // Create secure nonce tokens for approve/reject actions
        $approve_nonce = wp_create_nonce('gpfs_approve_post_' . $post_id);
        $reject_nonce = wp_create_nonce('gpfs_reject_post_' . $post_id);

        // Create action URLs
        $admin_url = admin_url('admin-ajax.php');
        $approve_url = add_query_arg(array(
            'action' => 'gpfs_approve_post',
            'post_id' => $post_id,
            'nonce' => $approve_nonce
        ), $admin_url);

        $reject_url = add_query_arg(array(
            'action' => 'gpfs_reject_post',
            'post_id' => $post_id,
            'nonce' => $reject_nonce
        ), $admin_url);

        // Get email templates from settings or use defaults
        $default_subject = '[{site_name}] New Guest Post Submission: "{post_title}"';
        $email_subject = isset($settings['admin_notification_subject']) ? $settings['admin_notification_subject'] : $default_subject;
        
        $default_message = "A new guest post has been submitted to your site {site_name}.\n\n";
        $default_message .= "Post Details:\n";
        $default_message .= "-------------\n";
        $default_message .= "Title: {post_title}\n";
        $default_message .= "Author: {author_name} ({author_email})\n";
        $default_message .= "Author Bio: {author_bio}\n";
        $default_message .= "Submission Date: {submission_date}\n\n";
        $default_message .= "You can view the full post in your WordPress admin:\n";
        $default_message .= "{edit_link}\n\n";
        $default_message .= "Quick Actions:\n";
        $default_message .= "-------------\n";
        $default_message .= "Approve: {approve_link}\n";
        $default_message .= "Reject: {reject_link}\n\n";
        $default_message .= "This email was sent from your website {site_url}.";
        
        $email_message = isset($settings['admin_notification_body']) ? $settings['admin_notification_body'] : $default_message;
        
        // Replace placeholders in subject
        $email_subject = str_replace(
            array('{site_name}', '{post_title}'),
            array(get_bloginfo('name'), $post->post_title),
            $email_subject
        );
        
        // Replace placeholders in message
        $submission_date = get_date_from_gmt($post->post_date_gmt, get_option('date_format') . ' ' . get_option('time_format'));
        $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
        $post_link = get_permalink($post_id);
        
        $email_message = str_replace(
            array(
                '{site_name}',
                '{site_url}',
                '{post_title}',
                '{post_content}',
                '{author_name}',
                '{author_email}',
                '{author_bio}',
                '{submission_date}',
                '{edit_link}',
                '{post_link}',
                '{approve_link}',
                '{reject_link}'
            ),
            array(
                get_bloginfo('name'),
                get_bloginfo('url'),
                $post->post_title,
                wp_strip_all_tags($post->post_content),
                $author_name,
                $author_email,
                $author_bio ? $author_bio : __('(No bio provided)', 'guest-post-frontend-submitter'),
                $submission_date,
                $edit_link,
                $post_link,
                $approve_url,
                $reject_url
            ),
            $email_message
        );

        // Email headers
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
            'Reply-To: ' . $author_name . ' <' . $author_email . '>'
        );

        // Send the email
        wp_mail($notification_email, $email_subject, $email_message, $headers);
    }

    /**
     * Handle post approval via URL.
     *
     * @since    1.0.0
     */
    public function approve_post() {
        // Check if this is an approval request
        if (!isset($_GET['action']) || $_GET['action'] !== 'gpfs_approve_post') {
            return;
        }

        // Verify required parameters
        if (!isset($_GET['post_id']) || !isset($_GET['nonce'])) {
            wp_die(__('Invalid request.', 'guest-post-frontend-submitter'));
        }

        $post_id = absint($_GET['post_id']);
        $nonce = sanitize_text_field($_GET['nonce']);

        // Verify nonce
        if (!wp_verify_nonce($nonce, 'gpfs_approve_post_' . $post_id)) {
            wp_die(__('Security check failed. Invalid nonce.', 'guest-post-frontend-submitter'));
        }

        // Check if post exists and is a draft
        $post = get_post($post_id);
        if (!$post || !in_array($post->post_status, array('draft', 'pending'))) {
            wp_die(__('Invalid post or post is not in draft/pending status.', 'guest-post-frontend-submitter'));
        }

        // Update post status to publish
        $updated_post = array(
            'ID' => $post_id,
            'post_status' => 'publish'
        );
        wp_update_post($updated_post);

        // Get author information for notification
        $author_name = get_post_meta($post_id, '_gpfs_author_name', true);
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);

        // Send notification to author if email is available
        if (!empty($author_email)) {
            $this->send_author_notification($post_id, 'approved');
        }

        // Redirect to the published post
        wp_redirect(get_permalink($post_id));
        exit;
    }

    /**
     * Handle post rejection via URL.
     *
     * @since    1.0.0
     */
    public function reject_post() {
        // Check if this is a rejection request
        if (!isset($_GET['action']) || $_GET['action'] !== 'gpfs_reject_post') {
            return;
        }

        // Verify required parameters
        if (!isset($_GET['post_id']) || !isset($_GET['nonce'])) {
            wp_die(__('Invalid request.', 'guest-post-frontend-submitter'));
        }

        $post_id = absint($_GET['post_id']);
        $nonce = sanitize_text_field($_GET['nonce']);

        // Verify nonce
        if (!wp_verify_nonce($nonce, 'gpfs_reject_post_' . $post_id)) {
            wp_die(__('Security check failed. Invalid nonce.', 'guest-post-frontend-submitter'));
        }

        // Check if post exists
        $post = get_post($post_id);
        if (!$post) {
            wp_die(__('Invalid post.', 'guest-post-frontend-submitter'));
        }

        // Move post to trash
        wp_trash_post($post_id);

        // Get author information for notification
        $author_name = get_post_meta($post_id, '_gpfs_author_name', true);
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);

        // Send notification to author if email is available
        if (!empty($author_email)) {
            $this->send_author_notification($post_id, 'rejected');
        }

        // Redirect to admin posts page
        wp_redirect(admin_url('edit.php?post_status=trash&post_type=post'));
        exit;
    }

    /**
     * Send notification to the author about post status.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    string    $status     The post status (approved/rejected).
     */
    private function send_author_notification($post_id, $status) {
        $post = get_post($post_id);
        $author_name = get_post_meta($post_id, '_gpfs_author_name', true);
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);
        $author_bio = get_post_meta($post_id, '_gpfs_author_bio', true);

        if (!$post || empty($author_email)) {
            return;
        }

        $site_name = get_bloginfo('name');
        $settings = get_option('gpfs_settings', array());
        
        if ($status === 'approved') {
            $default_subject = '[{site_name}] Your Guest Post Has Been Approved';
            $subject = isset($settings['approval_email_subject']) ? $settings['approval_email_subject'] : $default_subject;
            
            $default_message = "Hello {author_name},\n\n";
            $default_message .= "Great news! Your guest post \"{post_title}\" has been approved and published on {site_name}.\n\n";
            $default_message .= "You can view your published post here:\n";
            $default_message .= "{post_link}\n\n";
            $default_message .= "Thank you for your contribution!\n\n";
            $default_message .= "Regards,\n";
            $default_message .= "{site_name} Team";
            
            $message = isset($settings['approval_email_body']) ? $settings['approval_email_body'] : $default_message;
        } else {
            $default_subject = '[{site_name}] About Your Guest Post Submission';
            $subject = isset($settings['rejection_email_subject']) ? $settings['rejection_email_subject'] : $default_subject;
            
            $default_message = "Hello {author_name},\n\n";
            $default_message .= "Thank you for submitting your guest post \"{post_title}\" to {site_name}.\n\n";
            $default_message .= "After review, we regret to inform you that we are unable to publish your submission at this time.\n\n";
            $default_message .= "We encourage you to review our content guidelines and consider submitting again in the future.\n\n";
            $default_message .= "Regards,\n";
            $default_message .= "{site_name} Team";
            
            $message = isset($settings['rejection_email_body']) ? $settings['rejection_email_body'] : $default_message;
        }
        
        // Replace placeholders in subject
        $subject = str_replace(
            array('{site_name}', '{post_title}'),
            array($site_name, $post->post_title),
            $subject
        );
        
        // Replace placeholders in message
        $post_link = get_permalink($post_id);
        
        $message = str_replace(
            array(
                '{site_name}',
                '{site_url}',
                '{post_title}',
                '{post_content}',
                '{author_name}',
                '{author_email}',
                '{author_bio}',
                '{submission_date}',
                '{post_link}'
            ),
            array(
                $site_name,
                get_bloginfo('url'),
                $post->post_title,
                wp_strip_all_tags($post->post_content),
                $author_name,
                $author_email,
                $author_bio ? $author_bio : '',
                get_the_date('', $post_id),
                $post_link
            ),
            $message
        );

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>'
        );

        wp_mail($author_email, $subject, $message, $headers);
    }
    /**
     * Handle post approval via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_approve_post() {
        // Check for nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gpfs_approve_post_' . $_REQUEST['post_id'])) {
            wp_send_json_error(array('message' => __('Security check failed.', 'guest-post-frontend-submitter')));
        }

        // Check if post exists
        $post_id = absint($_REQUEST['post_id']);
        $post = get_post($post_id);
        
        if (!$post || !in_array($post->post_status, array('draft', 'pending'))) {
            wp_send_json_error(array('message' => __('Invalid post or post is not in draft/pending status.', 'guest-post-frontend-submitter')));
        }

        // Update post status to publish
        $updated_post = array(
            'ID' => $post_id,
            'post_status' => 'publish'
        );
        wp_update_post($updated_post);

        // Send notification to author
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);
        if (!empty($author_email)) {
            $this->send_author_notification($post_id, 'approved');
        }

        // Determine redirect URL
        $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : 'post';
        
        if ($redirect === 'dashboard') {
            $redirect_url = admin_url('index.php');
        } elseif ($redirect === 'submissions') {
            $redirect_url = admin_url('admin.php?page=guest-post-submissions');
        } else {
            $redirect_url = get_permalink($post_id);
        }

        wp_send_json_success(array(
            'message' => __('Post approved successfully.', 'guest-post-frontend-submitter'),
            'redirect' => $redirect_url
        ));
    }

    /**
     * Handle post rejection via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_reject_post() {
        // Check for nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'gpfs_reject_post_' . $_REQUEST['post_id'])) {
            wp_send_json_error(array('message' => __('Security check failed.', 'guest-post-frontend-submitter')));
        }

        // Check if post exists
        $post_id = absint($_REQUEST['post_id']);
        $post = get_post($post_id);
        
        if (!$post) {
            wp_send_json_error(array('message' => __('Invalid post.', 'guest-post-frontend-submitter')));
        }

        // Move post to trash
        wp_trash_post($post_id);

        // Send notification to author
        $author_email = get_post_meta($post_id, '_gpfs_author_email', true);
        if (!empty($author_email)) {
            $this->send_author_notification($post_id, 'rejected');
        }

        // Determine redirect URL
        $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : 'posts';
        
        if ($redirect === 'dashboard') {
            $redirect_url = admin_url('index.php');
        } elseif ($redirect === 'submissions') {
            $redirect_url = admin_url('admin.php?page=guest-post-submissions');
        } else {
            $redirect_url = admin_url('edit.php?post_status=trash&post_type=post');
        }

        wp_send_json_success(array(
            'message' => __('Post rejected successfully.', 'guest-post-frontend-submitter'),
            'redirect' => $redirect_url
        ));
    }
}

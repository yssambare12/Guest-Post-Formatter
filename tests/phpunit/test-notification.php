<?php
/**
 * Class NotificationTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Notification functionality tests.
 */
class NotificationTest extends WP_UnitTestCase {

    /**
     * Test instance of GPFS_Notification.
     *
     * @var GPFS_Notification
     */
    protected $notification;

    /**
     * Test post ID.
     *
     * @var int
     */
    protected $post_id;

    /**
     * Set up test environment.
     */
    public function setUp() {
        parent::setUp();
        $this->notification = new GPFS_Notification();
        
        // Create a test post
        $this->post_id = $this->factory->post->create(array(
            'post_title' => 'Test Guest Post',
            'post_content' => 'Test content',
            'post_status' => 'draft',
        ));
        
        // Add author meta
        update_post_meta($this->post_id, '_gpfs_author_name', 'Test Author');
        update_post_meta($this->post_id, '_gpfs_author_email', 'test@example.com');
    }

    /**
     * Test admin notification when enabled.
     */
    public function test_send_admin_notification_when_enabled() {
        // Enable notifications in options
        update_option('gpfs_options', array(
            'enable_notifications' => 1,
            'notification_email' => 'admin@example.com',
        ));
        
        // Mock wp_mail
        add_filter('wp_mail', array($this, 'mock_wp_mail'), 10, 1);
        $this->mail_data = null;
        
        // Send notification
        $this->notification->send_admin_notification($this->post_id, array());
        
        // Check if email was sent
        $this->assertNotNull($this->mail_data);
        $this->assertEquals('admin@example.com', $this->mail_data['to']);
        $this->assertStringContainsString('Test Guest Post', $this->mail_data['subject']);
        $this->assertStringContainsString('Test Author', $this->mail_data['message']);
        $this->assertStringContainsString('test@example.com', $this->mail_data['message']);
        $this->assertStringContainsString('Approve:', $this->mail_data['message']);
        $this->assertStringContainsString('Reject:', $this->mail_data['message']);
    }

    /**
     * Test admin notification when disabled.
     */
    public function test_send_admin_notification_when_disabled() {
        // Disable notifications in options
        update_option('gpfs_options', array('enable_notifications' => 0));
        
        // Mock wp_mail
        add_filter('wp_mail', array($this, 'mock_wp_mail'), 10, 1);
        $this->mail_data = null;
        
        // Send notification
        $this->notification->send_admin_notification($this->post_id, array());
        
        // Check that no email was sent
        $this->assertNull($this->mail_data);
    }

    /**
     * Test custom email templates.
     */
    public function test_custom_email_templates() {
        // Set custom email templates in options
        update_option('gpfs_options', array(
            'enable_notifications' => 1,
            'notification_email' => 'admin@example.com',
            'email_subject' => 'Custom Subject: %post_title%',
            'email_message' => 'Custom Message: %author_name% submitted %post_title%',
        ));
        
        // Mock wp_mail
        add_filter('wp_mail', array($this, 'mock_wp_mail'), 10, 1);
        $this->mail_data = null;
        
        // Send notification
        $this->notification->send_admin_notification($this->post_id, array());
        
        // Check if email was sent with custom templates
        $this->assertNotNull($this->mail_data);
        $this->assertStringContainsString('Custom Subject: Test Guest Post', $this->mail_data['subject']);
        $this->assertStringContainsString('Custom Message: Test Author submitted Test Guest Post', $this->mail_data['message']);
    }

    /**
     * Test post approval functionality.
     */
    public function test_approve_post() {
        // Create a nonce for approval
        $nonce = wp_create_nonce('gpfs_approve_post_' . $this->post_id);
        
        // Set up GET parameters
        $_GET = array(
            'action' => 'gpfs_approve_post',
            'post_id' => $this->post_id,
            'nonce' => $nonce,
        );
        
        // Mock wp_redirect
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 1);
        $this->redirect_url = null;
        
        // Call approve_post method
        ob_start();
        $this->notification->approve_post();
        ob_end_clean();
        
        // Check if post status was changed to publish
        $post = get_post($this->post_id);
        $this->assertEquals('publish', $post->post_status);
        
        // Check if redirect happened
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString(get_permalink($this->post_id), $this->redirect_url);
    }

    /**
     * Test post rejection functionality.
     */
    public function test_reject_post() {
        // Create a nonce for rejection
        $nonce = wp_create_nonce('gpfs_reject_post_' . $this->post_id);
        
        // Set up GET parameters
        $_GET = array(
            'action' => 'gpfs_reject_post',
            'post_id' => $this->post_id,
            'nonce' => $nonce,
        );
        
        // Mock wp_redirect
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 1);
        $this->redirect_url = null;
        
        // Call reject_post method
        ob_start();
        $this->notification->reject_post();
        ob_end_clean();
        
        // Check if post status was changed to trash
        $post = get_post($this->post_id);
        $this->assertEquals('trash', $post->post_status);
        
        // Check if redirect happened
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString('edit.php', $this->redirect_url);
        $this->assertStringContainsString('post_status=trash', $this->redirect_url);
    }

    /**
     * Test author notification on approval.
     */
    public function test_author_notification_on_approval() {
        // Create a nonce for approval
        $nonce = wp_create_nonce('gpfs_approve_post_' . $this->post_id);
        
        // Set up GET parameters
        $_GET = array(
            'action' => 'gpfs_approve_post',
            'post_id' => $this->post_id,
            'nonce' => $nonce,
        );
        
        // Mock wp_mail
        add_filter('wp_mail', array($this, 'mock_wp_mail'), 10, 1);
        $this->mail_data = null;
        
        // Mock wp_redirect
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 1);
        
        // Call approve_post method
        ob_start();
        $this->notification->approve_post();
        ob_end_clean();
        
        // Check if email was sent to author
        $this->assertNotNull($this->mail_data);
        $this->assertEquals('test@example.com', $this->mail_data['to']);
        $this->assertStringContainsString('Approved', $this->mail_data['subject']);
        $this->assertStringContainsString('has been approved', $this->mail_data['message']);
    }

    /**
     * Test invalid nonce for approval.
     */
    public function test_invalid_nonce_for_approval() {
        // Set up GET parameters with invalid nonce
        $_GET = array(
            'action' => 'gpfs_approve_post',
            'post_id' => $this->post_id,
            'nonce' => 'invalid_nonce',
        );
        
        // Expect wp_die to be called
        $this->expectException('WPDieException');
        
        // Call approve_post method
        $this->notification->approve_post();
    }

    /**
     * Mock function for wp_mail to prevent actual emails during tests.
     *
     * @param array $args The email arguments.
     * @return false Prevent actual email.
     */
    public function mock_wp_mail($args) {
        $this->mail_data = $args;
        return false; // Prevent actual email
    }

    /**
     * Mock function for wp_redirect to prevent actual redirects during tests.
     *
     * @param string $location The location to redirect to.
     * @return false Prevent actual redirect.
     */
    public function mock_redirect($location) {
        $this->redirect_url = $location;
        return false; // Prevent actual redirect
    }
}

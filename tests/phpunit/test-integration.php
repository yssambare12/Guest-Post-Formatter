<?php
/**
 * Class IntegrationTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Integration tests for the entire plugin workflow.
 */
class IntegrationTest extends WP_UnitTestCase {

    /**
     * Test post ID.
     *
     * @var int
     */
    protected $post_id;

    /**
     * Test category ID.
     *
     * @var int
     */
    protected $category_id;

    /**
     * Set up test environment.
     */
    public function setUp() {
        parent::setUp();
        
        // Create a test category
        $this->category_id = $this->factory->category->create(array('name' => 'Test Category'));
        
        // Set up plugin options
        update_option('gpfs_options', array(
            'post_status' => 'draft',
            'post_category' => $this->category_id,
            'enable_notifications' => 1,
            'enable_captcha' => 0,
            'notification_email' => 'admin@example.com',
        ));
    }

    /**
     * Test the complete workflow from form submission to approval.
     */
    public function test_complete_workflow() {
        // 1. Simulate form submission
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Integration Test Post',
            'gpfs_content' => 'This is a test post for the integration test.',
            'gpfs_author_name' => 'Integration Tester',
            'gpfs_author_email' => 'integration@example.com',
            'gpfs_category' => $this->category_id,
            'gpfs_excerpt' => 'Test excerpt for integration test.',
            'gpfs_website' => '', // Honeypot field should be empty
        );
        
        // Mock wp_redirect for form submission
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 1);
        $this->redirect_url = null;
        
        // Mock wp_mail for notification
        add_filter('wp_mail', array($this, 'mock_wp_mail'), 10, 1);
        $this->mail_data = null;
        
        // Process the submission
        $form_handler = new GPFS_Form_Handler();
        ob_start();
        $form_handler->process_submission();
        ob_end_clean();
        
        // Check if redirect happened with success parameter
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString('gpfs_success=1', $this->redirect_url);
        
        // Check if a post was created
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'title' => 'Integration Test Post',
        ));
        
        $this->assertCount(1, $posts);
        $this->post_id = $posts[0]->ID;
        
        // Check if notification email was sent
        $this->assertNotNull($this->mail_data);
        $this->assertEquals('admin@example.com', $this->mail_data['to']);
        $this->assertStringContainsString('Integration Test Post', $this->mail_data['subject']);
        
        // 2. Simulate post approval
        // Extract approval URL from email
        preg_match('/Approve: (http[^\s]+)/', $this->mail_data['message'], $matches);
        $approve_url = $matches[1];
        
        // Parse the URL to get parameters
        $url_parts = parse_url($approve_url);
        parse_str($url_parts['query'], $query_params);
        
        // Set up GET parameters for approval
        $_GET = array(
            'action' => $query_params['action'],
            'post_id' => $query_params['post_id'],
            'nonce' => $query_params['nonce'],
        );
        
        // Reset mail data
        $this->mail_data = null;
        
        // Process the approval
        $notification = new GPFS_Notification();
        ob_start();
        $notification->approve_post();
        ob_end_clean();
        
        // Check if post status was changed to publish
        $post = get_post($this->post_id);
        $this->assertEquals('publish', $post->post_status);
        
        // Check if author notification was sent
        $this->assertNotNull($this->mail_data);
        $this->assertEquals('integration@example.com', $this->mail_data['to']);
        $this->assertStringContainsString('Approved', $this->mail_data['subject']);
    }

    /**
     * Test the complete workflow from form submission to rejection.
     */
    public function test_rejection_workflow() {
        // 1. Simulate form submission
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Rejection Test Post',
            'gpfs_content' => 'This is a test post for the rejection test.',
            'gpfs_author_name' => 'Rejection Tester',
            'gpfs_author_email' => 'rejection@example.com',
            'gpfs_category' => $this->category_id,
            'gpfs_excerpt' => 'Test excerpt for rejection test.',
            'gpfs_website' => '', // Honeypot field should be empty
        );
        
        // Mock wp_redirect for form submission
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 1);
        $this->redirect_url = null;
        
        // Mock wp_mail for notification
        add_filter('wp_mail', array($this, 'mock_wp_mail'), 10, 1);
        $this->mail_data = null;
        
        // Process the submission
        $form_handler = new GPFS_Form_Handler();
        ob_start();
        $form_handler->process_submission();
        ob_end_clean();
        
        // Check if a post was created
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'title' => 'Rejection Test Post',
        ));
        
        $this->assertCount(1, $posts);
        $this->post_id = $posts[0]->ID;
        
        // Extract rejection URL from email
        preg_match('/Reject: (http[^\s]+)/', $this->mail_data['message'], $matches);
        $reject_url = $matches[1];
        
        // Parse the URL to get parameters
        $url_parts = parse_url($reject_url);
        parse_str($url_parts['query'], $query_params);
        
        // Set up GET parameters for rejection
        $_GET = array(
            'action' => $query_params['action'],
            'post_id' => $query_params['post_id'],
            'nonce' => $query_params['nonce'],
        );
        
        // Reset mail data
        $this->mail_data = null;
        
        // Process the rejection
        $notification = new GPFS_Notification();
        ob_start();
        $notification->reject_post();
        ob_end_clean();
        
        // Check if post status was changed to trash
        $post = get_post($this->post_id);
        $this->assertEquals('trash', $post->post_status);
        
        // Check if author notification was sent
        $this->assertNotNull($this->mail_data);
        $this->assertEquals('rejection@example.com', $this->mail_data['to']);
        $this->assertStringContainsString('Submission', $this->mail_data['subject']);
        $this->assertStringContainsString('unable to publish', $this->mail_data['message']);
    }

    /**
     * Test shortcode integration with form submission.
     */
    public function test_shortcode_integration() {
        // Create a page with the shortcode
        $page_id = $this->factory->post->create(array(
            'post_title' => 'Submit Guest Post',
            'post_content' => '[guest_post_form]',
            'post_status' => 'publish',
            'post_type' => 'page',
        ));
        
        // Get the page content
        $page = get_post($page_id);
        
        // Apply shortcodes to the content
        $content = apply_filters('the_content', $page->post_content);
        
        // Check if form is rendered
        $this->assertStringContainsString('id="gpfs-submission-form"', $content);
        $this->assertStringContainsString('name="gpfs_title"', $content);
        $this->assertStringContainsString('name="gpfs_content"', $content);
        $this->assertStringContainsString('name="gpfs_author_name"', $content);
        $this->assertStringContainsString('name="gpfs_author_email"', $content);
        $this->assertStringContainsString('name="gpfs_category"', $content);
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

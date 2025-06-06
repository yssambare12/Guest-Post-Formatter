<?php
/**
 * Class SecurityTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Security-focused tests.
 */
class SecurityTest extends WP_UnitTestCase {

    /**
     * Test instance of GPFS_Form_Handler.
     *
     * @var GPFS_Form_Handler
     */
    protected $form_handler;

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
        $this->form_handler = new GPFS_Form_Handler();
        $this->notification = new GPFS_Notification();
        
        // Create a test post
        $this->post_id = $this->factory->post->create(array(
            'post_title' => 'Security Test Post',
            'post_content' => 'Test content',
            'post_status' => 'draft',
        ));
    }

    /**
     * Test nonce validation in form submission.
     */
    public function test_form_submission_nonce_validation() {
        // Set up POST data with invalid nonce
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => 'invalid_nonce',
            'gpfs_title' => 'Test Post Title',
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => '1',
        );
        
        // Expect wp_die to be called
        $this->expectException('WPDieException');
        
        // Process the submission
        $this->form_handler->process_submission();
    }

    /**
     * Test XSS prevention in post title.
     */
    public function test_xss_prevention_in_title() {
        // Set up POST data with XSS attempt in title
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test <script>alert("XSS")</script> Title',
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => '1',
            'gpfs_website' => '', // Honeypot field should be empty
        );
        
        // Set up options
        update_option('gpfs_options', array(
            'post_status' => 'draft',
            'enable_captcha' => 0, // Disable CAPTCHA for this test
        ));
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if a post was created
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'draft',
        ));
        
        $this->assertGreaterThan(0, count($posts));
        $post = end($posts);
        
        // Check that script tags were sanitized from title
        $this->assertStringNotContainsString('<script>', $post->post_title);
        $this->assertStringContainsString('Test Title', $post->post_title);
    }

    /**
     * Test XSS prevention in post content.
     */
    public function test_xss_prevention_in_content() {
        // Set up POST data with XSS attempt in content
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test Title',
            'gpfs_content' => 'Test <script>alert("XSS")</script> content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => '1',
            'gpfs_website' => '', // Honeypot field should be empty
        );
        
        // Set up options
        update_option('gpfs_options', array(
            'post_status' => 'draft',
            'enable_captcha' => 0, // Disable CAPTCHA for this test
        ));
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if a post was created
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'title' => 'Test Title',
        ));
        
        $this->assertCount(1, $posts);
        $post = $posts[0];
        
        // Check that script tags were sanitized from content
        // Note: wp_kses_post allows some HTML but removes scripts
        $this->assertStringNotContainsString('<script>', $post->post_content);
    }

    /**
     * Test SQL injection prevention.
     */
    public function test_sql_injection_prevention() {
        // Set up POST data with SQL injection attempt
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => "Test Title'; DROP TABLE wp_posts; --",
            'gpfs_content' => 'Test content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => '1',
            'gpfs_website' => '', // Honeypot field should be empty
        );
        
        // Set up options
        update_option('gpfs_options', array(
            'post_status' => 'draft',
            'enable_captcha' => 0, // Disable CAPTCHA for this test
        ));
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if posts table still exists
        $this->assertTrue($this->table_exists('posts'));
        
        // Check if a post was created with sanitized title
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'draft',
        ));
        
        $this->assertGreaterThan(0, count($posts));
    }

    /**
     * Test nonce validation in post approval.
     */
    public function test_approval_nonce_validation() {
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
     * Test nonce validation in post rejection.
     */
    public function test_rejection_nonce_validation() {
        // Set up GET parameters with invalid nonce
        $_GET = array(
            'action' => 'gpfs_reject_post',
            'post_id' => $this->post_id,
            'nonce' => 'invalid_nonce',
        );
        
        // Expect wp_die to be called
        $this->expectException('WPDieException');
        
        // Call reject_post method
        $this->notification->reject_post();
    }

    /**
     * Test CAPTCHA validation.
     */
    public function test_captcha_validation() {
        // Set up session for CAPTCHA
        if (!session_id()) {
            session_start();
        }
        $_SESSION['gpfs_captcha_answer'] = 10;
        
        // Set up POST data with incorrect CAPTCHA
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test Post Title',
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => '1',
            'gpfs_captcha' => '5', // Incorrect answer
            'gpfs_website' => '',
        );
        
        // Enable CAPTCHA in options
        update_option('gpfs_options', array('enable_captcha' => 1));
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        $this->redirect_url = null;
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if redirect happened with CAPTCHA error
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString('gpfs_error=captcha_invalid', $this->redirect_url);
    }

    /**
     * Test honeypot spam prevention.
     */
    public function test_honeypot_spam_prevention() {
        // Set up POST data with filled honeypot
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test Post Title',
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => '1',
            'gpfs_website' => 'http://spam.com', // Honeypot field filled = spam
        );
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        $this->redirect_url = null;
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if redirect happened with spam error
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString('gpfs_error=spam', $this->redirect_url);
    }

    /**
     * Helper method to check if a table exists.
     *
     * @param string $table Table name without prefix.
     * @return bool Whether the table exists.
     */
    protected function table_exists($table) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
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

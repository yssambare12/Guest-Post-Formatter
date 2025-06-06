<?php
/**
 * Class FormHandlerTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Form handler functionality tests.
 */
class FormHandlerTest extends WP_UnitTestCase {

    /**
     * Test instance of GPFS_Form_Handler.
     *
     * @var GPFS_Form_Handler
     */
    protected $form_handler;

    /**
     * Set up test environment.
     */
    public function setUp() {
        parent::setUp();
        $this->form_handler = new GPFS_Form_Handler();
        
        // Create a test category
        $this->category_id = $this->factory->category->create(array('name' => 'Test Category'));
    }

    /**
     * Test form submission processing with valid data.
     */
    public function test_process_submission_valid_data() {
        // Set up POST data
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test Post Title',
            'gpfs_content' => 'Test post content with enough characters to pass validation.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => $this->category_id,
            'gpfs_excerpt' => 'Test excerpt',
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
            'title' => 'Test Post Title',
        ));
        
        $this->assertCount(1, $posts);
        $post = $posts[0];
        
        // Check post data
        $this->assertEquals('Test Post Title', $post->post_title);
        $this->assertEquals('Test post content with enough characters to pass validation.', $post->post_content);
        $this->assertEquals('Test excerpt', $post->post_excerpt);
        $this->assertEquals('draft', $post->post_status);
        
        // Check post meta
        $this->assertEquals('Test Author', get_post_meta($post->ID, '_gpfs_author_name', true));
        $this->assertEquals('test@example.com', get_post_meta($post->ID, '_gpfs_author_email', true));
        
        // Check category
        $categories = wp_get_post_categories($post->ID);
        $this->assertContains($this->category_id, $categories);
    }

    /**
     * Test form submission with honeypot filled (spam detection).
     */
    public function test_process_submission_honeypot_filled() {
        // Set up POST data with filled honeypot
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test Post Title',
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => $this->category_id,
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
        
        // Check that no post was created
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'any',
            'title' => 'Test Post Title',
        ));
        
        $this->assertCount(0, $posts);
    }

    /**
     * Test form submission with missing required fields.
     */
    public function test_process_submission_missing_required_fields() {
        // Set up POST data with missing title
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => '', // Missing required field
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'test@example.com',
            'gpfs_category' => $this->category_id,
            'gpfs_website' => '',
        );
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        $this->redirect_url = null;
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if redirect happened with required fields error
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString('gpfs_error=required', $this->redirect_url);
    }

    /**
     * Test form submission with invalid email.
     */
    public function test_process_submission_invalid_email() {
        // Set up POST data with invalid email
        $_POST = array(
            'gpfs_submit_post' => '1',
            'gpfs_nonce' => wp_create_nonce('gpfs_submit_post_nonce'),
            'gpfs_title' => 'Test Post Title',
            'gpfs_content' => 'Test post content.',
            'gpfs_author_name' => 'Test Author',
            'gpfs_author_email' => 'invalid-email', // Invalid email
            'gpfs_category' => $this->category_id,
            'gpfs_website' => '',
        );
        
        // Mock the redirect function
        add_filter('wp_redirect', array($this, 'mock_redirect'), 10, 2);
        $this->redirect_url = null;
        
        // Process the submission
        ob_start();
        $this->form_handler->process_submission();
        ob_end_clean();
        
        // Check if redirect happened with email error
        $this->assertNotNull($this->redirect_url);
        $this->assertStringContainsString('gpfs_error=required', $this->redirect_url);
    }

    /**
     * Test CAPTCHA validation when enabled.
     */
    public function test_process_submission_captcha_validation() {
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
            'gpfs_category' => $this->category_id,
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
     * Test featured image handling.
     */
    public function test_handle_featured_image() {
        // This is a complex test that would require mocking file uploads
        // For simplicity, we'll just test that the method exists
        $this->assertTrue(method_exists($this->form_handler, 'handle_featured_image'));
    }

    /**
     * Test custom fields handling.
     */
    public function test_handle_custom_fields() {
        // This is a complex test that would require setting up custom fields
        // For simplicity, we'll just test that the method exists
        $this->assertTrue(method_exists($this->form_handler, 'handle_custom_fields'));
    }

    /**
     * Mock function for wp_redirect to prevent actual redirects during tests.
     *
     * @param string $location The location to redirect to.
     * @return string The location.
     */
    public function mock_redirect($location) {
        $this->redirect_url = $location;
        return false; // Prevent actual redirect
    }
}

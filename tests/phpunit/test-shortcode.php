<?php
/**
 * Class ShortcodeTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Shortcode functionality tests.
 */
class ShortcodeTest extends WP_UnitTestCase {

    /**
     * Test instance of GPFS_Shortcode.
     *
     * @var GPFS_Shortcode
     */
    protected $shortcode;

    /**
     * Set up test environment.
     */
    public function setUp() {
        parent::setUp();
        $this->shortcode = new GPFS_Shortcode();
    }

    /**
     * Test that the shortcode is registered.
     */
    public function test_shortcode_registration() {
        // Register the shortcode
        $this->shortcode->register_shortcode();
        
        // Check if shortcode is registered
        $this->assertTrue(shortcode_exists('guest_post_form'));
    }

    /**
     * Test shortcode rendering with default attributes.
     */
    public function test_shortcode_render_default() {
        // Get shortcode output
        $output = $this->shortcode->render_form(array());
        
        // Check if output contains expected elements
        $this->assertStringContainsString('id="gpfs-submission-form"', $output);
        $this->assertStringContainsString('name="gpfs_title"', $output);
        $this->assertStringContainsString('name="gpfs_content"', $output);
        $this->assertStringContainsString('name="gpfs_author_name"', $output);
        $this->assertStringContainsString('name="gpfs_author_email"', $output);
        $this->assertStringContainsString('name="gpfs_category"', $output);
        $this->assertStringContainsString('name="gpfs_excerpt"', $output);
        $this->assertStringContainsString('name="gpfs_featured_image"', $output);
        $this->assertStringContainsString('name="gpfs_submit_post"', $output);
        $this->assertStringContainsString('name="gpfs_nonce"', $output);
    }

    /**
     * Test shortcode rendering with custom attributes.
     */
    public function test_shortcode_render_custom_attributes() {
        // Define custom attributes
        $atts = array(
            'title' => 'Custom Title',
            'success_message' => 'Custom Success Message',
            'button_text' => 'Custom Button',
            'show_excerpt' => 'no',
            'show_featured_image' => 'no',
        );
        
        // Get shortcode output with custom attributes
        $output = $this->shortcode->render_form($atts);
        
        // Check if output contains custom elements
        $this->assertStringContainsString('Custom Title', $output);
        $this->assertStringContainsString('Custom Button', $output);
        $this->assertStringNotContainsString('name="gpfs_excerpt"', $output);
        $this->assertStringNotContainsString('name="gpfs_featured_image"', $output);
    }

    /**
     * Test CAPTCHA rendering when enabled.
     */
    public function test_captcha_rendering_when_enabled() {
        // Set CAPTCHA to enabled in options
        update_option('gpfs_options', array('enable_captcha' => 1));
        
        // Get shortcode output
        $output = $this->shortcode->render_form(array());
        
        // Check if CAPTCHA is rendered
        $this->assertStringContainsString('gpfs-captcha-field', $output);
        $this->assertStringContainsString('name="gpfs_captcha"', $output);
    }

    /**
     * Test CAPTCHA not rendering when disabled.
     */
    public function test_captcha_not_rendering_when_disabled() {
        // Set CAPTCHA to disabled in options
        update_option('gpfs_options', array('enable_captcha' => 0));
        
        // Get shortcode output
        $output = $this->shortcode->render_form(array());
        
        // Check if CAPTCHA is not rendered
        $this->assertStringNotContainsString('name="gpfs_captcha"', $output);
    }

    /**
     * Test error message generation.
     */
    public function test_error_message_generation() {
        // Test various error types
        $error_types = array(
            'required' => 'Please fill in all required fields.',
            'insert' => 'There was an error submitting your post.',
            'spam' => 'Your submission was flagged as spam.',
            'email' => 'Please enter a valid email address.',
            'captcha_missing' => 'Please answer the security question.',
            'captcha_invalid' => 'The security answer is incorrect.',
            'unknown' => 'An unknown error occurred.',
        );
        
        foreach ($error_types as $type => $expected_text) {
            $message = $this->invoke_protected_method($this->shortcode, 'get_error_message', array($type));
            $this->assertStringContainsString($expected_text, $message);
        }
    }

    /**
     * Test login required message when user is not logged in.
     */
    public function test_login_required_message() {
        // Set require_login to true in options
        update_option('gpfs_options', array('require_login' => 1));
        
        // Ensure user is logged out
        wp_set_current_user(0);
        
        // Get shortcode output
        $output = $this->shortcode->render_form(array());
        
        // Check if login required message is displayed
        $this->assertStringContainsString('gpfs-login-required', $output);
        $this->assertStringContainsString('You must be logged in', $output);
    }

    /**
     * Helper method to invoke protected methods.
     *
     * @param object $object     Object instance.
     * @param string $method     Method name.
     * @param array  $parameters Method parameters.
     * @return mixed             Method result.
     */
    protected function invoke_protected_method($object, $method, $parameters = array()) {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}

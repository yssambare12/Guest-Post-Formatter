<?php
/**
 * Class CoreTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Core functionality tests.
 */
class CoreTest extends WP_UnitTestCase {

    /**
     * Test instance of GPFS_Core.
     *
     * @var GPFS_Core
     */
    protected $core;

    /**
     * Set up test environment.
     */
    public function setUp() {
        parent::setUp();
        $this->core = new GPFS_Core();
    }

    /**
     * Test that the plugin initializes properly.
     */
    public function test_plugin_initialization() {
        $this->assertInstanceOf(GPFS_Core::class, $this->core);
        $this->assertInstanceOf(GPFS_Loader::class, $this->get_protected_property($this->core, 'loader'));
    }

    /**
     * Test that admin hooks are registered.
     */
    public function test_admin_hooks_registered() {
        global $wp_filter;
        
        // Call the method that registers admin hooks
        $this->invoke_protected_method($this->core, 'define_admin_hooks');
        
        // Check if admin_menu hook is registered
        $this->assertTrue(has_action('admin_menu', array($this->core, 'add_admin_menu')));
        
        // Check if admin_init hook is registered
        $this->assertTrue(has_action('admin_init', array($this->core, 'register_settings')));
    }

    /**
     * Test that public hooks are registered.
     */
    public function test_public_hooks_registered() {
        global $wp_filter;
        
        // Call the method that registers public hooks
        $this->invoke_protected_method($this->core, 'define_public_hooks');
        
        // Check if wp_enqueue_scripts hooks are registered
        $this->assertTrue(has_action('wp_enqueue_scripts', array($this->core, 'enqueue_styles')));
        $this->assertTrue(has_action('wp_enqueue_scripts', array($this->core, 'enqueue_scripts')));
    }

    /**
     * Test that settings are registered correctly.
     */
    public function test_register_settings() {
        global $wp_registered_settings;
        
        // Call the method to register settings
        $this->core->register_settings();
        
        // Check if our settings are registered
        $this->assertArrayHasKey('gpfs_options', $wp_registered_settings);
    }

    /**
     * Test settings validation.
     */
    public function test_validate_settings() {
        // Create test input
        $input = array(
            'post_status' => 'draft',
            'post_category' => '5',
            'enable_notifications' => '1',
            'enable_captcha' => '1',
            'notification_email' => 'test@example.com',
            'email_subject' => 'Test Subject',
            'email_message' => 'Test Message',
        );
        
        // Validate settings
        $output = $this->invoke_protected_method($this->core, 'validate_settings', array($input));
        
        // Check if validation works correctly
        $this->assertEquals('draft', $output['post_status']);
        $this->assertEquals(5, $output['post_category']);
        $this->assertEquals(1, $output['enable_notifications']);
        $this->assertEquals(1, $output['enable_captcha']);
        $this->assertEquals('test@example.com', $output['notification_email']);
        $this->assertEquals('Test Subject', $output['email_subject']);
        $this->assertEquals('Test Message', $output['email_message']);
    }

    /**
     * Test invalid email validation.
     */
    public function test_validate_settings_invalid_email() {
        // Create test input with invalid email
        $input = array(
            'notification_email' => 'invalid-email',
        );
        
        // Validate settings
        $output = $this->invoke_protected_method($this->core, 'validate_settings', array($input));
        
        // Check if validation falls back to admin email
        $this->assertEquals(get_option('admin_email'), $output['notification_email']);
    }

    /**
     * Test that styles are enqueued correctly.
     */
    public function test_enqueue_styles() {
        // Enqueue styles
        $this->core->enqueue_styles();
        
        // Check if styles are enqueued
        $this->assertTrue(wp_style_is('guest-post-frontend-submitter', 'registered'));
    }

    /**
     * Test that scripts are enqueued correctly.
     */
    public function test_enqueue_scripts() {
        // Enqueue scripts
        $this->core->enqueue_scripts();
        
        // Check if scripts are enqueued
        $this->assertTrue(wp_script_is('guest-post-frontend-submitter', 'registered'));
    }

    /**
     * Helper method to access protected properties.
     *
     * @param object $object     Object instance.
     * @param string $property   Property name.
     * @return mixed             Property value.
     */
    protected function get_protected_property($object, $property) {
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
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

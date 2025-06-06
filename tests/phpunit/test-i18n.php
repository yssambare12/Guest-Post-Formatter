<?php
/**
 * Class I18nTest
 *
 * @package Guest_Post_Frontend_Submitter
 */

/**
 * Internationalization tests.
 */
class I18nTest extends WP_UnitTestCase {

    /**
     * Test that the plugin is translation-ready.
     */
    public function test_plugin_textdomain() {
        // Check if the textdomain is loaded
        $this->assertTrue(is_textdomain_loaded('guest-post-frontend-submitter'));
    }

    /**
     * Test that the plugin has a POT file.
     */
    public function test_pot_file_exists() {
        $pot_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'languages/guest-post-frontend-submitter.pot';
        $this->assertFileExists($pot_file);
    }

    /**
     * Test that the POT file contains essential strings.
     */
    public function test_pot_file_content() {
        $pot_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'languages/guest-post-frontend-submitter.pot';
        $pot_content = file_get_contents($pot_file);
        
        // Check for essential strings
        $this->assertStringContainsString('msgid "Guest Post Frontend Submitter"', $pot_content);
        $this->assertStringContainsString('msgid "Submit a Guest Post"', $pot_content);
        $this->assertStringContainsString('msgid "Thank you! Your post has been submitted successfully."', $pot_content);
    }

    /**
     * Test that the plugin uses translation functions.
     */
    public function test_translation_functions_used() {
        // Get the main plugin file
        $plugin_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'guest-post-frontend-submitter.php';
        $plugin_content = file_get_contents($plugin_file);
        
        // Check for translation function usage
        $this->assertStringContainsString('load_plugin_textdomain', $plugin_content);
        
        // Check a few key files for translation functions
        $files_to_check = array(
            'includes/class-gpfs-shortcode.php',
            'includes/class-gpfs-form-handler.php',
            'includes/class-gpfs-notification.php',
            'includes/class-gpfs-core.php',
        );
        
        foreach ($files_to_check as $file) {
            $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . $file;
            $file_content = file_get_contents($file_path);
            
            // Check for __() or _e() functions
            $this->assertTrue(
                preg_match('/__(\'|")/', $file_content) > 0 || 
                preg_match('/_e(\'|")/', $file_content) > 0,
                "File $file does not use translation functions"
            );
        }
    }

    /**
     * Test that the plugin has the correct text domain.
     */
    public function test_text_domain_consistency() {
        // Get the main plugin file
        $plugin_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'guest-post-frontend-submitter.php';
        $plugin_content = file_get_contents($plugin_file);
        
        // Check for text domain in plugin header
        $this->assertStringContainsString("Text Domain: guest-post-frontend-submitter", $plugin_content);
        
        // Check for domain path in plugin header
        $this->assertStringContainsString("Domain Path: /languages", $plugin_content);
        
        // Check a few key files for consistent text domain
        $files_to_check = array(
            'includes/class-gpfs-shortcode.php',
            'includes/class-gpfs-form-handler.php',
            'includes/class-gpfs-notification.php',
            'includes/class-gpfs-core.php',
        );
        
        foreach ($files_to_check as $file) {
            $file_path = plugin_dir_path(dirname(dirname(__FILE__))) . $file;
            $file_content = file_get_contents($file_path);
            
            // Check for text domain in translation functions
            $this->assertTrue(
                preg_match('/__(\'|")[^\'"]+(\'|"), \'guest-post-frontend-submitter\'/', $file_content) > 0 || 
                preg_match('/_e(\'|")[^\'"]+(\'|"), \'guest-post-frontend-submitter\'/', $file_content) > 0,
                "File $file does not use the correct text domain"
            );
        }
    }
}

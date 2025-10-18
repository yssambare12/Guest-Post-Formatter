<?php
/**
 * Plugin Name: Guest Post Frontend Submitter
 * Plugin URI: https://example.com/plugins/guest-post-frontend-submitter
 * Description: Enables front-end guest post submission with a simple shortcode.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: guest-post-frontend-submitter
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants
define( 'GPFS_VERSION', '1.0.0' );
define( 'GPFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GPFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GPFS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin textdomain.
 */
function gpfs_load_textdomain() {
    load_plugin_textdomain( 'guest-post-frontend-submitter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'gpfs_load_textdomain' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-loader.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-core.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-form-handler.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-shortcode.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-notification.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-react-form.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-anti-spam.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-email-marketing.php';
require_once GPFS_PLUGIN_DIR . 'admin/class-gpfs-admin.php';

/**
 * Begins execution of the plugin.
 */
function run_guest_post_frontend_submitter() {
    $plugin = new GPFS_Core();
    $plugin->run();
    
    // Initialize admin
    if (is_admin()) {
        $admin = new GPFS_Admin();
    }
    
    // Initialize React form handler
    global $gpfs_react_form;
    $gpfs_react_form = new GPFS_React_Form();
    
    // Initialize anti-spam
    new GPFS_Anti_Spam();
    
    // Initialize email marketing
    new GPFS_Email_Marketing();
}
run_guest_post_frontend_submitter();

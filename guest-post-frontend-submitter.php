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
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-loader.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-core.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-form-handler.php';
require_once GPFS_PLUGIN_DIR . 'includes/class-gpfs-shortcode.php';

/**
 * Begins execution of the plugin.
 */
function run_guest_post_frontend_submitter() {
    $plugin = new GPFS_Core();
    $plugin->run();
}
run_guest_post_frontend_submitter();

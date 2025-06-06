<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/includes
 */

class GPFS_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      GPFS_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->loader = new GPFS_Loader();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Add admin menu, settings, etc.
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Initialize shortcode
        $shortcode = new GPFS_Shortcode();
        $this->loader->add_action('init', $shortcode, 'register_shortcode');
        
        // Initialize form handler
        $form_handler = new GPFS_Form_Handler();
        $this->loader->add_action('init', $form_handler, 'process_submission');
    }

    /**
     * Add admin menu for the plugin.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_options_page(
            __('Guest Post Frontend Submitter', 'guest-post-frontend-submitter'),
            __('Guest Post Submitter', 'guest-post-frontend-submitter'),
            'manage_options',
            'guest-post-frontend-submitter',
            array($this, 'display_admin_page')
        );
    }

    /**
     * Register settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'gpfs_settings',
            'gpfs_options',
            array($this, 'validate_settings')
        );

        add_settings_section(
            'gpfs_general_settings',
            __('General Settings', 'guest-post-frontend-submitter'),
            array($this, 'general_settings_callback'),
            'guest-post-frontend-submitter'
        );

        add_settings_field(
            'post_status',
            __('Default Post Status', 'guest-post-frontend-submitter'),
            array($this, 'post_status_callback'),
            'guest-post-frontend-submitter',
            'gpfs_general_settings'
        );

        add_settings_field(
            'post_category',
            __('Default Category', 'guest-post-frontend-submitter'),
            array($this, 'post_category_callback'),
            'guest-post-frontend-submitter',
            'gpfs_general_settings'
        );
    }

    /**
     * Display the admin page.
     *
     * @since    1.0.0
     */
    public function display_admin_page() {
        include_once GPFS_PLUGIN_DIR . 'admin/partials/admin-display.php';
    }

    /**
     * General settings section callback.
     *
     * @since    1.0.0
     */
    public function general_settings_callback() {
        echo '<p>' . __('Configure the general settings for the guest post submission form.', 'guest-post-frontend-submitter') . '</p>';
    }

    /**
     * Post status field callback.
     *
     * @since    1.0.0
     */
    public function post_status_callback() {
        $options = get_option('gpfs_options');
        $post_status = isset($options['post_status']) ? $options['post_status'] : 'pending';
        ?>
        <select name="gpfs_options[post_status]">
            <option value="pending" <?php selected($post_status, 'pending'); ?>><?php _e('Pending', 'guest-post-frontend-submitter'); ?></option>
            <option value="draft" <?php selected($post_status, 'draft'); ?>><?php _e('Draft', 'guest-post-frontend-submitter'); ?></option>
            <option value="publish" <?php selected($post_status, 'publish'); ?>><?php _e('Published', 'guest-post-frontend-submitter'); ?></option>
        </select>
        <p class="description"><?php _e('Select the default status for submitted posts.', 'guest-post-frontend-submitter'); ?></p>
        <?php
    }

    /**
     * Post category field callback.
     *
     * @since    1.0.0
     */
    public function post_category_callback() {
        $options = get_option('gpfs_options');
        $post_category = isset($options['post_category']) ? $options['post_category'] : '';
        
        wp_dropdown_categories(array(
            'name' => 'gpfs_options[post_category]',
            'selected' => $post_category,
            'show_option_none' => __('Select Category', 'guest-post-frontend-submitter'),
            'option_none_value' => '',
        ));
        
        echo '<p class="description">' . __('Select the default category for submitted posts.', 'guest-post-frontend-submitter') . '</p>';
    }

    /**
     * Validate settings.
     *
     * @since    1.0.0
     * @param    array    $input    The input options.
     * @return   array              The validated options.
     */
    public function validate_settings($input) {
        $output = array();
        
        if (isset($input['post_status'])) {
            $output['post_status'] = sanitize_text_field($input['post_status']);
        }
        
        if (isset($input['post_category'])) {
            $output['post_category'] = absint($input['post_category']);
        }
        
        return $output;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'guest-post-frontend-submitter',
            GPFS_PLUGIN_URL . 'assets/css/guest-post-frontend-submitter-public.css',
            array(),
            GPFS_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'guest-post-frontend-submitter',
            GPFS_PLUGIN_URL . 'assets/js/guest-post-frontend-submitter-public.js',
            array('jquery'),
            GPFS_VERSION,
            false
        );
        
        wp_localize_script(
            'guest-post-frontend-submitter',
            'gpfs_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gpfs_nonce')
            )
        );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
}

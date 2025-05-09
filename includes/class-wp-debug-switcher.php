<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://fitzport.com
 * @since      1.0.0
 *
 * @package    Wp_Debug_Switcher
 * @subpackage Wp_Debug_Switcher/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Wp_Debug_Switcher
 * @subpackage Wp_Debug_Switcher/includes
 * @author     Fitzport <info@fitzport.com>
 */
class Wp_Debug_Switcher {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Wp_Debug_Switcher_Admin    $admin    Maintains and registers all hooks for the admin area.
     */
    protected $admin;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-debug-switcher-admin.php';
        
        $this->admin = new Wp_Debug_Switcher_Admin();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Add admin menu
        add_action( 'admin_menu', array( $this->admin, 'add_admin_menu' ) );
        
        // Register settings
        add_action( 'admin_init', array( $this->admin, 'register_settings' ) );
        
        // Enqueue styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_scripts' ) );
        
        // Register AJAX handler for log viewer
        add_action( 'wp_ajax_debug_log_viewer', array( $this->admin, 'debug_log_viewer_callback' ) );
        
        // Add footer attribution if enabled - use priority 1 to ensure it loads before other footer content
        add_action( 'wp_footer', array( $this, 'add_footer_attribution' ), 1 );
        
        // Also add to admin footer
        add_action( 'admin_footer', array( $this, 'add_footer_attribution' ), 1 );
    }
    
    /**
     * Add attribution link to the footer if enabled
     *
     * @since    1.0.0
     */
    public function add_footer_attribution() {
        $options = get_option( 'wp_debug_switcher_options' );
        
        // Only show attribution if enabled
        if ( isset( $options['show_attribution'] ) && $options['show_attribution'] ) {
            // Use a high priority (0) to display at the very top of footer
            // and output before other footer content without removing it
            echo '<div id="wp-debug-switcher-attribution" style="text-align: center; font-size: 10px; margin: 10px 0; padding: 5px; opacity: 0.7; width: 100%; display: block; clear: both; position: relative; z-index: 999;">';
            echo 'Powered by <a href="https://fitzport.com" target="_blank" rel="dofollow" style="color: inherit; text-decoration: none;">fitzport.com</a>';
            echo '</div>';
        }
    }

    /**
     * Run the plugin.
     *
     * @since    1.0.0
     */
    public function run() {
        // Define PHP constant filter hooks to inject our debug settings
        add_filter( 'wp_debug_enabled', array( $this, 'filter_wp_debug' ) );
        add_filter( 'wp_debug_log_enabled', array( $this, 'filter_wp_debug_log' ) );
        add_filter( 'script_debug_enabled', array( $this, 'filter_script_debug' ) );
        
        // Apply debug settings using pre_option hooks
        add_filter( 'pre_option_wp_debug', array( $this, 'filter_wp_debug' ) );
        add_filter( 'pre_option_wp_debug_log', array( $this, 'filter_wp_debug_log' ) );
        add_filter( 'pre_option_script_debug', array( $this, 'filter_script_debug' ) );
        
        // Hook into PHP errors if needed
        add_action( 'activated_plugin', array( $this, 'apply_debug_settings' ) );
        add_action( 'deactivated_plugin', array( $this, 'restore_default_settings' ) );
        
        // Apply debug settings immediately
        $this->apply_debug_settings();
    }
    
    /**
     * Filter WP_DEBUG constant
     *
     * @since    1.0.0
     * @return   bool    Whether WP_DEBUG should be enabled
     */
    public function filter_wp_debug() {
        $options = get_option( 'wp_debug_switcher_options', array() );
        return isset( $options['wp_debug'] ) ? (bool) $options['wp_debug'] : false;
    }
    
    /**
     * Filter WP_DEBUG_LOG constant
     *
     * @since    1.0.0
     * @return   bool    Whether WP_DEBUG_LOG should be enabled
     */
    public function filter_wp_debug_log() {
        $options = get_option( 'wp_debug_switcher_options', array() );
        return isset( $options['wp_debug_log'] ) ? (bool) $options['wp_debug_log'] : false;
    }
    
    /**
     * Filter SCRIPT_DEBUG constant
     *
     * @since    1.0.0
     * @return   bool    Whether SCRIPT_DEBUG should be enabled
     */
    public function filter_script_debug() {
        $options = get_option( 'wp_debug_switcher_options', array() );
        return isset( $options['script_debug'] ) ? (bool) $options['script_debug'] : false;
    }
    
    /**
     * Apply debug settings in runtime
     *
     * @since    1.0.0
     */
    public function apply_debug_settings() {
        $options = get_option( 'wp_debug_switcher_options', array() );
        
        // Force define constants if not already defined
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', isset($options['wp_debug']) ? (bool)$options['wp_debug'] : false);
        }
        
        if (!defined('WP_DEBUG_LOG')) {
            define('WP_DEBUG_LOG', isset($options['wp_debug_log']) ? (bool)$options['wp_debug_log'] : false);
        }
        
        if (!defined('SCRIPT_DEBUG')) {
            define('SCRIPT_DEBUG', isset($options['script_debug']) ? (bool)$options['script_debug'] : false);
        }
        
        // If constants are already defined but we need to override their behavior
        // we can use a custom error handler
        if (defined('WP_DEBUG') && WP_DEBUG !== (bool)$options['wp_debug']) {
            // Setup custom error handling if needed
            if (isset($options['wp_debug']) && (bool)$options['wp_debug']) {
                // Enable error reporting
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
            } else {
                // Disable error reporting
                error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
                ini_set('display_errors', 0);
            }
        }
        
        // Setup log file handling
        if (isset($options['wp_debug_log']) && (bool)$options['wp_debug_log']) {
            ini_set('log_errors', 1);
            ini_set('error_log', WP_CONTENT_DIR . '/debug.log');
        }
    }
    
    /**
     * Restore default settings when deactivated
     *
     * @since    1.0.0
     */
    public function restore_default_settings() {
        // Turn off all debug options when plugin is deactivated
        ini_set('display_errors', 0);
        ini_set('log_errors', 0);
        error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
    }
}

<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           Wp_Debug_Switcher
 *
 * @wordpress-plugin
 * Plugin Name:       WP Debug Mode Switcher
 * Plugin URI:        https://fitzport.com/wp-debug-switcher
 * Description:       Enable or disable WordPress debugging modes (WP_DEBUG, WP_DEBUG_LOG, SCRIPT_DEBUG) directly from the WordPress admin dashboard without editing wp-config.php.
 * Version:           1.0.0
 * Author:            Fitzport
 * Author URI:        https://fitzport.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-debug-switcher
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'WP_DEBUG_SWITCHER_VERSION', '1.0.0' );
define( 'WP_DEBUG_SWITCHER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_DEBUG_SWITCHER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-debug-switcher.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_debug_switcher() {

    $plugin = new Wp_Debug_Switcher();
    $plugin->run();

}
// Set up default options on plugin activation with attribution enabled by default
function wp_debug_switcher_set_default_options() {
    // Only set options if they don't already exist
    if ( ! get_option( 'wp_debug_switcher_options' ) ) {
        $default_options = array(
            'wp_debug'        => false,
            'wp_debug_log'    => false,
            'script_debug'    => false,
            'show_attribution' => true // Attribution is enabled by default
        );
        
        update_option( 'wp_debug_switcher_options', $default_options );
    }
}
register_activation_hook( __FILE__, 'wp_debug_switcher_set_default_options' );

run_wp_debug_switcher();

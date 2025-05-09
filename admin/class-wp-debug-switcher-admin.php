<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://fitzport.com
 * @since      1.0.0
 *
 * @package    Wp_Debug_Switcher
 * @subpackage Wp_Debug_Switcher/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Debug_Switcher
 * @subpackage Wp_Debug_Switcher/admin
 * @author     Fitzport <info@fitzport.com>
 */
class Wp_Debug_Switcher_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Initialize default options if they don't exist
        $this->maybe_initialize_options();
    }

    /**
     * Initialize default options if they don't exist
     *
     * @since    1.0.0
     */
    private function maybe_initialize_options() {
        if ( false === get_option( 'wp_debug_switcher_options' ) ) {
            $defaults = array(
                'wp_debug'          => false,
                'wp_debug_log'      => false,
                'script_debug'      => false,
                'show_attribution'  => true,  // Attribution is ON by default
            );
            update_option( 'wp_debug_switcher_options', $defaults );
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles( $hook ) {
        // Only load styles on our settings page
        if ( 'tools_page_wp-debug-switcher' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-debug-switcher-admin', plugin_dir_url( __FILE__ ) . 'css/wp-debug-switcher-admin.css', array(), WP_DEBUG_SWITCHER_VERSION, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook ) {
        // Only load scripts on our settings page
        if ( 'tools_page_wp-debug-switcher' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'wp-debug-switcher-admin', plugin_dir_url( __FILE__ ) . 'js/wp-debug-switcher-admin.js', array( 'jquery' ), WP_DEBUG_SWITCHER_VERSION, false );
        
        // Pass AJAX URL and nonce to JavaScript
        wp_localize_script( 'wp-debug-switcher-admin', 'wpDebugSwitcher', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wp_debug_switcher_nonce' ),
        ) );
    }

    /**
     * Add menu item for the plugin
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'WP Debug Switcher',
            'Debug Switcher',
            'manage_options',
            'wp-debug-switcher',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Register settings
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'wp_debug_switcher_settings',
            'wp_debug_switcher_options',
            array( $this, 'sanitize_options' )
        );

        add_settings_section(
            'wp_debug_switcher_main_section',
            'Debug Settings',
            array( $this, 'settings_section_callback' ),
            'wp_debug_switcher_settings'
        );

        add_settings_field(
            'wp_debug',
            'WP_DEBUG',
            array( $this, 'wp_debug_callback' ),
            'wp_debug_switcher_settings',
            'wp_debug_switcher_main_section'
        );

        add_settings_field(
            'wp_debug_log',
            'WP_DEBUG_LOG',
            array( $this, 'wp_debug_log_callback' ),
            'wp_debug_switcher_settings',
            'wp_debug_switcher_main_section'
        );

        add_settings_field(
            'script_debug',
            'SCRIPT_DEBUG',
            array( $this, 'script_debug_callback' ),
            'wp_debug_switcher_settings',
            'wp_debug_switcher_main_section'
        );
        
        // Attribution settings section
        add_settings_section(
            'wp_debug_switcher_attribution_section',
            '',  // No title for this section
            array( $this, 'attribution_section_callback' ),
            'wp_debug_switcher_settings'
        );
        
        add_settings_field(
            'show_attribution',
            '',  // No label for this field
            array( $this, 'show_attribution_callback' ),
            'wp_debug_switcher_settings',
            'wp_debug_switcher_attribution_section'
        );
    }

    /**
     * Sanitize options
     *
     * @since    1.0.0
     * @param    array    $input    The input array to sanitize.
     * @return   array              The sanitized input.
     */
    public function sanitize_options( $input ) {
        $sanitized_input = array();
        
        // Sanitize WP_DEBUG
        $sanitized_input['wp_debug'] = isset( $input['wp_debug'] ) ? (bool) $input['wp_debug'] : false;
        
        // Sanitize WP_DEBUG_LOG
        $sanitized_input['wp_debug_log'] = isset( $input['wp_debug_log'] ) ? (bool) $input['wp_debug_log'] : false;
        
        // Sanitize SCRIPT_DEBUG
        $sanitized_input['script_debug'] = isset( $input['script_debug'] ) ? (bool) $input['script_debug'] : false;
        
        // Sanitize show_attribution
        $sanitized_input['show_attribution'] = isset( $input['show_attribution'] ) ? (bool) $input['show_attribution'] : false;
        
        return $sanitized_input;
    }
    
    /**
     * Attribution section callback
     *
     * @since    1.0.0
     */
    public function attribution_section_callback() {
        // Empty - no output needed
    }
    
    /**
     * Show attribution callback
     *
     * @since    1.0.0
     */
    public function show_attribution_callback() {
        $options = get_option( 'wp_debug_switcher_options' );
        $checked = isset( $options['show_attribution'] ) ? $options['show_attribution'] : false;
        
        echo '<div class="attribution-option">';
        echo '<label class="attribution-switch">';
        echo '<input type="checkbox" name="wp_debug_switcher_options[show_attribution]" ' . checked( $checked, true, false ) . ' value="1">';
        echo '<span class="attribution-text">Display "Powered by <a href="https://fitzport.com" target="_blank" rel="dofollow">fitzport.com</a>" in site footer (enabled by default)</span>';
        echo '</label>';
        echo '</div>';
    }

    /**
     * Settings section callback
     *
     * @since    1.0.0
     */
    public function settings_section_callback() {
        echo '<p>Toggle WordPress debugging options directly from the dashboard - no wp-config.php editing required!</p>';
        echo '<p>These settings will be applied automatically and immediately upon changing them. The plugin handles all the necessary configuration changes for you.</p>';
        echo '<div class="notice notice-success inline">';
        echo '<p><span class="dashicons dashicons-yes"></span> <strong>No configuration needed:</strong> This plugin works instantly after activation - no need to edit any files!</p>';
        echo '</div>';
    }

    /**
     * WP_DEBUG callback
     *
     * @since    1.0.0
     */
    public function wp_debug_callback() {
        $options = get_option( 'wp_debug_switcher_options' );
        $checked = isset( $options['wp_debug'] ) ? $options['wp_debug'] : false;
        $status_class = $checked ? 'status-on' : 'status-off';
        $status_text = $checked ? 'Enabled' : 'Disabled';
        
        echo '<label class="switch">';
        echo '<input type="checkbox" name="wp_debug_switcher_options[wp_debug]" ' . checked( $checked, true, false ) . ' value="1">';
        echo '<span class="slider round"></span>';
        echo '</label>';
        echo '<span class="status-indicator ' . esc_attr( $status_class ) . '">' . esc_html( $status_text ) . '</span>';
        echo '<p class="description">Enables WordPress debug mode. Shows PHP errors, warnings, and notices.</p>';
        
        // Show actual current setting 
        $actual_setting = defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled';
        echo '<p class="actual-setting">Current active setting: <strong>' . esc_html( $actual_setting ) . '</strong></p>';
    }

    /**
     * WP_DEBUG_LOG callback
     *
     * @since    1.0.0
     */
    public function wp_debug_log_callback() {
        $options = get_option( 'wp_debug_switcher_options' );
        $checked = isset( $options['wp_debug_log'] ) ? $options['wp_debug_log'] : false;
        $status_class = $checked ? 'status-on' : 'status-off';
        $status_text = $checked ? 'Enabled' : 'Disabled';
        
        echo '<label class="switch">';
        echo '<input type="checkbox" name="wp_debug_switcher_options[wp_debug_log]" ' . checked( $checked, true, false ) . ' value="1">';
        echo '<span class="slider round"></span>';
        echo '</label>';
        echo '<span class="status-indicator ' . esc_attr( $status_class ) . '">' . esc_html( $status_text ) . '</span>';
        echo '<p class="description">Logs all PHP errors to wp-content/debug.log file.</p>';
        
        // Show actual current setting
        $actual_setting = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? 'Enabled' : 'Disabled';
        echo '<p class="actual-setting">Current active setting: <strong>' . esc_html( $actual_setting ) . '</strong></p>';
    }

    /**
     * SCRIPT_DEBUG callback
     *
     * @since    1.0.0
     */
    public function script_debug_callback() {
        $options = get_option( 'wp_debug_switcher_options' );
        $checked = isset( $options['script_debug'] ) ? $options['script_debug'] : false;
        $status_class = $checked ? 'status-on' : 'status-off';
        $status_text = $checked ? 'Enabled' : 'Disabled';
        
        echo '<label class="switch">';
        echo '<input type="checkbox" name="wp_debug_switcher_options[script_debug]" ' . checked( $checked, true, false ) . ' value="1">';
        echo '<span class="slider round"></span>';
        echo '</label>';
        echo '<span class="status-indicator ' . esc_attr( $status_class ) . '">' . esc_html( $status_text ) . '</span>';
        echo '<p class="description">Loads unminified versions of JavaScript and CSS files. Useful for debugging.</p>';
        
        // Show actual current setting
        $actual_setting = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'Enabled' : 'Disabled';
        echo '<p class="actual-setting">Current active setting: <strong>' . esc_html( $actual_setting ) . '</strong></p>';
    }

    /**
     * Display the admin settings page
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Show error/update messages
        settings_errors( 'wp_debug_switcher_messages' );
        
        // Get current debug status
        $wp_debug_status = defined('WP_DEBUG') && WP_DEBUG ? 'enabled' : 'disabled';
        $debug_log_status = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'enabled' : 'disabled';
        $script_debug_status = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'enabled' : 'disabled';
        ?>
        <div class="wrap">
            <div class="wp-debug-switcher-header">
                <h1><span class="dashicons dashicons-admin-tools"></span> <?php echo esc_html( get_admin_page_title() ); ?></h1>
                <div class="wp-debug-status-summary">
                    <div class="status-item <?php echo $wp_debug_status === 'enabled' ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="dashicons <?php echo $wp_debug_status === 'enabled' ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                        <span class="status-label">WP_DEBUG</span>
                    </div>
                    <div class="status-item <?php echo $debug_log_status === 'enabled' ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="dashicons <?php echo $debug_log_status === 'enabled' ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                        <span class="status-label">WP_DEBUG_LOG</span>
                    </div>
                    <div class="status-item <?php echo $script_debug_status === 'enabled' ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="dashicons <?php echo $script_debug_status === 'enabled' ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                        <span class="status-label">SCRIPT_DEBUG</span>
                    </div>
                </div>
            </div>
            
            <div class="wp-debug-intro">
                <p>This plugin allows you to toggle WordPress debugging options <strong>directly from your dashboard</strong> - no file editing required! Simply activate the plugin, adjust your settings below, and the changes will take effect immediately.</p>
                <p>A small "Powered by fitzport.com" attribution is shown in your site footer by default, but can be disabled in the settings below if needed.</p>
            </div>
            
            <div class="wp-debug-switcher-container">
                <div class="wp-debug-switcher-settings">
                    <div class="wp-debug-card-header">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <h2>Debug Settings</h2>
                    </div>
                    <form action="options.php" method="post">
                        <?php
                        settings_fields( 'wp_debug_switcher_settings' );
                        do_settings_sections( 'wp_debug_switcher_settings' );
                        submit_button( 'Save Debug Settings', 'primary', 'submit', false, array( 'id' => 'save-debug-settings' ) );
                        ?>
                    </form>
                </div>
                
                <div class="wp-debug-switcher-log-viewer">
                    <div class="wp-debug-card-header">
                        <span class="dashicons dashicons-text-page"></span>
                        <h2>Debug Log Viewer</h2>
                    </div>
                    
                    <?php if ( $this->debug_log_exists() ): ?>
                        <div class="log-actions">
                            <button id="refresh-log" class="button button-secondary">
                                <span class="dashicons dashicons-update"></span> Refresh Log
                            </button>
                            <button id="clear-log" class="button button-secondary">
                                <span class="dashicons dashicons-trash"></span> Clear Log
                            </button>
                        </div>
                        <div id="debug-log-content" class="debug-log-content">
                            <div class="loading">
                                <span class="spinner is-active"></span>
                                Loading log file...
                            </div>
                        </div>
                        <div class="log-file-info">
                            <span class="dashicons dashicons-info"></span>
                            Log file location: <code><?php echo esc_html( WP_CONTENT_DIR . '/debug.log' ); ?></code>
                        </div>
                    <?php else: ?>
                        <div class="notice notice-info inline">
                            <p><span class="dashicons dashicons-info-outline"></span> No debug.log file found. Enable WP_DEBUG_LOG and generate some errors to create it.</p>
                        </div>
                        <div class="wp-debug-log-placeholder">
                            <div class="wp-debug-log-empty">
                                <span class="dashicons dashicons-text-page"></span>
                                <p>Debug log will appear here once created</p>
                                <ol class="setup-steps">
                                    <li>Enable <strong>WP_DEBUG</strong> and <strong>WP_DEBUG_LOG</strong> using the toggles</li>
                                    <li>Save your settings</li>
                                    <li>Generate an error on your site</li>
                                </ol>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="wp-debug-help-section">
                <div class="wp-debug-help-header">
                    <span class="dashicons dashicons-editor-help"></span>
                    <h2>WordPress Debug Mode Quick Guide</h2>
                </div>
                <div class="wp-debug-help-content">
                    <div class="help-column">
                        <h3>What does each option do?</h3>
                        <ul>
                            <li><strong>WP_DEBUG</strong> - When enabled, WordPress will display all PHP errors, warnings, and notices.</li>
                            <li><strong>WP_DEBUG_LOG</strong> - When enabled, errors will be logged to wp-content/debug.log.</li>
                            <li><strong>SCRIPT_DEBUG</strong> - When enabled, WordPress will use development versions of CSS and JavaScript files (unminified).</li>
                        </ul>
                    </div>
                    <div class="help-column">
                        <h3>Using this plugin</h3>
                        <p>This plugin works automatically without any configuration:</p>
                        <ul>
                            <li><strong>Instant operation</strong> - Just activate and use it right away!</li>
                            <li><strong>Zero configuration</strong> - No need to edit wp-config.php</li>
                            <li><strong>Real-time updates</strong> - Settings take effect immediately</li>
                            <li><strong>Automatic attribution</strong> - Gives credit to fitzport.com by default</li>
                        </ul>
                        <p>Toggle the debug settings and click Save to apply your changes instantly!</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Check if debug.log file exists
     *
     * @since    1.0.0
     * @return   boolean   True if file exists, false otherwise
     */
    private function debug_log_exists() {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        return file_exists( $log_file );
    }

    /**
     * AJAX callback for log viewer
     *
     * @since    1.0.0
     */
    public function debug_log_viewer_callback() {
        // Check nonce
        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wp_debug_switcher_nonce' ) ) {
            wp_send_json_error( 'Invalid security token.' );
        }
        
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have sufficient permissions.' );
        }
        
        $log_file = WP_CONTENT_DIR . '/debug.log';
        $action = isset( $_REQUEST['log_action'] ) ? sanitize_text_field( $_REQUEST['log_action'] ) : 'view';
        
        switch ( $action ) {
            case 'view':
                // Check if file exists
                if ( ! file_exists( $log_file ) ) {
                    wp_send_json_error( 'Log file does not exist.' );
                }
                
                // Read the last 50 lines (or less if file is smaller)
                $lines = $this->read_last_lines( $log_file, 50 );
                
                if ( empty( $lines ) ) {
                    wp_send_json_success( 'Log file is empty.' );
                } else {
                    wp_send_json_success( implode( "\n", $lines ) );
                }
                break;
                
            case 'clear':
                // Check if file exists
                if ( ! file_exists( $log_file ) ) {
                    wp_send_json_error( 'Log file does not exist.' );
                }
                
                // Attempt to clear the file
                $result = file_put_contents( $log_file, '' );
                
                if ( false === $result ) {
                    wp_send_json_error( 'Could not clear log file. Check file permissions.' );
                } else {
                    wp_send_json_success( 'Log file cleared successfully.' );
                }
                break;
                
            default:
                wp_send_json_error( 'Invalid action.' );
                break;
        }
    }

    /**
     * Read the last n lines of a file
     *
     * @since    1.0.0
     * @param    string    $file    The file path
     * @param    int       $n       Number of lines to read
     * @return   array               Array of last n lines
     */
    private function read_last_lines( $file, $n ) {
        // Check if file exists
        if ( ! file_exists( $file ) ) {
            return array();
        }
        
        // Get file size
        $filesize = filesize( $file );
        
        if ( 0 === $filesize ) {
            return array();
        }
        
        // Safety limit - don't try to read huge files
        $max_size = 1024 * 1024; // 1MB
        
        if ( $filesize > $max_size ) {
            // If file is too large, only read the last 1MB
            $handle = fopen( $file, 'r' );
            fseek( $handle, -$max_size, SEEK_END );
            $lines = explode( "\n", fread( $handle, $max_size ) );
            fclose( $handle );
            
            // Remove first line which might be incomplete
            array_shift( $lines );
        } else {
            // File is small enough, read the whole thing
            $lines = file( $file );
        }
        
        // Get last n lines
        $lines = array_slice( $lines, -$n );
        
        // Remove newlines from each line
        return array_map( 'rtrim', $lines );
    }
}

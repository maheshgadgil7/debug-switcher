/**
 * Admin JavaScript for the WP Debug Switcher plugin
 *
 * @link       https://fitzport.com
 * @since      1.0.0
 *
 * @package    Wp_Debug_Switcher
 * @subpackage Wp_Debug_Switcher/admin/js
 */

(function( $ ) {
    'use strict';

    $(document).ready(function() {
        /**
         * Toggle switch animation and status indicator updates
         */
        $('.switch input[type="checkbox"]').on('change', function() {
            var statusIndicator = $(this).closest('.switch').siblings('.status-indicator');
            var descriptionEl = $(this).closest('td').find('.description');
            
            // Smooth animation for status change
            if ($(this).is(':checked')) {
                statusIndicator.fadeOut(100, function() {
                    $(this).removeClass('status-off').addClass('status-on').text('Enabled').fadeIn(100);
                });
                
                // Add highlight effect to the form row
                $(this).closest('tr').addClass('highlight-row');
                setTimeout(function() {
                    $('.highlight-row').removeClass('highlight-row');
                }, 1000);
            } else {
                statusIndicator.fadeOut(100, function() {
                    $(this).removeClass('status-on').addClass('status-off').text('Disabled').fadeIn(100);
                });
            }
            
            // Update WP_DEBUG_LOG dependency notice
            if ($(this).attr('name') === 'wp_debug_switcher_options[wp_debug]') {
                if (!$(this).is(':checked')) {
                    // If WP_DEBUG is turned off, show a note about WP_DEBUG_LOG dependency
                    var $wpDebugLogRow = $('input[name="wp_debug_switcher_options[wp_debug_log]"]').closest('tr');
                    $wpDebugLogRow.find('.description').html(
                        'Logs all PHP errors to wp-content/debug.log file. <span class="dependency-note">Note: WP_DEBUG must be enabled for this to work.</span>'
                    );
                } else {
                    // Reset the description
                    $('input[name="wp_debug_switcher_options[wp_debug_log]"]').closest('tr').find('.description').html(
                        'Logs all PHP errors to wp-content/debug.log file.'
                    );
                }
            }
        });

        /**
         * Code snippet copy functionality
         */
        // Add copy button to code snippet
        $('.wp-debug-code-snippet').append('<button class="copy-button">Copy Code</button>');
        
        // Copy code snippet when button is clicked
        $('.copy-button').on('click', function(e) {
            e.preventDefault();
            
            var codeText = $(this).siblings('pre').text();
            
            // Create temporary textarea to copy from
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(codeText).select();
            
            // Execute copy command
            var success = document.execCommand('copy');
            $temp.remove();
            
            // Show success message
            if (success) {
                $(this).text('Copied!').addClass('copied');
                
                // Reset button after 2 seconds
                var button = $(this);
                setTimeout(function() {
                    button.text('Copy Code').removeClass('copied');
                }, 2000);
            }
        });

        /**
         * Debug log viewer functionality
         */
        // Load debug log on page load with a slight delay to ensure smooth page load
        setTimeout(function() {
            loadDebugLog();
        }, 300);

        // Refresh log button with loading indication
        $('#refresh-log').on('click', function(e) {
            e.preventDefault();
            
            // Button loading state
            var $button = $(this);
            var originalText = $button.text();
            $button.text('Refreshing...').addClass('button-loading').prop('disabled', true);
            
            // Load log and restore button state
            loadDebugLog(function() {
                $button.text(originalText).removeClass('button-loading').prop('disabled', false);
            });
        });

        // Clear log button
        $('#clear-log').on('click', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog with improved styling
            if (!confirm('Are you sure you want to clear the debug log file? This action cannot be undone.')) {
                return;
            }
            
            // Button loading state
            var $button = $(this);
            var originalText = $button.text();
            $button.text('Clearing...').addClass('button-loading').prop('disabled', true);
            
            clearDebugLog(function() {
                $button.text(originalText).removeClass('button-loading').prop('disabled', false);
            });
        });

        /**
         * Auto-refresh toggle
         */
        // Add auto-refresh toggle
        $('.log-actions').append('<label class="auto-refresh-toggle"><input type="checkbox" id="auto-refresh"> Auto-refresh (10s)</label>');
        
        var refreshInterval;
        
        // Toggle auto-refresh
        $('#auto-refresh').on('change', function() {
            if ($(this).is(':checked')) {
                // Start auto-refresh
                refreshInterval = setInterval(function() {
                    // Only refresh if the tab is visible
                    if (!document.hidden) {
                        loadDebugLog(null, true); // true means silent refresh
                    }
                }, 10000); // 10 seconds
                
                // Show notification
                $('<div class="auto-refresh-notice">Auto-refresh enabled</div>').insertAfter('.log-actions').fadeIn().delay(2000).fadeOut(function() {
                    $(this).remove();
                });
            } else {
                // Stop auto-refresh
                clearInterval(refreshInterval);
            }
        });

        /**
         * Load debug log via AJAX
         * @param {Function} callback - Optional callback after loading
         * @param {Boolean} silent - If true, don't show loading indicator
         */
        function loadDebugLog(callback, silent) {
            var logContent = $('#debug-log-content');
            
            if (!silent) {
                logContent.html('<div class="loading">Loading log file...</div>');
            }
            
            $.ajax({
                url: wpDebugSwitcher.ajaxurl,
                type: 'POST',
                data: {
                    action: 'debug_log_viewer',
                    log_action: 'view',
                    nonce: wpDebugSwitcher.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // If this is a silent refresh and there's no change, don't update
                        if (silent && logContent.data('current-log') === response.data) {
                            if (callback && typeof callback === 'function') {
                                callback();
                            }
                            return;
                        }
                        
                        // Store current log for comparison in silent refreshes
                        logContent.data('current-log', response.data);
                        
                        // Process and colorize log content
                        var content = processLogContent(response.data);
                        
                        if (!silent) {
                            logContent.hide().html(content).fadeIn(300);
                        } else {
                            logContent.html(content);
                        }
                        
                        // Scroll to bottom
                        logContent.scrollTop(logContent[0].scrollHeight);
                    } else {
                        if (!silent) {
                            logContent.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                        }
                    }
                    
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                },
                error: function(xhr, status, error) {
                    if (!silent) {
                        logContent.html('<div class="notice notice-error"><p>Error loading log: ' + error + '</p></div>');
                    }
                    
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                }
            });
        }

        /**
         * Process and colorize log content
         */
        function processLogContent(content) {
            if (!content || content === '') {
                return '<p class="empty-log">Log file is empty.</p>';
            }
            
            // Escape HTML
            var escapedContent = content
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
            
            // Colorize different types of log entries
            escapedContent = escapedContent
                // Error - red
                .replace(/(\[.*?ERROR.*?\]|\bError\b|\bFatal error\b|\bWarning\b|\bException\b|\bParse error\b)/gi, '<span class="log-error">$1</span>')
                // Notice - yellow
                .replace(/(\[.*?NOTICE.*?\]|\bNotice\b|\bDeprecated\b)/gi, '<span class="log-notice">$1</span>')
                // Info - blue
                .replace(/(\[.*?INFO.*?\]|\bInfo\b|\bStack trace\b)/gi, '<span class="log-info">$1</span>')
                // Highlight file paths and line numbers
                .replace(/(\s|\()([\/\\][\w\d\.\/\\-_]+\.php)(\s|:|\))/g, '$1<span class="log-path">$2</span>$3')
                .replace(/(\bon line\s+)(\d+)/gi, '$1<span class="log-line">$2</span>');
            
            // Add line numbers
            var lines = escapedContent.split('\n');
            var numberedContent = '';
            for (var i = 0; i < lines.length; i++) {
                if (lines[i].trim() !== '') {
                    numberedContent += '<div class="log-line-wrap"><span class="line-number">' + (i + 1) + '</span>' + lines[i] + '</div>';
                } else {
                    numberedContent += '<div class="log-line-wrap empty-line"><span class="line-number">' + (i + 1) + '</span></div>';
                }
            }
            
            return numberedContent;
        }

        /**
         * Clear debug log via AJAX
         * @param {Function} callback - Optional callback after clearing
         */
        function clearDebugLog(callback) {
            var logContent = $('#debug-log-content');
            
            logContent.html('<div class="loading">Clearing log file...</div>');
            
            $.ajax({
                url: wpDebugSwitcher.ajaxurl,
                type: 'POST',
                data: {
                    action: 'debug_log_viewer',
                    log_action: 'clear',
                    nonce: wpDebugSwitcher.nonce
                },
                success: function(response) {
                    if (response.success) {
                        logContent.html('<div class="log-success">Log file cleared successfully. <span class="refresh-link">Refresh</span> to confirm.</div>');
                        
                        // Add click handler to refresh link
                        logContent.find('.refresh-link').on('click', function() {
                            loadDebugLog();
                        });
                    } else {
                        logContent.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                    }
                    
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                },
                error: function(xhr, status, error) {
                    logContent.html('<div class="notice notice-error"><p>Error clearing log: ' + error + '</p></div>');
                    
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                }
            });
        }

        /**
         * Add extra styles needed by JS features
         */
        $('<style>')
            .text(`
                /* Highlight effect for toggled rows */
                .highlight-row {
                    background-color: #f7fcfe;
                    transition: background-color 1s;
                }
                
                /* Copy button styles */
                .wp-debug-code-snippet {
                    position: relative;
                }
                
                .copy-button {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: #f0f0f1;
                    border: 1px solid #c3c4c7;
                    border-radius: 3px;
                    color: #3c434a;
                    font-size: 12px;
                    padding: 4px 8px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .copy-button:hover {
                    background: #e0e0e0;
                }
                
                .copy-button.copied {
                    background: #389e38;
                    color: #fff;
                    border-color: #389e38;
                }
                
                /* Auto-refresh toggle */
                .auto-refresh-toggle {
                    margin-left: auto;
                    font-size: 13px;
                    display: flex;
                    align-items: center;
                }
                
                .auto-refresh-toggle input {
                    margin-right: 5px;
                }
                
                .auto-refresh-notice {
                    background: #f0f6fc;
                    color: #72aee6;
                    border-left: 4px solid #72aee6;
                    padding: 6px 12px;
                    margin-top: 5px;
                    font-size: 13px;
                    display: none;
                }
                
                /* Enhanced log styling */
                .log-line-wrap {
                    position: relative;
                    padding-left: 50px;
                    min-height: 18px;
                    line-height: 18px;
                }
                
                .line-number {
                    position: absolute;
                    left: 0;
                    width: 40px;
                    text-align: right;
                    color: #606770;
                    background: #1e1e1e;
                    padding: 0 5px;
                    user-select: none;
                    font-size: 0.85em;
                    opacity: 0.6;
                }
                
                .empty-line {
                    height: 18px;
                }
                
                .log-error {
                    color: #ff6b6b;
                    font-weight: bold;
                }
                
                .log-notice {
                    color: #feca57;
                }
                
                .log-info {
                    color: #54a0ff;
                }
                
                .log-path {
                    color: #ff9f43;
                    text-decoration: underline;
                }
                
                .log-line {
                    color: #5352ed;
                    font-weight: bold;
                }
                
                .empty-log {
                    text-align: center;
                    color: #999;
                    font-style: italic;
                    padding: 20px;
                }
                
                .log-success {
                    text-align: center;
                    color: #46b450;
                    padding: 20px;
                }
                
                .refresh-link {
                    color: #0073aa;
                    text-decoration: underline;
                    cursor: pointer;
                }
                
                .dependency-note {
                    color: #856404;
                    background-color: #fff3cd;
                    border-radius: 3px;
                    padding: 2px 5px;
                    font-size: 0.9em;
                    margin-left: 5px;
                }
                
                /* Button loading state */
                .button-loading {
                    opacity: 0.8;
                    pointer-events: none;
                }
            `)
            .appendTo('head');
    });

})( jQuery );
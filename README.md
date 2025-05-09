# WP Debug Mode Switcher

A lightweight WordPress plugin that allows administrators to toggle debug modes (WP_DEBUG, WP_DEBUG_LOG, SCRIPT_DEBUG) directly from the WordPress admin dashboard without editing wp-config.php.

## Features

- **Zero Configuration**: Works instantly upon activation - no need to edit wp-config.php
- **Toggle Debugging Options**: Enable or disable WordPress debug modes with simple toggle switches
- **Status Indicators**: See at a glance which debug options are currently enabled
- **Debug Log Viewer**: View the latest debug logs directly in the WordPress dashboard
- **Clear Logs**: Clear the debug.log file with a single click
- **Auto-refresh**: Option to auto-refresh the log viewer every 10 seconds
- **Real-time Updates**: Changes take effect immediately
- **Neumorphism Design**: Beautiful, vibrant interface with intuitive controls
- **Responsive Design**: Works well on desktop and mobile devices
- **Admin-only Access**: Only users with manage_options capability can access the settings

## Installation

1. Upload the `wp-debug-switcher` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Tools > Debug Switcher
4. That's it! No wp-config.php editing required - the plugin works immediately

## Usage

1. Navigate to Tools > Debug Switcher in the WordPress admin menu
2. Toggle the switches for WP_DEBUG, WP_DEBUG_LOG, and SCRIPT_DEBUG
3. Click 'Save Settings' to apply changes
4. View debug logs in the Debug Log Viewer section
5. Use the 'Refresh Log' button to update the log view
6. Use the 'Clear Log' button to clear the debug.log file

## FAQ

### How does this plugin work?

The plugin stores your debug settings in the WordPress database and uses runtime hooks and PHP configuration techniques to apply these settings automatically. This allows you to change debug settings without manually editing files each time.

### Will this plugin work without modifying wp-config.php?

Yes! That's the main benefit of this plugin. It works immediately after activation without requiring any wp-config.php modifications. The plugin uses advanced techniques to control debug settings at runtime.

### Can I use this plugin on a production site?

Yes, but be careful with enabling debug modes on production sites:
- WP_DEBUG can expose sensitive information to visitors if errors occur
- WP_DEBUG_LOG is safer as it only logs errors to a file
- Consider using a staging environment for extensive debugging

### Where is the debug.log file located?

The debug.log file is located in your wp-content directory. The exact path is displayed in the Debug Log Viewer section.

## Screenshots

1. Main plugin interface with toggle switches and log viewer
2. Debug log viewing with syntax highlighting and error highlighting
3. Zero configuration notice showing no wp-config.php editing required

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```
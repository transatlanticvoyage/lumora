<?php
/**
 * Plugin Name: Lumora
 * Plugin URI: https://github.com/transatlanticvoyage/lumora
 * Description: Lumora - WordPress plugin for zen data management
 * Version: 1.0.0
 * Author: Lumora Team
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LUMORA_PLUGIN_VERSION', '1.0.0');
define('LUMORA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LUMORA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Initialize plugin
class Lumora {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Plugin initialization code will go here
    }
}

// Start the plugin
new Lumora();
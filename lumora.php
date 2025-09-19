<?php
// TEST COMMENT - Verifying dual git tracking for Lumora
// Test comment 9: Lumora dual visibility test - 2025-09-19 16:48
/**
 * Plugin Name: Lumora
 * Plugin URI: https://github.com/transatlanticvoyage/lumora
 * Description: Lumora - WordPress plugin for zen data fallback and shortcode management
 * Version: 1.1.0
 * Author: Lumora Team
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LUMORA_PLUGIN_VERSION', '1.1.0');
define('LUMORA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LUMORA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load plugin components
require_once LUMORA_PLUGIN_PATH . 'includes/class-lumora-database.php';
require_once LUMORA_PLUGIN_PATH . 'includes/class-lumora-zen-shortcodes.php';
require_once LUMORA_PLUGIN_PATH . 'includes/class-lumora-tax-exports.php';
require_once LUMORA_PLUGIN_PATH . 'includes/class-lumora-admin.php';

// Initialize plugin
class Lumora {
    
    private $database;
    private $shortcodes;
    private $admin;
    
    public function __construct() {
        // Initialize components
        $this->database = new Lumora_Database();
        $this->shortcodes = new Lumora_Zen_Shortcodes();
        
        // Only load admin in admin area
        if (is_admin()) {
            $this->admin = new Lumora_Admin();
        }
        
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Hook into WordPress
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create/update database tables
        $this->database->create_zen_tables();
    }
    
    /**
     * Plugin initialization
     */
    public function init() {
        // Additional initialization if needed
    }
}

// Start the plugin
new Lumora();
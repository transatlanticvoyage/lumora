<?php
// TEST COMMENT - Verifying dual git tracking for Lumora

/**
 * Lumora Database Class
 * Handles shared database schema creation and management
 * Compatible with Grove and Snefuruplin database structure
 */
class Lumora_Database {
    
    const ZEN_DB_VERSION = '1.3';
    
    public function __construct() {
        // Check and create tables on initialization
        add_action('init', array($this, 'maybe_create_tables'));
        
        // Hook into plugin activation
        register_activation_hook(LUMORA_PLUGIN_PATH . 'lumora.php', array($this, 'on_activation'));
    }
    
    /**
     * Plugin activation hook
     */
    public function on_activation() {
        $this->create_zen_tables();
        $this->maybe_insert_default_data();
    }
    
    /**
     * Check if tables need to be created or updated
     */
    public function maybe_create_tables() {
        $installed_version = get_option('lumora_zen_db_version', '0');
        
        if (version_compare($installed_version, self::ZEN_DB_VERSION, '<')) {
            $this->create_zen_tables();
            update_option('lumora_zen_db_version', self::ZEN_DB_VERSION);
        }
    }
    
    /**
     * Create all zen tables with shared schema
     * Uses CREATE TABLE IF NOT EXISTS for safe operation
     */
    public function create_zen_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Include WordPress upgrade functions
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create zen_sitespren table
        $this->create_sitespren_table($charset_collate);
        
        // Create zen_services table
        $this->create_services_table($charset_collate);
        
        // Create zen_locations table
        $this->create_locations_table($charset_collate);
    }
    
    /**
     * Create zen_sitespren table
     */
    private function create_sitespren_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            wppma_id int(11) NOT NULL AUTO_INCREMENT,
            wppma_db_only_created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            wppma_db_only_updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            id varchar(255) NOT NULL,
            created_at timestamp NULL,
            sitespren_base varchar(255) DEFAULT NULL,
            true_root_domain varchar(255) DEFAULT NULL,
            full_subdomain varchar(255) DEFAULT NULL,
            webproperty_type varchar(255) DEFAULT NULL,
            fk_users_id varchar(255) DEFAULT NULL,
            updated_at timestamp NULL,
            wpuser1 varchar(255) DEFAULT NULL,
            wppass1 varchar(255) DEFAULT NULL,
            wp_plugin_installed1 tinyint(1) DEFAULT NULL,
            wp_plugin_connected2 tinyint(1) DEFAULT NULL,
            fk_domreg_hostaccount varchar(255) DEFAULT NULL,
            is_wp_site tinyint(1) DEFAULT NULL,
            wp_rest_app_pass varchar(255) DEFAULT NULL,
            driggs_industry varchar(255) DEFAULT NULL,
            driggs_city varchar(255) DEFAULT NULL,
            driggs_brand_name varchar(255) DEFAULT NULL,
            driggs_site_type_purpose varchar(255) DEFAULT NULL,
            driggs_email_1 varchar(255) DEFAULT NULL,
            driggs_address_full text DEFAULT NULL,
            driggs_address_species_id int(11) DEFAULT NULL,
            driggs_phone_1 varchar(255) DEFAULT NULL,
            driggs_phone1_platform_id int(11) DEFAULT NULL,
            driggs_cgig_id int(11) DEFAULT NULL,
            driggs_citations_done tinyint(1) DEFAULT NULL,
            driggs_social_profiles_done tinyint(1) DEFAULT NULL,
            driggs_special_note_for_ai_tool text DEFAULT NULL,
            driggs_hours text DEFAULT NULL,
            driggs_owner_name varchar(255) DEFAULT NULL,
            driggs_short_descr text DEFAULT NULL,
            driggs_long_descr text DEFAULT NULL,
            driggs_year_opened int(11) DEFAULT NULL,
            driggs_employees_qty int(11) DEFAULT NULL,
            driggs_keywords text DEFAULT NULL,
            driggs_category varchar(255) DEFAULT NULL,
            driggs_address_species_note text DEFAULT NULL,
            driggs_payment_methods text DEFAULT NULL,
            driggs_social_media_links text DEFAULT NULL,
            driggs_street_1 varchar(255) DEFAULT NULL,
            driggs_street_2 varchar(255) DEFAULT NULL,
            driggs_state_code varchar(10) DEFAULT NULL,
            driggs_zip varchar(20) DEFAULT NULL,
            driggs_state_full varchar(255) DEFAULT NULL,
            driggs_country varchar(255) DEFAULT NULL,
            driggs_revenue_goal decimal(15,2) DEFAULT NULL,
            ns_full varchar(255) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            is_starred1 varchar(10) DEFAULT NULL,
            icon_name varchar(255) DEFAULT NULL,
            icon_color varchar(50) DEFAULT NULL,
            is_bulldozer tinyint(1) DEFAULT 0,
            is_competitor tinyint(1) DEFAULT 0,
            is_external tinyint(1) DEFAULT 0,
            is_internal tinyint(1) DEFAULT 0,
            is_ppx tinyint(1) DEFAULT 0,
            is_ms tinyint(1) DEFAULT 0,
            is_wayback_rebuild tinyint(1) DEFAULT 0,
            is_naked_wp_build tinyint(1) DEFAULT 0,
            is_rnr tinyint(1) DEFAULT 0,
            is_aff tinyint(1) DEFAULT 0,
            is_other1 tinyint(1) DEFAULT 0,
            is_other2 tinyint(1) DEFAULT 0,
            snailimage varchar(255) DEFAULT NULL,
            snail_image_url text DEFAULT NULL,
            snail_image_status varchar(50) DEFAULT NULL,
            snail_image_error text DEFAULT NULL,
            screenshot_url text DEFAULT NULL,
            screenshot_taken_at timestamp NULL,
            screenshot_status varchar(50) DEFAULT NULL,
            rel_cncglub_id int(11) DEFAULT NULL,
            rel_city_id int(11) DEFAULT NULL,
            driggs_logo_url text DEFAULT NULL,
            driggs_social_profiles_done tinyint(1) DEFAULT 0,
            driggs_hours text DEFAULT NULL,
            driggs_owner_name varchar(255) DEFAULT NULL,
            driggs_short_descr text DEFAULT NULL,
            driggs_long_descr text DEFAULT NULL,
            driggs_year_opened int(11) DEFAULT NULL,
            driggs_employees_qty int(11) DEFAULT NULL,
            rel_industry_id int(11) DEFAULT NULL,
            PRIMARY KEY (wppma_id),
            UNIQUE KEY unique_id (id),
            INDEX idx_sitespren_base (sitespren_base),
            INDEX idx_fk_users_id (fk_users_id),
            INDEX idx_is_external (is_external),
            INDEX idx_is_internal (is_internal)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create zen_services table
     */
    private function create_services_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_services';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            service_id int(11) NOT NULL AUTO_INCREMENT,
            service_name varchar(255) NOT NULL,
            service_placard varchar(255) DEFAULT NULL,
            service_moniker varchar(255) DEFAULT NULL,
            description1_short text DEFAULT NULL,
            description1_long text DEFAULT NULL,
            service_image_url text DEFAULT NULL,
            service_image_id int(11) DEFAULT NULL,
            is_pinned_service tinyint(1) DEFAULT 0,
            position_in_custom_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (service_id),
            INDEX idx_service_name (service_name),
            INDEX idx_is_pinned (is_pinned_service),
            INDEX idx_position (position_in_custom_order)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Create zen_locations table
     */
    private function create_locations_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_locations';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            location_id int(11) NOT NULL AUTO_INCREMENT,
            location_name varchar(255) NOT NULL,
            location_placard varchar(255) DEFAULT NULL,
            location_moniker varchar(255) DEFAULT NULL,
            location_sobriquet varchar(255) DEFAULT NULL,
            street varchar(255) DEFAULT NULL,
            city varchar(255) DEFAULT NULL,
            state_code varchar(10) DEFAULT NULL,
            zip_code varchar(20) DEFAULT NULL,
            country varchar(255) DEFAULT 'USA',
            location_image_url text DEFAULT NULL,
            location_image_id int(11) DEFAULT NULL,
            is_pinned_location tinyint(1) DEFAULT 0,
            position_in_custom_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (location_id),
            INDEX idx_location_name (location_name),
            INDEX idx_is_pinned (is_pinned_location),
            INDEX idx_position (position_in_custom_order),
            INDEX idx_city_state (city, state_code)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Insert default data if tables are empty
     */
    private function maybe_insert_default_data() {
        global $wpdb;
        
        // Create default sitespren record for current site
        $sitespren_table = $wpdb->prefix . 'zen_sitespren';
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $sitespren_table");
        
        if ($existing_count == 0) {
            $site_url = get_site_url();
            $parsed_url = parse_url($site_url);
            $domain = isset($parsed_url['host']) ? $parsed_url['host'] : 'localhost';
            
            $wpdb->insert(
                $sitespren_table,
                array(
                    'id' => wp_generate_uuid4(),
                    'sitespren_base' => $domain,
                    'driggs_brand_name' => get_option('blogname', 'My Site'),
                    'driggs_site_type_purpose' => 'WordPress Site',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get services data (compatible with Grove format)
     */
    public static function get_services($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC',
            'pinned_first' => true,
            'limit' => -1
        );
        
        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'zen_services';
        
        $sql = "SELECT * FROM $table_name";
        
        // Add ORDER BY clause
        if ($args['pinned_first']) {
            $sql .= " ORDER BY is_pinned_service DESC, " . esc_sql($args['orderby']) . " " . esc_sql($args['order']);
        } else {
            $sql .= " ORDER BY " . esc_sql($args['orderby']) . " " . esc_sql($args['order']);
        }
        
        // Add LIMIT if specified
        if ($args['limit'] > 0) {
            $sql .= " LIMIT " . intval($args['limit']);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get locations data (compatible with Grove format)
     */
    public static function get_locations($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC',
            'pinned_first' => true,
            'limit' => -1
        );
        
        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'zen_locations';
        
        $sql = "SELECT * FROM $table_name";
        
        // Add ORDER BY clause
        if ($args['pinned_first']) {
            $sql .= " ORDER BY is_pinned_location DESC, " . esc_sql($args['orderby']) . " " . esc_sql($args['order']);
        } else {
            $sql .= " ORDER BY " . esc_sql($args['orderby']) . " " . esc_sql($args['order']);
        }
        
        // Add LIMIT if specified
        if ($args['limit'] > 0) {
            $sql .= " LIMIT " . intval($args['limit']);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get single service by ID
     */
    public static function get_service($service_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_services';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE service_id = %d",
            $service_id
        ));
    }
    
    /**
     * Get single location by ID
     */
    public static function get_location($location_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_locations';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE location_id = %d",
            $location_id
        ));
    }
    
    /**
     * Get sitespren data
     */
    public static function get_sitespren($wppma_id = 1) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zen_sitespren';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE wppma_id = %d",
            $wppma_id
        ));
    }
}
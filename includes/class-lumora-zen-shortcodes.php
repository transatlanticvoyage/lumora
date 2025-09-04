<?php
// 

/**
 * Lumora Zen Shortcodes Class
 * Handles WordPress shortcodes for zen_services and zen_locations data
 */
class Lumora_Zen_Shortcodes {
    
    public function __construct() {
        // Don't auto-register shortcodes - let the commander control it
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('init', array($this, 'maybe_register_shortcodes'), 15); // Later priority
    }
    
    /**
     * Maybe register shortcodes based on Lumora settings
     */
    public function maybe_register_shortcodes() {
        $mode = get_option('lumora_shortcode_mode', 'automatic');
        
        switch ($mode) {
            case 'automatic':
                // Default: Only if Snefuruplin, Grove AND Beacon are ALL not active
                if (!is_plugin_active('snefuruplin/snefuruplin.php') && 
                    !is_plugin_active('grove/grove.php') &&
                    !is_plugin_active('beacon/beacon.php')) {
                    $this->register_shortcodes();
                }
                break;
                
            case 'force_active':
                // Always register (override others)
                $this->register_shortcodes();
                break;
                
            case 'disabled':
                // Never register
                break;
        }
    }
    
    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        // Services shortcodes
        add_shortcode('zen_services', array($this, 'render_services'));
        add_shortcode('zen_service', array($this, 'render_single_service'));
        add_shortcode('zen_service_image', array($this, 'render_service_image'));
        
        // Locations shortcodes
        add_shortcode('zen_locations', array($this, 'render_locations'));
        add_shortcode('zen_location', array($this, 'render_single_location'));
        add_shortcode('zen_location_image', array($this, 'render_location_image'));
        
        // Utility shortcodes
        add_shortcode('zen_pinned_services', array($this, 'render_pinned_services'));
        add_shortcode('zen_pinned_locations', array($this, 'render_pinned_locations'));
        
        // Sitespren shortcode
        add_shortcode('sitespren', array($this, 'render_sitespren'));
    }
    
    /**
     * Enqueue shortcodes CSS
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'lumora-zen-shortcodes',
            LUMORA_PLUGIN_URL . 'assets/zen-shortcodes.css',
            array(),
            LUMORA_PLUGIN_VERSION
        );
    }
    
    /**
     * Render services list or single service
     * Usage: [zen_services limit="5" template="grid"] or [zen_services service_id="1" dbcol="service_name"]
     */
    public function render_services($atts) {
        $atts = shortcode_atts(array(
            'limit' => -1,
            'template' => 'list',
            'service_id' => null,
            'dbcol' => null,
            'pinned_first' => true,
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC'
        ), $atts);
        
        // Handle single service display
        if (!empty($atts['service_id'])) {
            return $this->render_single_service($atts);
        }
        
        $services = Lumora_Database::get_services(array(
            'limit' => intval($atts['limit']),
            'pinned_first' => $atts['pinned_first'] === 'true',
            'orderby' => $atts['orderby'],
            'order' => strtoupper($atts['order'])
        ));
        
        if (empty($services)) {
            return '<p class="zen-services-not-found">No services found.</p>';
        }
        
        $output = '<div class="zen-services-wrapper zen-template-' . esc_attr($atts['template']) . '">';
        
        foreach ($services as $service) {
            $output .= $this->format_service_item($service, $atts['template']);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render single service
     */
    public function render_single_service($atts) {
        $atts = shortcode_atts(array(
            'service_id' => null,
            'dbcol' => null,
            'template' => 'simple'
        ), $atts);
        
        if (empty($atts['service_id'])) {
            return '<span class="zen-services-error">Service ID is required</span>';
        }
        
        $service = Lumora_Database::get_service(intval($atts['service_id']));
        
        if (!$service) {
            return '<span class="zen-services-not-found">Service not found</span>';
        }
        
        // Return specific column if requested
        if (!empty($atts['dbcol'])) {
            $column = $atts['dbcol'];
            if (isset($service->$column)) {
                return esc_html($service->$column);
            } else {
                return '<span class="zen-services-no-field">Field "' . esc_html($column) . '" not found</span>';
            }
        }
        
        return $this->format_service_item($service, $atts['template']);
    }
    
    /**
     * Format service item for display
     */
    private function format_service_item($service, $template) {
        $output = '<div class="zen-service-item">';
        
        if ($template === 'cards') {
            $output .= '<div class="zen-service-card">';
        }
        
        // Pinned badge
        if ($service->is_pinned_service) {
            $output .= '<span class="zen-pinned-badge">Pinned</span>';
        }
        
        // Service image
        if (!empty($service->service_image_url)) {
            $output .= '<div class="zen-service-image-wrapper">';
            $output .= '<img src="' . esc_url($service->service_image_url) . '" alt="' . esc_attr($service->service_name) . '" class="zen-service-image">';
            $output .= '</div>';
        }
        
        // Service name
        if (!empty($service->service_name)) {
            $output .= '<h3 class="zen-service-name">' . esc_html($service->service_name) . '</h3>';
        }
        
        // Service placard
        if (!empty($service->service_placard)) {
            $output .= '<p class="zen-service-placard">' . esc_html($service->service_placard) . '</p>';
        }
        
        // Service moniker
        if (!empty($service->service_moniker)) {
            $output .= '<p class="zen-service-moniker">' . esc_html($service->service_moniker) . '</p>';
        }
        
        // Short description
        if (!empty($service->description1_short)) {
            $output .= '<p class="zen-service-description-short">' . esc_html($service->description1_short) . '</p>';
        }
        
        // Long description (for detailed templates)
        if ($template === 'detailed' && !empty($service->description1_long)) {
            $output .= '<div class="zen-service-description-long">' . wp_kses_post($service->description1_long) . '</div>';
        }
        
        if ($template === 'cards') {
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render service image
     */
    public function render_service_image($atts) {
        $atts = shortcode_atts(array(
            'service_id' => null,
            'size' => 'medium',
            'alt' => ''
        ), $atts);
        
        if (empty($atts['service_id'])) {
            return '<span class="zen-services-error">Service ID is required</span>';
        }
        
        $service = Lumora_Database::get_service(intval($atts['service_id']));
        
        if (!$service || empty($service->service_image_url)) {
            return '<span class="zen-services-not-found">Service image not found</span>';
        }
        
        $alt = !empty($atts['alt']) ? $atts['alt'] : $service->service_name;
        
        return '<img src="' . esc_url($service->service_image_url) . '" alt="' . esc_attr($alt) . '" class="zen-service-image zen-service-image-' . esc_attr($atts['size']) . '">';
    }
    
    /**
     * Render locations list or single location
     */
    public function render_locations($atts) {
        $atts = shortcode_atts(array(
            'limit' => -1,
            'template' => 'list',
            'location_id' => null,
            'dbcol' => null,
            'pinned_first' => true,
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC'
        ), $atts);
        
        // Handle single location display
        if (!empty($atts['location_id'])) {
            return $this->render_single_location($atts);
        }
        
        $locations = Lumora_Database::get_locations(array(
            'limit' => intval($atts['limit']),
            'pinned_first' => $atts['pinned_first'] === 'true',
            'orderby' => $atts['orderby'],
            'order' => strtoupper($atts['order'])
        ));
        
        if (empty($locations)) {
            return '<p class="zen-locations-not-found">No locations found.</p>';
        }
        
        $output = '<div class="zen-locations-wrapper zen-template-' . esc_attr($atts['template']) . '">';
        
        foreach ($locations as $location) {
            $output .= $this->format_location_item($location, $atts['template']);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render single location
     */
    public function render_single_location($atts) {
        $atts = shortcode_atts(array(
            'location_id' => null,
            'dbcol' => null,
            'template' => 'simple'
        ), $atts);
        
        if (empty($atts['location_id'])) {
            return '<span class="zen-locations-error">Location ID is required</span>';
        }
        
        $location = Lumora_Database::get_location(intval($atts['location_id']));
        
        if (!$location) {
            return '<span class="zen-locations-not-found">Location not found</span>';
        }
        
        // Return specific column if requested
        if (!empty($atts['dbcol'])) {
            $column = $atts['dbcol'];
            if (isset($location->$column)) {
                return esc_html($location->$column);
            } else {
                return '<span class="zen-locations-no-field">Field "' . esc_html($column) . '" not found</span>';
            }
        }
        
        return $this->format_location_item($location, $atts['template']);
    }
    
    /**
     * Format location item for display
     */
    private function format_location_item($location, $template) {
        $output = '<div class="zen-location-item">';
        
        if ($template === 'cards') {
            $output .= '<div class="zen-location-card">';
        }
        
        // Pinned badge
        if ($location->is_pinned_location) {
            $output .= '<span class="zen-pinned-badge">Pinned</span>';
        }
        
        // Location image
        if (!empty($location->location_image_url)) {
            $output .= '<div class="zen-location-image-wrapper">';
            $output .= '<img src="' . esc_url($location->location_image_url) . '" alt="' . esc_attr($location->location_name) . '" class="zen-location-image">';
            $output .= '</div>';
        }
        
        // Location name
        if (!empty($location->location_name)) {
            $output .= '<h3 class="zen-location-name">' . esc_html($location->location_name) . '</h3>';
        }
        
        // Location placard
        if (!empty($location->location_placard)) {
            $output .= '<p class="zen-location-placard">' . esc_html($location->location_placard) . '</p>';
        }
        
        // Location moniker
        if (!empty($location->location_moniker)) {
            $output .= '<p class="zen-location-moniker">' . esc_html($location->location_moniker) . '</p>';
        }
        
        // Address
        $address_parts = array();
        if (!empty($location->street)) $address_parts[] = $location->street;
        if (!empty($location->city)) $address_parts[] = $location->city;
        if (!empty($location->state_code)) $address_parts[] = $location->state_code;
        if (!empty($location->zip_code)) $address_parts[] = $location->zip_code;
        
        if (!empty($address_parts)) {
            $output .= '<p class="zen-location-address">' . esc_html(implode(', ', $address_parts)) . '</p>';
        }
        
        if ($template === 'cards') {
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render location image
     */
    public function render_location_image($atts) {
        $atts = shortcode_atts(array(
            'location_id' => null,
            'size' => 'medium',
            'alt' => ''
        ), $atts);
        
        if (empty($atts['location_id'])) {
            return '<span class="zen-locations-error">Location ID is required</span>';
        }
        
        $location = Lumora_Database::get_location(intval($atts['location_id']));
        
        if (!$location || empty($location->location_image_url)) {
            return '<span class="zen-locations-not-found">Location image not found</span>';
        }
        
        $alt = !empty($atts['alt']) ? $atts['alt'] : $location->location_name;
        
        return '<img src="' . esc_url($location->location_image_url) . '" alt="' . esc_attr($alt) . '" class="zen-location-image zen-location-image-' . esc_attr($atts['size']) . '">';
    }
    
    /**
     * Render pinned services only
     */
    public function render_pinned_services($atts) {
        $atts = shortcode_atts(array(
            'template' => 'list',
            'limit' => -1
        ), $atts);
        
        global $wpdb;
        $table = $wpdb->prefix . 'zen_services';
        
        $sql = "SELECT * FROM $table WHERE is_pinned_service = 1 ORDER BY position_in_custom_order ASC";
        
        if ($atts['limit'] > 0) {
            $sql .= " LIMIT " . intval($atts['limit']);
        }
        
        $services = $wpdb->get_results($sql);
        
        if (empty($services)) {
            return '<p class="zen-services-not-found">No pinned services found.</p>';
        }
        
        $output = '<div class="zen-services-wrapper zen-template-' . esc_attr($atts['template']) . '">';
        
        foreach ($services as $service) {
            $output .= $this->format_service_item($service, $atts['template']);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render pinned locations only
     */
    public function render_pinned_locations($atts) {
        $atts = shortcode_atts(array(
            'template' => 'list',
            'limit' => -1
        ), $atts);
        
        global $wpdb;
        $table = $wpdb->prefix . 'zen_locations';
        
        $sql = "SELECT * FROM $table WHERE is_pinned_location = 1 ORDER BY position_in_custom_order ASC";
        
        if ($atts['limit'] > 0) {
            $sql .= " LIMIT " . intval($atts['limit']);
        }
        
        $locations = $wpdb->get_results($sql);
        
        if (empty($locations)) {
            return '<p class="zen-locations-not-found">No pinned locations found.</p>';
        }
        
        $output = '<div class="zen-locations-wrapper zen-template-' . esc_attr($atts['template']) . '">';
        
        foreach ($locations as $location) {
            $output .= $this->format_location_item($location, $atts['template']);
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render sitespren data
     * Usage: [sitespren dbcol="driggs_brand_name"]
     */
    public function render_sitespren($atts) {
        $atts = shortcode_atts(array(
            'dbcol' => null,
            'wppma_id' => 1
        ), $atts);
        
        if (empty($atts['dbcol'])) {
            return '<span class="sitespren-error">dbcol parameter is required</span>';
        }
        
        $sitespren = Lumora_Database::get_sitespren(intval($atts['wppma_id']));
        
        if (!$sitespren) {
            return '<span class="sitespren-not-found">Sitespren data not found</span>';
        }
        
        $column = $atts['dbcol'];
        if (isset($sitespren->$column)) {
            return esc_html($sitespren->$column);
        } else {
            return '<span class="sitespren-no-field">Field "' . esc_html($column) . '" not found</span>';
        }
    }
}
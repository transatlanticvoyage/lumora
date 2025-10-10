<?php
// TEST COMMENT - Verifying dual git tracking for Lumora
/**
 * Lumora Admin Class
 */

class Lumora_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_lumora_driggs_get_data', array($this, 'lumora_driggs_get_data'));
        add_action('wp_ajax_lumora_driggs_update_field', array($this, 'lumora_driggs_update_field'));
        add_action('wp_ajax_lumora_driggs_export', array($this, 'lumora_driggs_export'));
        add_action('wp_ajax_lumora_driggs_save_all', array($this, 'lumora_driggs_save_all'));
        add_action('wp_ajax_lumora_driggs_reset', array($this, 'lumora_driggs_reset'));
        add_action('wp_ajax_lumora_update_site_settings', array($this, 'lumora_update_site_settings'));
        
        // BeamRay AJAX handlers
        add_action('wp_ajax_lumora_beamray_create_new_post', array($this, 'lumora_beamray_create_new_post'));
        add_action('wp_ajax_lumora_beamray_update_post_field', array($this, 'lumora_beamray_update_post_field'));
        add_action('wp_ajax_lumora_beamray_update_meta_field', array($this, 'lumora_beamray_update_meta_field'));
        
        // Media Library Column hooks
        add_filter('manage_media_columns', array($this, 'add_lumora_media_column'));
        add_action('manage_media_custom_column', array($this, 'lumora_media_column_content'), 10, 2);
        add_action('admin_head-upload.php', array($this, 'lumora_media_column_styles'));
        add_action('admin_footer-upload.php', array($this, 'lumora_media_rename_popup_script'));
        
        // Media Rename AJAX handlers
        add_action('wp_ajax_lumora_get_attachment_details', array($this, 'lumora_get_attachment_details'));
        add_action('wp_ajax_lumora_rename_media_file', array($this, 'lumora_rename_media_file'));
        
        // Test handler for debugging
        add_action('wp_ajax_lumora_test_rename', array($this, 'lumora_test_rename'));
        
        // Handle file redirects
        add_action('init', array($this, 'lumora_handle_file_redirects'));
    }
    
    /**
     * AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
     */
    private function suppress_all_admin_notices() {
        // Remove all admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('user_admin_notices');
        
        // Also remove any notices that might be added later
        add_action('admin_notices', array($this, 'remove_notices'), 1);
        add_action('all_admin_notices', array($this, 'remove_notices'), 1);
    }
    
    public function remove_notices() {
        // This function intentionally left blank to suppress notices
    }
    
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            'Lumora Hub',
            'Lumora Hub', 
            'manage_options',
            'lumorahub',
            array($this, 'lumorahub_page'),
            'dashicons-lightbulb',
            3.5
        );
        
        // Child menu pages (mirroring Grove structure)
        add_submenu_page(
            'lumorahub',
            'Lumora Driggs Mar',
            'Lumora Driggs Mar',
            'manage_options',
            'lumora_driggs_mar',
            array($this, 'lumora_driggs_mar')
        );
        
        add_submenu_page(
            'lumorahub',
            'Lumora Locations Mar',
            'Lumora Locations Mar',
            'manage_options',
            'lumora_locations_mar',
            array($this, 'lumora_locations_mar')
        );
        
        add_submenu_page(
            'lumorahub',
            'Lumora Services Mar', 
            'Lumora Services Mar',
            'manage_options',
            'lumora_services_mar',
            array($this, 'lumora_services_mar')
        );
        
        add_submenu_page(
            'lumorahub',
            'Lumora BeamRay Table',
            'Lumora BeamRay Table',
            'manage_options',
            'lum_beamray_mar',
            array($this, 'lum_beamray_mar_page')
        );
    }
    
    /**
     * Main hub page (redirects to driggs_mar)
     */
    public function lumorahub_page() {
        // Redirect to the main driggs management page
        wp_redirect(admin_url('admin.php?page=lumora_driggs_mar'));
        exit;
    }
    
    /**
     * Lumora Driggs Mar page - Main site management interface
     */
    public function lumora_driggs_mar() {
        // AGGRESSIVE NOTICE SUPPRESSION - Remove ALL WordPress admin notices
        $this->suppress_all_admin_notices();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        $current_site_url = get_site_url();
        
        // Ensure zen_sitespren table has a record for current site
        $this->ensure_sitespren_record();
        
        ?>
        <div class="wrap" style="margin: 0; padding: 0;">
            <!-- Allow space for WordPress notices -->
            <div style="height: 20px;"></div>
            
            <div style="padding: 20px;">
                <h1 style="margin-bottom: 20px;">✨ Lumora Hub - Driggs Site Manager</h1>
                
                <!-- WordPress Native Settings - Horizontal Bar -->
                <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 12px 20px; margin-bottom: 20px; border-radius: 5px;">
                    <form id="lumora-site-settings-form">
                        <div style="display: flex; align-items: center; gap: 25px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label for="lumora-site-title" style="font-weight: 600; color: #333;">Site Title:</label>
                                <input type="text" id="lumora-site-title" name="blogname" value="<?php echo esc_attr(get_option('blogname')); ?>" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; width: 200px;">
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label for="lumora-site-tagline" style="font-weight: 600; color: #333;">Tagline:</label>
                                <input type="text" id="lumora-site-tagline" name="blogdescription" value="<?php echo esc_attr(get_option('blogdescription')); ?>" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; width: 250px;">
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label for="lumora-site-icon" style="font-weight: 600; color: #333;">Site Icon:</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <?php 
                                    $site_icon = get_option('site_icon');
                                    if ($site_icon) {
                                        $icon_url = wp_get_attachment_image_src($site_icon, array(24, 24));
                                        if ($icon_url) {
                                            echo '<img src="' . esc_url($icon_url[0]) . '" alt="Site Icon" style="width: 24px; height: 24px; border-radius: 3px;">';
                                        }
                                    }
                                    ?>
                                    <button type="button" id="lumora-choose-site-icon" class="button button-small">Choose Icon</button>
                                    <input type="hidden" id="lumora-site-icon-id" name="site_icon" value="<?php echo esc_attr($site_icon); ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="button button-primary button-small">Update Settings</button>
                        </div>
                    </form>
                </div>
                
                <!-- Control Bar with Export Buttons -->
                <div style="background: white; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <strong>Current Site:</strong> <?php echo esc_html($current_site_url); ?>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <!-- Export Buttons -->
                            <div style="display: flex; gap: 5px;">
                                <button id="lumora-export-sharkintax" class="button button-secondary button-small" data-format="sharkintax" title="Export as Sharkintax format">sharkintax</button>
                                <button id="lumora-export-walrustax" class="button button-secondary button-small" data-format="walrustax" title="Export as Walrustax format">walrustax</button>
                                <button id="lumora-export-xls" class="button button-secondary button-small" data-format="xls" title="Export as Excel format">xls</button>
                                <button id="lumora-export-csv" class="button button-secondary button-small" data-format="csv" title="Export as CSV format">csv</button>
                                <button id="lumora-export-sql" class="button button-secondary button-small" data-format="sql" title="Export as SQL format">sql</button>
                            </div>
                            <div style="border-left: 1px solid #ddd; padding-left: 10px;">
                                <button id="lumora-save-all-btn" class="button button-primary">Save All Changes</button>
                                <button id="lumora-reset-btn" class="button button-secondary">Reset to Defaults</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vertical Field Table -->
                <div style="background: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <div style="overflow-x: auto;">
                        <table id="lumora-driggs-table" style="width: auto; border-collapse: collapse; font-size: 14px;">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: left; background: #f0f0f0; width: 50px;">
                                        <input type="checkbox" id="lumora-select-all" style="width: 20px; height: 20px;">
                                    </th>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">Field Name</th>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">Value</th>
                                    <th style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa;">shortcode 1</th>
                                    <th style="padding: 0; border: 1px solid #ddd; font-weight: bold; text-transform: lowercase; background: #f8f9fa; width: 20px;">stuff3</th>
                                </tr>
                            </thead>
                            <tbody id="lumora-table-body">
                                <!-- Data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
                    <p><strong>Instructions:</strong></p>
                    <ul>
                        <li>Click on any value to edit it inline</li>
                        <li>Toggle switches control boolean fields</li>
                        <li>Changes are saved automatically when you click out of a field</li>
                        <li>Use export buttons to download data in various formats</li>
                        <li>Use "Save All Changes" to force save all modifications</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <style type="text/css">
        /* Toggle Switch Styles */
        .lumora-toggle-switch {
            cursor: pointer !important;
        }
        
        .lumora-toggle-switch input[type="checkbox"]:focus + .lumora-toggle-slider {
            box-shadow: 0 0 1px #2196F3;
        }
        
        .lumora-toggle-slider {
            pointer-events: none;
        }
        
        .lumora-toggle-knob {
            pointer-events: none;
        }
        
        /* Ensure table cells are properly styled */
        #lumora-driggs-table td {
            vertical-align: middle;
        }
        
        #lumora-driggs-table td[data-field] {
            min-height: 30px;
        }
        
        #lumora-driggs-table textarea,
        #lumora-driggs-table input[type="text"],
        #lumora-driggs-table input[type="email"],
        #lumora-driggs-table input[type="number"] {
            font-family: inherit;
            font-size: 14px;
        }
        
        /* Roaring Div Styles */
        .lumora_roaring_div {
            width: 20px;
            height: 100%;
            border: 1px solid gray;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            background-color: #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .lumora_roaring_div:hover {
            background-color: #e0e0e0;
            border-color: #666;
        }
        
        /* Ensure stuff3 column cells have no padding */
        #lumora-driggs-table td.lumora-stuff3-cell {
            padding: 0 !important;
            width: 20px !important;
        }
        
        /* Read-only field styling */
        .lumora-driggs-readonly-field {
            color: #666 !important;
            font-style: italic !important;
            cursor: default !important;
            user-select: text !important;
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            pointer-events: auto !important;
        }
        </style>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            let currentData = {};
            let hasChanges = false;
            
            // Load initial data
            loadLumoraDriggsData();
            
            function loadLumoraDriggsData() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lumora_driggs_get_data',
                        nonce: '<?php echo wp_create_nonce('lumora_driggs_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            currentData = response.data;
                            displayData();
                        } else {
                            alert('Error loading driggs data: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error loading driggs data');
                    }
                });
            }
            
            function displayData() {
                let tbody = $('#lumora-table-body');
                tbody.empty();
                
                // Field definitions matching /drom app order exactly - preserve top 5 system fields
                const fields = [
                    // System fields (keep at top with gray background)
                    {key: 'wppma_id', label: 'wppma_id', type: 'number'},
                    {key: 'wppma_db_only_created_at', label: 'wppma_db_only_created_at', type: 'datetime'},
                    {key: 'wppma_db_only_updated_at', label: 'wppma_db_only_updated_at', type: 'datetime'},
                    {key: 'id', label: 'id', type: 'text'},
                    {key: 'created_at', label: 'created_at', type: 'datetime'},
                    
                    // Core site information (matching /drom order)
                    {key: 'sitespren_base', label: 'sitespren_base', type: 'text'},
                    {key: 'driggs_brand_name', label: 'driggs_brand_name', type: 'text'},
                    {key: 'driggs_revenue_goal', label: 'driggs_revenue_goal', type: 'number'},
                    
                    // phone section
                    {key: 'phone_section_separator', label: 'phone section', type: 'separator'},
                    {key: 'driggs_phone1_platform_id', label: 'driggs_phone1_platform_id', type: 'number'},
                    {key: 'driggs_phone_1', label: 'driggs_phone_1', type: 'text'},
                    
                    // address section
                    {key: 'address_section_separator', label: 'address section', type: 'separator'},
                    {key: 'driggs_address_species_id', label: 'driggs_address_species_id', type: 'number'},
                    {key: 'driggs_address_species_note', label: 'driggs_address_species_note', type: 'textarea'},
                    {key: 'driggs_address_full', label: 'driggs_address_full', type: 'textarea'},
                    {key: 'driggs_street_1', label: 'driggs_street_1', type: 'text'},
                    {key: 'driggs_street_2', label: 'driggs_street_2', type: 'text'},
                    {key: 'driggs_city', label: 'driggs_city', type: 'text'},
                    {key: 'driggs_state_code', label: 'driggs_state_code', type: 'text'},
                    {key: 'driggs_zip', label: 'driggs_zip', type: 'text'},
                    {key: 'driggs_state_full', label: 'driggs_state_full', type: 'text'},
                    {key: 'driggs_country', label: 'driggs_country', type: 'text'},
                    
                    // backlinks section
                    {key: 'backlinks_section_separator', label: 'backlinks section', type: 'separator'},
                    {key: 'driggs_cgig_id', label: 'driggs_cgig_id', type: 'number'},
                    {key: 'driggs_citations_done', label: 'driggs_citations_done', type: 'boolean'},
                    {key: 'driggs_social_profiles_done', label: 'driggs_social_profiles_done', type: 'boolean'},
                    
                    // basics section
                    {key: 'basics_section_separator', label: 'basics section', type: 'separator'},
                    {key: 'driggs_industry', label: 'driggs_industry', type: 'text'},
                    {key: 'driggs_keywords', label: 'driggs_keywords', type: 'textarea'},
                    {key: 'driggs_category', label: 'driggs_category', type: 'text'},
                    {key: 'driggs_site_type_purpose', label: 'driggs_site_type_purpose', type: 'text'},
                    {key: 'driggs_email_1', label: 'driggs_email_1', type: 'email'},
                    {key: 'driggs_hours', label: 'driggs_hours', type: 'textarea'},
                    {key: 'driggs_owner_name', label: 'driggs_owner_name', type: 'text'},
                    {key: 'driggs_short_descr', label: 'driggs_short_descr', type: 'textarea'},
                    {key: 'driggs_long_descr', label: 'driggs_long_descr', type: 'textarea'},
                    {key: 'driggs_year_opened', label: 'driggs_year_opened', type: 'number'},
                    {key: 'driggs_employees_qty', label: 'driggs_employees_qty', type: 'number'},
                    {key: 'driggs_payment_methods', label: 'driggs_payment_methods', type: 'textarea'},
                    {key: 'driggs_special_note_for_ai_tool', label: 'driggs_special_note_for_ai_tool', type: 'textarea'},
                    {key: 'driggs_social_media_links', label: 'driggs_social_media_links', type: 'textarea'},
                    
                    // misc section
                    {key: 'misc_section_separator', label: 'misc section', type: 'separator'},
                    {key: 'updated_at', label: 'updated_at', type: 'datetime'},
                    {key: 'fk_users_id', label: 'fk_users_id', type: 'text'},
                    {key: 'true_root_domain', label: 'true_root_domain', type: 'text'},
                    {key: 'full_subdomain', label: 'full_subdomain', type: 'text'},
                    {key: 'webproperty_type', label: 'webproperty_type', type: 'text'},
                    {key: 'ns_full', label: 'ns_full', type: 'text'},
                    {key: 'ip_address', label: 'ip_address', type: 'text'},
                    {key: 'is_wp_site', label: 'is_wp_site', type: 'boolean'},
                    {key: 'wpuser1', label: 'wpuser1', type: 'text'},
                    {key: 'wppass1', label: 'wppass1', type: 'password'},
                    {key: 'wp_plugin_installed1', label: 'wp_plugin_installed1', type: 'boolean'},
                    {key: 'wp_plugin_connected2', label: 'wp_plugin_connected2', type: 'boolean'},
                    {key: 'wp_rest_app_pass', label: 'wp_rest_app_pass', type: 'textarea'},
                    {key: 'fk_domreg_hostaccount', label: 'fk_domreg_hostaccount', type: 'text'},
                    {key: 'is_starred1', label: 'is_starred1', type: 'text'},
                    {key: 'icon_name', label: 'icon_name', type: 'text'},
                    {key: 'icon_color', label: 'icon_color', type: 'text'},
                    {key: 'is_bulldozer', label: 'is_bulldozer', type: 'boolean'},
                    {key: 'is_competitor', label: 'is_competitor', type: 'boolean'},
                    {key: 'is_external', label: 'is_external', type: 'boolean'},
                    {key: 'is_internal', label: 'is_internal', type: 'boolean'},
                    {key: 'is_ppx', label: 'is_ppx', type: 'boolean'},
                    {key: 'is_ms', label: 'is_ms', type: 'boolean'},
                    {key: 'is_wayback_rebuild', label: 'is_wayback_rebuild', type: 'boolean'},
                    {key: 'is_naked_wp_build', label: 'is_naked_wp_build', type: 'boolean'},
                    {key: 'is_rnr', label: 'is_rnr', type: 'boolean'},
                    {key: 'is_aff', label: 'is_aff', type: 'boolean'},
                    {key: 'is_other1', label: 'is_other1', type: 'boolean'},
                    {key: 'is_other2', label: 'is_other2', type: 'boolean'},
                    {key: 'is_flylocal', label: 'is_flylocal', type: 'boolean'},
                    
                    // Additional Lumora-specific fields not in /drom
                    {key: 'driggs_logo_url', label: 'driggs_logo_url', type: 'logo_url'},
                    {key: 'snailimage', label: 'snailimage', type: 'text'},
                    {key: 'snail_image_url', label: 'snail_image_url', type: 'text'},
                    {key: 'snail_image_status', label: 'snail_image_status', type: 'text'},
                    {key: 'snail_image_error', label: 'snail_image_error', type: 'textarea'},
                    {key: 'screenshot_url', label: 'screenshot_url', type: 'text'},
                    {key: 'screenshot_taken_at', label: 'screenshot_taken_at', type: 'datetime'},
                    {key: 'screenshot_status', label: 'screenshot_status', type: 'text'},
                    {key: 'rel_cncglub_id', label: 'rel_cncglub_id', type: 'number'},
                    {key: 'rel_city_id', label: 'rel_city_id', type: 'number'},
                    {key: 'rel_industry_id', label: 'rel_industry_id', type: 'number'}
                ];
                
                fields.forEach(function(field) {
                    // Handle separator rows differently
                    if (field.type === 'separator') {
                        let separatorTr = $('<tr style="background-color: #333; color: white;"></tr>');
                        
                        // Empty checkbox cell
                        let separatorCheckboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center; background-color: #333;"></td>');
                        separatorTr.append(separatorCheckboxTd);
                        
                        // Separator label spanning remaining columns
                        let separatorLabelTd = $('<td colspan="4" style="padding: 12px 8px; border: 1px solid #ddd; font-weight: bold; text-align: center; background-color: #333; color: white; font-size: 14px;"></td>');
                        separatorLabelTd.text(field.label);
                        separatorTr.append(separatorLabelTd);
                        
                        tbody.append(separatorTr);
                        return; // Skip normal row processing for separators
                    }
                    
                    let tr = $('<tr style="cursor: pointer;"></tr>');
                    
                    // Apply special background color to specific rows
                    const specialBgFields = ['wppma_id', 'wppma_db_only_created_at', 'wppma_db_only_updated_at', 'id', 'created_at'];
                    const isSpecialBg = specialBgFields.includes(field.key);
                    
                    if (isSpecialBg) {
                        tr.css('background-color', '#d5d5d5');
                        tr.hover(function() {
                            $(this).css('background-color', '#c5c5c5');
                        }, function() {
                            $(this).css('background-color', '#d5d5d5');
                        });
                    } else {
                        tr.hover(function() {
                            $(this).css('background-color', '#f9f9f9');
                        }, function() {
                            $(this).css('background-color', '');
                        });
                    }
                    
                    // Checkbox column
                    let checkboxTd = $('<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"></td>');
                    if (isSpecialBg) checkboxTd.css('background-color', '#d5d5d5');
                    let checkbox = $('<input type="checkbox" style="width: 20px; height: 20px;" data-field="' + field.key + '">');
                    checkboxTd.append(checkbox);
                    tr.append(checkboxTd);
                    
                    // Field name column - bold DB field names
                    let fieldNameTd = $('<td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; color: #23282d; font-family: monospace;"></td>');
                    if (isSpecialBg) fieldNameTd.css('background-color', '#d5d5d5');
                    fieldNameTd.text(field.key);
                    tr.append(fieldNameTd);
                    
                    // Value column - editable fields
                    let value = currentData[field.key] !== undefined ? currentData[field.key] : '';
                    let valueTd = $('<td style="padding: 8px; border: 1px solid #ddd; min-width: 200px;"></td>');
                    if (isSpecialBg) valueTd.css('background-color', '#d5d5d5');
                    
                    // Handle different field types
                    if (field.type === 'boolean') {
                        // Toggle switch for boolean fields
                        let toggleSwitch = createToggleSwitch(field.key, value == 1);
                        valueTd.append(toggleSwitch);
                    } else if (field.key === 'wppma_id' || field.key === 'id' || field.type === 'datetime') {
                        // Read-only fields (ID fields and datetime fields)
                        valueTd.text(value);
                        valueTd.css('color', '#666');
                        valueTd.css('font-style', 'italic');
                        valueTd.css('cursor', 'default');
                        valueTd.css('user-select', 'text');
                        valueTd.css('-webkit-user-select', 'text');
                        valueTd.css('-moz-user-select', 'text');
                        valueTd.addClass('lumora-driggs-readonly-field');
                        valueTd.attr('title', 'This field is read-only (can select text but not edit)');
                        // Ensure special background color is preserved
                        if (isSpecialBg) {
                            valueTd.css('background-color', '#d5d5d5');
                        }
                    } else if (field.type === 'logo_url') {
                        // Special handling for logo URL with image preview
                        let logoContainer = $('<div style="display: flex; flex-direction: column; width: 100%; height: 100%;"></div>');
                        
                        // Editable text input for URL
                        let urlInput = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; margin-bottom: 5px; font-size: 12px;">');
                        urlInput.val(value || '');
                        urlInput.attr('data-field', field.key);
                        
                        // Image preview container
                        let imagePreview = $('<div style="width: 100%; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; border-radius: 3px; background: #f9f9f9;"></div>');
                        
                        // Function to update image preview
                        function updateImagePreview(url) {
                            if (url && url.trim() !== '') {
                                let img = $('<img style="max-width: 100%; max-height: 38px; object-fit: contain;">');
                                img.attr('src', url);
                                img.on('load', function() {
                                    imagePreview.html(img);
                                });
                                img.on('error', function() {
                                    imagePreview.html('<span style="color: #999; font-size: 11px;">Invalid image URL</span>');
                                });
                            } else {
                                imagePreview.html('<span style="color: #ccc; font-size: 11px;">No logo URL</span>');
                            }
                        }
                        
                        // Update preview on input change
                        urlInput.on('input', function() {
                            let newUrl = $(this).val();
                            updateImagePreview(newUrl);
                        });
                        
                        // Save on blur
                        urlInput.on('blur', function() {
                            let newValue = $(this).val();
                            if (newValue !== value) {
                                updateField(field.key, newValue);
                                currentData[field.key] = newValue;
                                hasChanges = true;
                            }
                        });
                        
                        // Initialize with current value
                        updateImagePreview(value);
                        
                        logoContainer.append(urlInput).append(imagePreview);
                        valueTd.append(logoContainer);
                        valueTd.css('padding', '6px');
                    } else {
                        // Editable text fields
                        valueTd.text(value);
                        valueTd.attr('data-field', field.key);
                        valueTd.attr('data-type', field.type);
                        valueTd.css('cursor', 'text');
                        valueTd.on('click', function() {
                            startInlineEdit($(this), value, field.key, field.type);
                        });
                    }
                    
                    tr.append(valueTd);
                    
                    // Add new shortcode 1 column
                    let shortcodeTd = $('<td style="padding: 8px; border: 1px solid #ddd; position: relative; white-space: nowrap;"></td>');
                    if (isSpecialBg) shortcodeTd.css('background-color', '#d5d5d5');
                    
                    // Create copy button (now on the left)
                    let copyBtn = $('<button style="padding: 4px 8px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; font-size: 12px; color: #333; transition: background-color 0.2s; margin-right: 8px;">Copy</button>');
                    
                    // Add hover effect with yellow background
                    copyBtn.hover(
                        function() { $(this).css('background-color', '#ffeb3b'); },
                        function() { $(this).css('background-color', '#f0f0f0'); }
                    );
                    
                    // Create shortcode text (now on the right)
                    let shortcodeText = '[sitespren dbcol="' + field.key + '"]';
                    let shortcodeSpan = $('<span style="font-family: monospace; font-size: 12px; color: #333;"></span>');
                    shortcodeSpan.text(shortcodeText);
                    
                    // Copy to clipboard functionality
                    copyBtn.on('click', function(e) {
                        e.stopPropagation();
                        copyShortcodeToClipboard(shortcodeText, $(this));
                    });
                    
                    shortcodeTd.append(copyBtn);
                    shortcodeTd.append(shortcodeSpan);
                    tr.append(shortcodeTd);
                    
                    // stuff3 column - roaring div
                    let stuff3Td = $('<td class="lumora-stuff3-cell" style="padding: 0; border: 1px solid #ddd; width: 20px;"></td>');
                    if (isSpecialBg) stuff3Td.css('background-color', '#d5d5d5');
                    let roaringDiv = $('<div class="lumora_roaring_div">📡</div>');
                    stuff3Td.append(roaringDiv);
                    tr.append(stuff3Td);
                    
                    tbody.append(tr);
                });
            }
            
            function createToggleSwitch(fieldKey, isChecked) {
                let switchContainer = $('<div class="lumora-toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;"></div>');
                
                let checkbox = $('<input type="checkbox" style="opacity: 0; width: 0; height: 0;">');
                checkbox.prop('checked', isChecked);
                checkbox.attr('data-field', fieldKey);
                
                let slider = $('<span class="lumora-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>');
                
                let knob = $('<span class="lumora-toggle-knob" style="position: absolute; content: \'\'; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>');
                
                if (isChecked) {
                    slider.css('background-color', '#2196F3');
                    knob.css('transform', 'translateX(26px)');
                }
                
                slider.append(knob);
                switchContainer.append(checkbox);
                switchContainer.append(slider);
                
                // Toggle functionality
                switchContainer.on('click', function() {
                    let isCurrentlyChecked = checkbox.prop('checked');
                    let newValue = !isCurrentlyChecked;
                    
                    checkbox.prop('checked', newValue);
                    
                    if (newValue) {
                        slider.css('background-color', '#2196F3');
                        knob.css('transform', 'translateX(26px)');
                    } else {
                        slider.css('background-color', '#ccc');
                        knob.css('transform', 'translateX(0px)');
                    }
                    
                    // Update field
                    updateField(fieldKey, newValue ? 1 : 0);
                    currentData[fieldKey] = newValue ? 1 : 0;
                    hasChanges = true;
                });
                
                return switchContainer;
            }
            
            function startInlineEdit(cell, currentValue, fieldKey, fieldType) {
                // Skip if already editing
                if (cell.hasClass('editing')) return;
                
                cell.addClass('editing');
                let originalText = cell.text();
                cell.empty();
                
                let input;
                if (fieldType === 'textarea') {
                    input = $('<textarea style="width: 100%; height: 60px; padding: 4px; border: 1px solid #ddd; border-radius: 3px; resize: vertical; font-family: inherit; font-size: 14px;"></textarea>');
                } else if (fieldType === 'email') {
                    input = $('<input type="email" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; font-size: 14px;">');
                } else if (fieldType === 'number') {
                    input = $('<input type="number" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; font-size: 14px;">');
                } else if (fieldType === 'password') {
                    input = $('<input type="password" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; font-size: 14px;">');
                } else {
                    input = $('<input type="text" style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; font-size: 14px;">');
                }
                
                input.val(currentValue);
                cell.append(input);
                input.focus();
                
                // Position cursor at end of text instead of selecting all
                if (input[0].setSelectionRange) {
                    let len = currentValue ? currentValue.length : 0;
                    input[0].setSelectionRange(len, len);
                } else if (input[0].createTextRange) {
                    let range = input[0].createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', currentValue ? currentValue.length : 0);
                    range.moveStart('character', currentValue ? currentValue.length : 0);
                    range.select();
                }
                
                // Save on blur or enter
                function saveEdit() {
                    let newValue = input.val();
                    cell.removeClass('editing');
                    
                    // Always save the value, including empty strings (which become null)
                    // Only skip if user didn't actually change anything
                    if (newValue !== currentValue) {
                        // Convert empty string to null for database storage
                        let valueToSave = newValue.trim() === '' ? '' : newValue;
                        updateField(fieldKey, valueToSave);
                        
                        // Display empty cells as empty, not "null" text
                        cell.text(newValue.trim() === '' ? '' : newValue);
                        currentData[fieldKey] = newValue.trim() === '' ? null : newValue;
                        hasChanges = true;
                    } else {
                        cell.text(originalText);
                    }
                }
                
                // Cancel on escape
                function cancelEdit() {
                    cell.removeClass('editing');
                    cell.text(originalText);
                }
                
                input.on('blur', saveEdit);
                input.on('keydown', function(e) {
                    if (e.key === 'Enter' && fieldType !== 'textarea') {
                        e.preventDefault();
                        saveEdit();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelEdit();
                    }
                });
            }
            
            function updateField(field, value) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lumora_driggs_update_field',
                        nonce: '<?php echo wp_create_nonce('lumora_driggs_nonce'); ?>',
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert('Error updating field: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error updating field');
                    }
                });
            }
            
            function copyShortcodeToClipboard(shortcode, element) {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(shortcode).then(function() {
                        showCopyFeedback(element, 'Copied!');
                    }).catch(function() {
                        copyShortcodeToClipboardFallback(shortcode, element);
                    });
                } else {
                    copyShortcodeToClipboardFallback(shortcode, element);
                }
            }
            
            function copyShortcodeToClipboardFallback(shortcode, element) {
                let textArea = document.createElement('textarea');
                textArea.value = shortcode;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    showCopyFeedback(element, 'Copied!');
                } catch (err) {
                    showCopyFeedback(element, 'Copy failed');
                }
                
                document.body.removeChild(textArea);
            }
            
            function showCopyFeedback(element, message) {
                let originalText = element.text();
                element.text(message);
                element.css('background-color', '#4CAF50');
                element.css('color', 'white');
                
                setTimeout(function() {
                    element.text(originalText);
                    element.css('background-color', '#f0f0f0');
                    element.css('color', '#333');
                }, 1500);
            }
            
            // Export button handlers
            $('.button[data-format]').on('click', function() {
                let format = $(this).data('format');
                let selectedFields = [];
                
                // Get selected fields
                $('input[type="checkbox"][data-field]:checked').each(function() {
                    selectedFields.push($(this).data('field'));
                });
                
                // If no fields selected, use all fields
                if (selectedFields.length === 0) {
                    selectedFields = Object.keys(currentData);
                }
                
                // Trigger export
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lumora_driggs_export',
                        nonce: '<?php echo wp_create_nonce('lumora_driggs_nonce'); ?>',
                        format: format,
                        fields: selectedFields
                    },
                    success: function(response) {
                        if (response.success && response.data.download_url) {
                            // Create temporary download link
                            let link = document.createElement('a');
                            link.href = response.data.download_url;
                            link.download = response.data.filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            alert('Export failed: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Export failed');
                    }
                });
            });
            
            // WordPress site settings form handler
            $('#lumora-site-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lumora_update_site_settings',
                        nonce: '<?php echo wp_create_nonce('lumora_driggs_nonce'); ?>',
                        blogname: formData.get('blogname'),
                        blogdescription: formData.get('blogdescription'),
                        site_icon: formData.get('site_icon')
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Site settings updated successfully!');
                        } else {
                            alert('Error updating site settings: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error updating site settings');
                    }
                });
            });
            
            // Site icon chooser
            $('#lumora-choose-site-icon').on('click', function(e) {
                e.preventDefault();
                
                let mediaUploader = wp.media({
                    title: 'Choose Site Icon',
                    button: {
                        text: 'Use This Icon'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                mediaUploader.on('select', function() {
                    let attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#lumora-site-icon-id').val(attachment.id);
                    
                    // Update preview
                    let preview = '<img src="' + attachment.sizes.thumbnail.url + '" alt="Site Icon" style="width: 24px; height: 24px; border-radius: 3px;">';
                    $(preview).insertBefore('#lumora-choose-site-icon');
                });
                
                mediaUploader.open();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Lumora Locations Mar page
     */
    public function lumora_locations_mar() {
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap">
            <h1>✨ Lumora Hub - Locations Manager</h1>
            <p>Manage your zen locations data.</p>
            <p><em>Location management interface coming soon...</em></p>
        </div>
        <?php
    }
    
    /**
     * Lumora Services Mar page  
     */
    public function lumora_services_mar() {
        $this->suppress_all_admin_notices();
        
        ?>
        <div class="wrap">
            <h1>✨ Lumora Hub - Services Manager</h1>
            <p>Manage your zen services data.</p>
            <p><em>Services management interface coming soon...</em></p>
        </div>
        <?php
    }
    
    /**
     * Ensure zen_sitespren table has a record for the current site
     */
    private function ensure_sitespren_record() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        // Check if record exists
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE wppma_id = 1");
        
        if ($existing == 0) {
            // Create default record
            $site_url = get_site_url();
            $parsed_url = parse_url($site_url);
            $domain = isset($parsed_url['host']) ? $parsed_url['host'] : 'localhost';
            
            $wpdb->insert(
                $table_name,
                array(
                    'wppma_id' => 1,
                    'id' => wp_generate_uuid4(),
                    'sitespren_base' => $domain,
                    'driggs_brand_name' => get_option('blogname', 'My Site'),
                    'driggs_site_type_purpose' => 'WordPress Site',
                    'is_wp_site' => 1,
                    'wp_plugin_installed1' => 1,
                    'wp_plugin_connected2' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                )
            );
        }
    }
    
    /**
     * AJAX: Get driggs data for current site
     */
    public function lumora_driggs_get_data() {
        check_ajax_referer('lumora_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        $current_site_url = get_site_url();
        
        try {
            // Get the single record for this site - there should only be one row
            $result = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1", ARRAY_A);
            
            if ($wpdb->last_error) {
                wp_send_json_error('Database error: ' . $wpdb->last_error);
                return;
            }
            
            if (!$result) {
                wp_send_json_error('No data found for current site');
                return;
            }
            
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error('Error retrieving data: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Update single field
     */
    public function lumora_driggs_update_field() {
        check_ajax_referer('lumora_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        $field = sanitize_text_field($_POST['field']);
        $value = $_POST['value']; // We'll sanitize this based on field type
        
        // Validate field name - all editable fields from zen_sitespren table (matching /drom order)
        $allowed_fields = [
            // Core site fields
            'created_at', 'sitespren_base', 'true_root_domain', 'full_subdomain', 'webproperty_type',
            'fk_users_id', 'updated_at',
            
            // WordPress fields
            'wpuser1', 'wppass1', 'wp_plugin_installed1', 'wp_plugin_connected2',
            'fk_domreg_hostaccount', 'is_wp_site', 'wp_rest_app_pass',
            
            // Business information
            'driggs_industry', 'driggs_city', 'driggs_brand_name', 'driggs_site_type_purpose', 'driggs_email_1',
            
            // Address fields
            'driggs_address_full', 'driggs_address_species_id', 'driggs_street_1', 'driggs_street_2',
            'driggs_state_code', 'driggs_zip', 'driggs_state_full', 'driggs_country', 'driggs_address_species_note',
            
            // Contact fields
            'driggs_phone_1', 'driggs_phone1_platform_id',
            
            // Business details (some may need DB columns added)
            'driggs_hours', 'driggs_owner_name', 'driggs_short_descr', 'driggs_long_descr', 
            'driggs_year_opened', 'driggs_employees_qty', 'driggs_keywords', 'driggs_category',
            'driggs_payment_methods', 'driggs_social_media_links',
            
            // Project management
            'driggs_cgig_id', 'driggs_citations_done', 'driggs_social_profiles_done', 'driggs_special_note_for_ai_tool',
            'driggs_revenue_goal', 'driggs_logo_url',
            
            // Technical fields
            'ns_full', 'ip_address', 'is_starred1', 'icon_name', 'icon_color',
            
            // Classification flags
            'is_bulldozer', 'is_competitor', 'is_external', 'is_internal', 'is_ppx', 'is_ms',
            'is_wayback_rebuild', 'is_naked_wp_build', 'is_rnr', 'is_aff', 'is_other1', 'is_other2', 'is_flylocal',
            
            // Media management (existing in DB)
            'snailimage', 'snail_image_url', 'snail_image_status', 'snail_image_error',
            'screenshot_url', 'screenshot_taken_at', 'screenshot_status',
            
            // Relationships (some may need DB columns added)
            'rel_cncglub_id', 'rel_city_id', 'rel_industry_id'
        ];
        
        if (!in_array($field, $allowed_fields)) {
            wp_send_json_error('Invalid field name');
            return;
        }
        
        // Sanitize value based on field type (updated for all new fields)
        $number_fields = ['driggs_phone1_platform_id', 'driggs_cgig_id', 'driggs_revenue_goal', 'driggs_address_species_id', 
                         'driggs_year_opened', 'driggs_employees_qty', 'rel_cncglub_id', 'rel_city_id', 'rel_industry_id'];
        $boolean_fields = ['wp_plugin_installed1', 'wp_plugin_connected2', 'is_wp_site', 'is_bulldozer', 'driggs_citations_done', 
                          'driggs_social_profiles_done', 'is_competitor', 'is_external', 'is_internal', 'is_ppx', 'is_ms', 
                          'is_wayback_rebuild', 'is_naked_wp_build', 'is_rnr', 'is_aff', 'is_other1', 'is_other2', 'is_flylocal'];
        $email_fields = ['driggs_email_1'];
        $datetime_fields = ['created_at', 'updated_at', 'screenshot_taken_at'];
        
        if (in_array($field, $number_fields)) {
            $value = $value === '' ? NULL : intval($value);
        } elseif (in_array($field, $boolean_fields)) {
            $value = $value ? 1 : 0;
        } elseif (in_array($field, $email_fields)) {
            $value = $value === '' ? NULL : sanitize_email($value);
        } elseif (in_array($field, $datetime_fields)) {
            // Allow datetime values in MySQL format (YYYY-MM-DD HH:MM:SS)
            $value = $value === '' ? NULL : sanitize_text_field($value);
        } else {
            $value = $value === '' ? NULL : sanitize_textarea_field($value);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        try {
            // Update the single record (should only be one record per site)
            $result = $wpdb->update(
                $table_name,
                array($field => $value),
                array('wppma_id' => 1), // Update the first/only record
                null,
                array('%d')
            );
            
            if ($result === false) {
                wp_send_json_error('Database update failed: ' . $wpdb->last_error);
                return;
            }
            
            // Also update the updated_at timestamp
            $wpdb->update(
                $table_name,
                array('wppma_db_only_updated_at' => current_time('mysql')),
                array('wppma_id' => 1),
                array('%s'),
                array('%d')
            );
            
            wp_send_json_success('Field updated successfully');
        } catch (Exception $e) {
            wp_send_json_error('Error updating field: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Export data in various formats
     */
    public function lumora_driggs_export() {
        check_ajax_referer('lumora_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        $format = sanitize_text_field($_POST['format']);
        $fields = isset($_POST['fields']) ? $_POST['fields'] : null;
        
        // Validate format
        if (!Lumora_Tax_Exports::is_valid_format($format)) {
            wp_send_json_error('Invalid export format');
            return;
        }
        
        // Get current site data
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        $data = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1", ARRAY_A);
        
        if (!$data) {
            wp_send_json_error('No data found to export');
            return;
        }
        
        try {
            // Generate filename
            $filename = Lumora_Tax_Exports::generate_export_filename($format);
            
            // For AJAX response, we'll create a temporary file and return download URL
            $upload_dir = wp_upload_dir();
            $temp_file = $upload_dir['path'] . '/' . $filename;
            
            // Generate content
            switch ($format) {
                case 'sharkintax':
                    $content = Lumora_Tax_Exports::generate_sharkintax($data, $fields);
                    break;
                case 'walrustax':
                    $content = Lumora_Tax_Exports::generate_walrustax($data, $fields);
                    break;
                case 'csv':
                    $content = Lumora_Tax_Exports::generate_csv($data, $fields);
                    break;
                case 'xls':
                    $content = Lumora_Tax_Exports::generate_xls($data, $fields);
                    break;
                case 'sql':
                    $content = Lumora_Tax_Exports::generate_sql($data, $fields);
                    break;
            }
            
            // Write to temp file
            file_put_contents($temp_file, $content);
            
            // Return download URL
            $download_url = $upload_dir['url'] . '/' . $filename;
            
            wp_send_json_success(array(
                'download_url' => $download_url,
                'filename' => $filename
            ));
            
        } catch (Exception $e) {
            wp_send_json_error('Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Save all changes
     */
    public function lumora_driggs_save_all() {
        check_ajax_referer('lumora_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        try {
            // Update the updated_at timestamp
            $result = $wpdb->update(
                $table_name,
                array(
                    'wppma_db_only_updated_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('wppma_id' => 1),
                array('%s', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                wp_send_json_error('Database update failed: ' . $wpdb->last_error);
                return;
            }
            
            wp_send_json_success('All changes saved successfully');
        } catch (Exception $e) {
            wp_send_json_error('Error saving changes: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Reset to defaults
     */
    public function lumora_driggs_reset() {
        check_ajax_referer('lumora_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
            wp_send_json_error('Reset confirmation required');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zen_sitespren';
        
        try {
            // Reset to default values
            $site_url = get_site_url();
            $parsed_url = parse_url($site_url);
            $domain = isset($parsed_url['host']) ? $parsed_url['host'] : 'localhost';
            
            $default_data = array(
                'sitespren_base' => $domain,
                'driggs_brand_name' => get_option('blogname', 'My Site'),
                'driggs_site_type_purpose' => 'WordPress Site',
                'is_wp_site' => 1,
                'wp_plugin_installed1' => 1,
                'wp_plugin_connected2' => 1,
                'updated_at' => current_time('mysql'),
                'wppma_db_only_updated_at' => current_time('mysql'),
                
                // Clear business fields
                'driggs_industry' => null,
                'driggs_city' => null,
                'driggs_email_1' => null,
                'driggs_phone_1' => null,
                'driggs_address_full' => null,
                'driggs_street_1' => null,
                'driggs_street_2' => null,
                'driggs_state_code' => null,
                'driggs_zip' => null,
                'driggs_state_full' => null,
                'driggs_country' => null,
                'driggs_hours' => null,
                'driggs_owner_name' => null,
                'driggs_short_descr' => null,
                'driggs_long_descr' => null,
                'driggs_keywords' => null,
                'driggs_category' => null,
                'driggs_payment_methods' => null,
                'driggs_social_media_links' => null,
                'driggs_special_note_for_ai_tool' => null,
                'driggs_logo_url' => null,
                
                // Reset numeric fields
                'driggs_year_opened' => null,
                'driggs_employees_qty' => null,
                'driggs_revenue_goal' => null,
                'driggs_phone1_platform_id' => null,
                'driggs_address_species_id' => null,
                'driggs_cgig_id' => null,
                'rel_cncglub_id' => null,
                'rel_city_id' => null,
                'rel_industry_id' => null,
                
                // Reset boolean fields to defaults
                'driggs_citations_done' => 0,
                'driggs_social_profiles_done' => 0,
                'is_bulldozer' => 0,
                'is_competitor' => 0,
                'is_external' => 0,
                'is_internal' => 0,
                'is_ppx' => 0,
                'is_ms' => 0,
                'is_wayback_rebuild' => 0,
                'is_naked_wp_build' => 0,
                'is_rnr' => 0,
                'is_aff' => 0,
                'is_other1' => 0,
                'is_other2' => 0,
                'is_flylocal' => 0
            );
            
            $result = $wpdb->update(
                $table_name,
                $default_data,
                array('wppma_id' => 1)
            );
            
            if ($result === false) {
                wp_send_json_error('Database reset failed: ' . $wpdb->last_error);
                return;
            }
            
            wp_send_json_success('Data reset to defaults successfully');
        } catch (Exception $e) {
            wp_send_json_error('Error resetting data: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: Update WordPress site settings
     */
    public function lumora_update_site_settings() {
        check_ajax_referer('lumora_driggs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        $blogname = sanitize_text_field($_POST['blogname']);
        $blogdescription = sanitize_text_field($_POST['blogdescription']);
        $site_icon = intval($_POST['site_icon']);
        
        // Update WordPress options
        update_option('blogname', $blogname);
        update_option('blogdescription', $blogdescription);
        
        if ($site_icon > 0) {
            update_option('site_icon', $site_icon);
        }
        
        wp_send_json_success('Site settings updated successfully');
    }
    
    /**
     * Lumora BeamRay Mar page - WordPress Posts & Pages Manager (exact clone)
     */
    public function lum_beamray_mar_page() {
        // AGGRESSIVE NOTICE SUPPRESSION
        $this->suppress_all_admin_notices();
        
        // Include the beamraymar handler
        require_once LUMORA_PLUGIN_PATH . 'beamraymar-clone-from-snef/lum-beamray-handler.php';
        
        // Call the main function
        lumora_beamray_page();
    }
    
    /**
     * AJAX: Create new post/page for BeamRay
     */
    public function lumora_beamray_create_new_post() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lumora_beamray_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $post_data = array(
            'post_title' => sanitize_text_field($_POST['post_title']),
            'post_content' => wp_kses_post($_POST['post_content']),
            'post_status' => sanitize_text_field($_POST['post_status']),
            'post_type' => sanitize_text_field($_POST['post_type']),
            'post_name' => sanitize_text_field($_POST['post_name']),
            'post_parent' => intval($_POST['post_parent'])
        );

        $post_id = wp_insert_post($post_data);
        if ($post_id && !is_wp_error($post_id)) {
            wp_send_json_success(array('message' => 'Post created successfully', 'post_id' => $post_id));
        } else {
            wp_send_json_error('Failed to create post');
        }
    }
    
    /**
     * AJAX: Update post field for BeamRay
     */
    public function lumora_beamray_update_post_field() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lumora_beamray_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $post_id = intval($_POST['post_id']);
        $field = sanitize_text_field($_POST['field']);
        $value = sanitize_textarea_field($_POST['value']);

        $update_data = array(
            'ID' => $post_id,
            $field => $value
        );

        $result = wp_update_post($update_data);
        if ($result && !is_wp_error($result)) {
            wp_send_json_success('Field updated successfully');
        } else {
            wp_send_json_error('Failed to update field');
        }
    }
    
    /**
     * AJAX: Update meta field for BeamRay
     */
    public function lumora_beamray_update_meta_field() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lumora_beamray_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $post_id = intval($_POST['post_id']);
        $meta_key = sanitize_text_field($_POST['meta_key']);
        $meta_value = sanitize_textarea_field($_POST['meta_value']);

        $result = update_post_meta($post_id, $meta_key, $meta_value);
        if ($result !== false) {
            wp_send_json_success('Meta field updated successfully');
        } else {
            wp_send_json_error('Failed to update meta field');
        }
    }
    
    /**
     * Add Lumora Features column to media library
     */
    public function add_lumora_media_column($columns) {
        $columns['lumora_features'] = '<strong>Lumora Features</strong>';
        return $columns;
    }
    
    /**
     * Display content for Lumora Features column in media library
     */
    public function lumora_media_column_content($column_name, $attachment_id) {
        if ($column_name === 'lumora_features') {
            echo '<button type="button" class="button button-secondary lumora-rename-file-btn" data-attachment-id="' . esc_attr($attachment_id) . '">Rename File</button>';
        }
    }
    
    /**
     * Add CSS styles for Lumora media column and rename popup
     */
    public function lumora_media_column_styles() {
        ?>
        <style type="text/css">
        .column-lumora_features {
            width: 120px;
        }
        
        .lumora-rename-file-btn {
            font-size: 12px;
            padding: 4px 8px;
            height: auto;
            line-height: 1.2;
            white-space: nowrap;
        }
        
        .lumora-rename-file-btn:hover {
            background-color: #f0f0f0;
            border-color: #999;
        }
        
        /* Rename Popup Styles */
        .lumora-rename-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 100000;
            display: none;
        }
        
        .lumora-rename-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow: auto;
            z-index: 100001;
        }
        
        .lumora-rename-popup-header {
            padding: 20px 20px 10px;
            border-bottom: 1px solid #ddd;
            position: relative;
        }
        
        .lumora-rename-popup-header h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .lumora-rename-popup-close {
            position: absolute;
            right: 15px;
            top: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .lumora-rename-popup-close:hover {
            background: #f0f0f0;
            color: #333;
        }
        
        .lumora-rename-popup-body {
            padding: 20px;
        }
        
        .lumora-rename-current-image {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .lumora-rename-current-image img {
            max-width: 200px;
            max-height: 150px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .lumora-rename-field-group {
            margin-bottom: 15px;
        }
        
        .lumora-rename-field-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .lumora-rename-field-group input[type="text"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .lumora-rename-field-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
        }
        
        .lumora-rename-current-name {
            background: #f9f9f9;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            color: #666;
        }
        
        .lumora-rename-popup-footer {
            padding: 15px 20px 20px;
            text-align: right;
        }
        
        .lumora-rename-execute-btn {
            background: #0073aa;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .lumora-rename-execute-btn:hover {
            background: #005a87;
        }
        
        .lumora-rename-execute-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .lumora-rename-cancel-btn {
            background: #f1f1f1;
            color: #333;
            border: 1px solid #ccc;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .lumora-rename-cancel-btn:hover {
            background: #e0e0e0;
        }
        
        .lumora-rename-loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .lumora-rename-error {
            background: #ffebe8;
            border: 1px solid #ff6b6b;
            color: #c92a2a;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .lumora-rename-success {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        </style>
        <?php
    }
    
    /**
     * Add JavaScript for rename popup functionality
     */
    public function lumora_media_rename_popup_script() {
        ?>
        <div id="lumora-rename-popup-overlay" class="lumora-rename-popup-overlay">
            <div class="lumora-rename-popup">
                <div class="lumora-rename-popup-header">
                    <h3>Rename Media File</h3>
                    <button type="button" class="lumora-rename-popup-close" onclick="lumoraCloseRenamePopup()">&times;</button>
                </div>
                <div class="lumora-rename-popup-body">
                    <div class="lumora-rename-current-image">
                        <img id="lumora-rename-preview-image" src="" alt="Preview">
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label for="lumora-current-filename">Current Filename:</label>
                        <div id="lumora-current-filename" class="lumora-rename-current-name"></div>
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label for="lumora-new-filename">New Filename (without extension):</label>
                        <input type="text" id="lumora-new-filename" placeholder="Enter new filename">
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label>
                            <input type="checkbox" id="lumora-update-references" checked> 
                            Update all references in posts, pages, and Elementor (recommended)
                        </label>
                        <small style="color: #666; display: block; margin-top: 4px;">
                            For testing: uncheck this if rename hangs, then try again
                        </small>
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label>
                            <input type="checkbox" id="lumora-create-redirect" checked> 
                            Create redirect from old URL to new URL
                        </label>
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label>
                            <input type="checkbox" id="lumora-auto-populate-meta"> 
                            Update title and alt text to new file name
                        </label>
                    </div>
                    
                    <div id="lumora-rename-message"></div>
                </div>
                <div class="lumora-rename-popup-footer">
                    <button type="button" class="lumora-rename-cancel-btn" onclick="lumoraCloseRenamePopup()">Cancel</button>
                    <button type="button" class="lumora-rename-execute-btn" onclick="lumoraExecuteRename()">Save</button>
                </div>
                
                <!-- Additional Meta Fields Section -->
                <div class="lumora-rename-popup-body" style="border-top: 1px solid #ddd; margin-top: 0;">
                    <h4 style="margin-top: 0; margin-bottom: 15px; color: #333;">Image Details</h4>
                    
                    <div class="lumora-rename-field-group">
                        <label for="lumora-attachment-title">Title:</label>
                        <input type="text" id="lumora-attachment-title" placeholder="Enter image title">
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label for="lumora-attachment-alt">Alt Text:</label>
                        <input type="text" id="lumora-attachment-alt" placeholder="Enter alt text for accessibility">
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label for="lumora-attachment-caption">Caption:</label>
                        <textarea id="lumora-attachment-caption" placeholder="Enter image caption" rows="2"></textarea>
                    </div>
                    
                    <div class="lumora-rename-field-group">
                        <label for="lumora-attachment-description">Description:</label>
                        <textarea id="lumora-attachment-description" placeholder="Enter image description" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        // Add console logging for debugging
        console.log('Lumora: JavaScript loaded');
        
        // Declare variables in global scope
        let currentAttachmentId = null;
        let currentFilename = '';
        let currentExtension = '';
        
        jQuery(document).ready(function($) {
            console.log('Lumora: jQuery ready');
            
            // Handle rename button clicks
            $(document).on('click', '.lumora-rename-file-btn', function() {
                console.log('Lumora: Rename button clicked');
                currentAttachmentId = $(this).data('attachment-id');
                console.log('Lumora: Attachment ID:', currentAttachmentId);
                lumoraOpenRenamePopup(currentAttachmentId);
            });
            
            // Handle Enter key in filename input
            $('#lumora-new-filename').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    lumoraExecuteRename();
                }
            });
            
            // Close popup on overlay click
            $('#lumora-rename-popup-overlay').on('click', function(e) {
                if (e.target === this) {
                    lumoraCloseRenamePopup();
                }
            });
            
            // Handle auto-populate functionality
            $('#lumora-auto-populate-meta').on('change', function() {
                if ($(this).is(':checked')) {
                    lumoraAutoPopulateFields();
                }
            });
            
            // Auto-populate when filename changes if checkbox is checked
            $('#lumora-new-filename').on('input', function() {
                if ($('#lumora-auto-populate-meta').is(':checked')) {
                    lumoraAutoPopulateFields();
                }
            });
        });
        
        function lumoraOpenRenamePopup(attachmentId) {
            console.log('Lumora: Opening popup for attachment ID:', attachmentId);
            // Get attachment details via AJAX
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lumora_get_attachment_details',
                    nonce: '<?php echo wp_create_nonce('lumora_rename_nonce'); ?>',
                    attachment_id: attachmentId
                },
                success: function(response) {
                    if (response.success) {
                        let data = response.data;
                        currentFilename = data.filename;
                        currentExtension = data.extension;
                        
                        // Populate popup
                        jQuery('#lumora-rename-preview-image').attr('src', data.url);
                        jQuery('#lumora-current-filename').text(data.full_filename);
                        jQuery('#lumora-new-filename').val(data.filename);
                        jQuery('#lumora-rename-message').empty();
                        
                        // Populate attachment meta fields
                        jQuery('#lumora-attachment-title').val(data.title || '');
                        jQuery('#lumora-attachment-alt').val(data.alt_text || '');
                        jQuery('#lumora-attachment-caption').val(data.caption || '');
                        jQuery('#lumora-attachment-description').val(data.description || '');
                        
                        // Show popup
                        jQuery('#lumora-rename-popup-overlay').fadeIn(200);
                        
                        // Focus input and select text
                        setTimeout(function() {
                            jQuery('#lumora-new-filename').focus().select();
                        }, 250);
                    } else {
                        alert('Error loading attachment details: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error loading attachment details');
                }
            });
        }
        
        function lumoraCloseRenamePopup() {
            jQuery('#lumora-rename-popup-overlay').fadeOut(200);
            currentAttachmentId = null;
            currentFilename = '';
            currentExtension = '';
        }
        
        function lumoraAutoPopulateFields() {
            let newFilename = jQuery('#lumora-new-filename').val().trim();
            if (newFilename) {
                // Remove hyphens and underscores, then capitalize words
                let cleanName = newFilename.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                jQuery('#lumora-attachment-title').val(cleanName);
                jQuery('#lumora-attachment-alt').val(cleanName);
            }
        }
        
        function lumoraExecuteRename() {
            console.log('Lumora: Execute save called');
            let newFilename = jQuery('#lumora-new-filename').val().trim();
            console.log('Lumora: New filename:', newFilename);
            
            // Validate filename if provided and different from current
            let shouldRename = false;
            if (newFilename && newFilename !== currentFilename) {
                // Validate filename
                if (!/^[a-zA-Z0-9._-]+$/.test(newFilename)) {
                    lumoraShowMessage('Filename can only contain letters, numbers, dots, hyphens, and underscores', 'error');
                    return;
                }
                shouldRename = true;
            } else if (newFilename === currentFilename) {
                // Same filename, just update metadata
                newFilename = '';
                shouldRename = false;
            } else if (!newFilename) {
                // No filename provided, just update metadata
                shouldRename = false;
            }
            
            // Disable button and show loading
            jQuery('.lumora-rename-execute-btn').prop('disabled', true).text('Saving...');
            lumoraShowMessage('Saving changes, please wait...', 'loading');
            
            // Get options
            let updateReferences = jQuery('#lumora-update-references').is(':checked');
            let createRedirect = jQuery('#lumora-create-redirect').is(':checked');
            
            // Get attachment metadata
            let attachmentTitle = jQuery('#lumora-attachment-title').val().trim();
            let attachmentAlt = jQuery('#lumora-attachment-alt').val().trim();
            let attachmentCaption = jQuery('#lumora-attachment-caption').val().trim();
            let attachmentDescription = jQuery('#lumora-attachment-description').val().trim();
            
            console.log('Lumora: About to send AJAX request');
            console.log('Lumora: Attachment ID:', currentAttachmentId);
            console.log('Lumora: Update references:', updateReferences);
            console.log('Lumora: Create redirect:', createRedirect);
            
            // Execute rename via AJAX with increased timeout
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                timeout: 60000, // 60 seconds timeout
                data: {
                    action: 'lumora_rename_media_file',
                    nonce: '<?php echo wp_create_nonce('lumora_rename_nonce'); ?>',
                    attachment_id: currentAttachmentId,
                    new_filename: newFilename,
                    update_references: updateReferences,
                    create_redirect: createRedirect,
                    attachment_title: attachmentTitle,
                    attachment_alt: attachmentAlt,
                    attachment_caption: attachmentCaption,
                    attachment_description: attachmentDescription
                },
                beforeSend: function() {
                    console.log('Lumora: AJAX request starting...');
                },
                success: function(response) {
                    console.log('Lumora: AJAX success response:', response);
                    if (response.success) {
                        lumoraShowMessage('Changes saved successfully!', 'success');
                        
                        // Refresh the media library page after a short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        lumoraShowMessage('Error: ' + response.data, 'error');
                        jQuery('.lumora-rename-execute-btn').prop('disabled', false).text('Save');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Lumora: AJAX error - Status:', status, 'Error:', error);
                    console.log('Lumora: XHR response:', xhr.responseText);
                    if (status === 'timeout') {
                        lumoraShowMessage('Operation timed out. File may have been renamed - please refresh the page to check.', 'error');
                    } else {
                        lumoraShowMessage('Network error occurred: ' + error, 'error');
                    }
                    jQuery('.lumora-rename-execute-btn').prop('disabled', false).text('Save');
                }
            });
        }
        
        function lumoraShowMessage(message, type) {
            let messageDiv = jQuery('#lumora-rename-message');
            messageDiv.removeClass('lumora-rename-error lumora-rename-success lumora-rename-loading');
            
            if (type === 'error') {
                messageDiv.addClass('lumora-rename-error');
            } else if (type === 'success') {
                messageDiv.addClass('lumora-rename-success');
            } else if (type === 'loading') {
                messageDiv.addClass('lumora-rename-loading');
            }
            
            messageDiv.text(message);
        }
        </script>
        <?php
    }
    
    /**
     * AJAX: Get attachment details for rename popup
     */
    public function lumora_get_attachment_details() {
        check_ajax_referer('lumora_rename_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        if (!$attachment_id) {
            wp_send_json_error('Invalid attachment ID');
            return;
        }
        
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_send_json_error('Attachment not found');
            return;
        }
        
        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            wp_send_json_error('File not found on server');
            return;
        }
        
        $url = wp_get_attachment_url($attachment_id);
        $filename_parts = pathinfo($file_path);
        
        // Get attachment metadata
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        
        wp_send_json_success(array(
            'url' => $url,
            'full_filename' => $filename_parts['basename'],
            'filename' => $filename_parts['filename'],
            'extension' => $filename_parts['extension'],
            'file_path' => $file_path,
            'title' => $attachment->post_title,
            'alt_text' => $alt_text,
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content
        ));
    }
    
    /**
     * AJAX: Rename media file
     */
    public function lumora_rename_media_file() {
        // Add debugging
        error_log('Lumora: Starting rename operation');
        
        check_ajax_referer('lumora_rename_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $new_filename = sanitize_file_name($_POST['new_filename']);
        $update_references = isset($_POST['update_references']) && $_POST['update_references'] === 'true';
        $create_redirect = isset($_POST['create_redirect']) && $_POST['create_redirect'] === 'true';
        
        // Get attachment metadata
        $attachment_title = isset($_POST['attachment_title']) ? sanitize_text_field($_POST['attachment_title']) : '';
        $attachment_alt = isset($_POST['attachment_alt']) ? sanitize_text_field($_POST['attachment_alt']) : '';
        $attachment_caption = isset($_POST['attachment_caption']) ? sanitize_textarea_field($_POST['attachment_caption']) : '';
        $attachment_description = isset($_POST['attachment_description']) ? sanitize_textarea_field($_POST['attachment_description']) : '';
        
        if (!$attachment_id) {
            wp_send_json_error('Invalid attachment ID');
            return;
        }
        
        // New filename is optional - if empty, we just update metadata
        $should_rename = !empty($new_filename);
        
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_send_json_error('Attachment not found');
            return;
        }
        
        $file_info = null;
        $old_file_path = null;
        $new_file_path = null;
        
        // Only do file operations if we're actually renaming
        if ($should_rename) {
            $old_file_path = get_attached_file($attachment_id);
            if (!$old_file_path || !file_exists($old_file_path)) {
                wp_send_json_error('Original file not found on server');
                return;
            }
            
            $file_info = pathinfo($old_file_path);
            $new_filename_with_ext = $new_filename . '.' . $file_info['extension'];
            $new_file_path = $file_info['dirname'] . '/' . $new_filename_with_ext;
            
            // Check if new file already exists
            if (file_exists($new_file_path)) {
                wp_send_json_error('A file with that name already exists');
                return;
            }
            
            // Attempt to rename the file
            error_log('Lumora: Attempting to rename file from ' . $old_file_path . ' to ' . $new_file_path);
            if (!rename($old_file_path, $new_file_path)) {
                error_log('Lumora: File rename failed');
                wp_send_json_error('Failed to rename file on server');
                return;
            }
            
            error_log('Lumora: File renamed successfully, updating metadata');
            // Update attachment metadata
            update_attached_file($attachment_id, $new_file_path);
        } else {
            error_log('Lumora: Skipping file rename, updating metadata only');
            // Get file info for later use even if not renaming
            $old_file_path = get_attached_file($attachment_id);
            if ($old_file_path) {
                $file_info = pathinfo($old_file_path);
            }
        }
        
        // Update attachment post data and metadata
        $post_data = array('ID' => $attachment_id);
        
        // Only update filename-related title if no custom title provided, or if it matches old filename
        if (!empty($attachment_title)) {
            $post_data['post_title'] = $attachment_title;
        } else if ($attachment->post_title === $file_info['filename']) {
            $post_data['post_title'] = $new_filename;
        }
        
        // Update caption and description if provided
        if (!empty($attachment_caption)) {
            $post_data['post_excerpt'] = $attachment_caption;
        }
        if (!empty($attachment_description)) {
            $post_data['post_content'] = $attachment_description;
        }
        
        // Update post if we have data to update
        if (count($post_data) > 1) {
            wp_update_post($post_data);
        }
        
        // Update alt text
        if (!empty($attachment_alt)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $attachment_alt);
        }
        
        // Update attachment metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        if ($metadata && isset($metadata['file'])) {
            $upload_dir = wp_upload_dir();
            $relative_path = str_replace($upload_dir['basedir'] . '/', '', $new_file_path);
            $metadata['file'] = $relative_path;
            
            // Update image sizes paths if they exist
            if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                foreach ($metadata['sizes'] as $size => &$size_data) {
                    if (isset($size_data['file'])) {
                        $old_size_filename = str_replace($file_info['filename'], $new_filename, $size_data['file']);
                        $old_size_path = $file_info['dirname'] . '/' . $size_data['file'];
                        $new_size_path = $file_info['dirname'] . '/' . $old_size_filename;
                        
                        // Rename size files if they exist
                        if (file_exists($old_size_path)) {
                            rename($old_size_path, $new_size_path);
                            $size_data['file'] = $old_size_filename;
                        }
                    }
                }
            }
            
            wp_update_attachment_metadata($attachment_id, $metadata);
        }
        
        // Handle URL replacements if requested and file was renamed (with error handling)
        if ($should_rename && $update_references && $file_info) {
            error_log('Lumora: Starting URL replacements');
            try {
                $this->lumora_update_image_references($attachment_id, $file_info, $new_filename);
                error_log('Lumora: URL replacements completed');
            } catch (Exception $e) {
                error_log('Lumora URL replacement error: ' . $e->getMessage());
                // Continue anyway - the file was renamed successfully
            }
        }
        
        // Handle redirect creation if requested and file was renamed (with error handling)
        if ($should_rename && $create_redirect && $file_info) {
            error_log('Lumora: Creating redirects');
            try {
                $this->lumora_create_url_redirect($attachment_id, $file_info, $new_filename);
                error_log('Lumora: Redirects created');
            } catch (Exception $e) {
                error_log('Lumora redirect creation error: ' . $e->getMessage());
                // Continue anyway - the file was renamed successfully
            }
        }
        
        error_log('Lumora: Save operation completed successfully');
        wp_send_json_success('Changes saved successfully');
    }
    
    /**
     * Update image references in posts, pages, and Elementor data (optimized)
     */
    private function lumora_update_image_references($attachment_id, $file_info, $new_filename) {
        global $wpdb;
        
        // Increase time limit for this operation
        set_time_limit(300); // 5 minutes
        
        $upload_dir = wp_upload_dir();
        
        // Generate old URL based on file info
        $old_relative_path = str_replace($upload_dir['basedir'] . '/', '', $file_info['dirname'] . '/' . $file_info['basename']);
        $old_url_from_path = $upload_dir['baseurl'] . '/' . $old_relative_path;
        
        // Generate new URL
        $new_relative_path = str_replace($upload_dir['basedir'] . '/', '', $file_info['dirname'] . '/' . $new_filename . '.' . $file_info['extension']);
        $new_url = $upload_dir['baseurl'] . '/' . $new_relative_path;
        
        // Only proceed if we actually found content to replace
        $check_posts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s",
            '%' . $wpdb->esc_like($old_url_from_path) . '%'
        ));
        
        if ($check_posts > 0) {
            // Update in post content
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
                $old_url_from_path,
                $new_url,
                '%' . $wpdb->esc_like($old_url_from_path) . '%'
            ));
        }
        
        // Check postmeta before updating
        $check_meta = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
            '%' . $wpdb->esc_like($old_url_from_path) . '%'
        ));
        
        if ($check_meta > 0) {
            // Update simple string replacements in postmeta first
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) 
                 WHERE meta_value LIKE %s AND meta_value NOT LIKE 'a:%' AND meta_value NOT LIKE 'O:%'",
                $old_url_from_path,
                $new_url,
                '%' . $wpdb->esc_like($old_url_from_path) . '%'
            ));
            
            // Handle serialized data separately (limit to 50 rows to prevent timeout)
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_id, meta_value FROM {$wpdb->postmeta} 
                 WHERE meta_value LIKE %s AND (meta_value LIKE 'a:%' OR meta_value LIKE 'O:%')
                 LIMIT 50",
                '%' . $wpdb->esc_like($old_url_from_path) . '%'
            ));
            
            foreach ($results as $row) {
                $unserialized = maybe_unserialize($row->meta_value);
                if ($unserialized !== $row->meta_value) {
                    $updated = $this->lumora_recursive_replace($unserialized, $old_url_from_path, $new_url);
                    $serialized = maybe_serialize($updated);
                    
                    if ($serialized !== $row->meta_value) {
                        $wpdb->update(
                            $wpdb->postmeta,
                            array('meta_value' => $serialized),
                            array('meta_id' => $row->meta_id),
                            array('%s'),
                            array('%d')
                        );
                    }
                }
            }
        }
        
        // Handle image size variants (only if they exist)
        $metadata = wp_get_attachment_metadata($attachment_id);
        if ($metadata && isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                if (isset($size_data['file'])) {
                    $old_size_url = $upload_dir['baseurl'] . '/' . dirname($new_relative_path) . '/' . str_replace($new_filename, $file_info['filename'], $size_data['file']);
                    $new_size_url = $upload_dir['baseurl'] . '/' . dirname($new_relative_path) . '/' . $size_data['file'];
                    
                    // Only update if content exists
                    $has_content = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s",
                        '%' . $wpdb->esc_like($old_size_url) . '%'
                    ));
                    
                    if ($has_content > 0) {
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
                            $old_size_url,
                            $new_size_url,
                            '%' . $wpdb->esc_like($old_size_url) . '%'
                        ));
                    }
                    
                    $has_meta = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
                        '%' . $wpdb->esc_like($old_size_url) . '%'
                    ));
                    
                    if ($has_meta > 0) {
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_value LIKE %s",
                            $old_size_url,
                            $new_size_url,
                            '%' . $wpdb->esc_like($old_size_url) . '%'
                        ));
                    }
                }
            }
        }
        
        // Clear any object cache
        wp_cache_flush();
    }
    
    /**
     * Recursively replace URLs in arrays and objects
     */
    private function lumora_recursive_replace($data, $old_url, $new_url) {
        if (is_string($data)) {
            return str_replace($old_url, $new_url, $data);
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->lumora_recursive_replace($value, $old_url, $new_url);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->lumora_recursive_replace($value, $old_url, $new_url);
            }
        }
        
        return $data;
    }
    
    /**
     * Create URL redirect mapping for old filename to new filename
     */
    private function lumora_create_url_redirect($attachment_id, $file_info, $new_filename) {
        $upload_dir = wp_upload_dir();
        
        // Get relative paths
        $old_relative_path = str_replace($upload_dir['basedir'] . '/', '', $file_info['dirname'] . '/' . $file_info['basename']);
        $new_relative_path = str_replace($upload_dir['basedir'] . '/', '', $file_info['dirname'] . '/' . $new_filename . '.' . $file_info['extension']);
        
        // Store redirect mapping in options table
        $redirects = get_option('lumora_file_redirects', array());
        $redirects[$old_relative_path] = $new_relative_path;
        update_option('lumora_file_redirects', $redirects);
        
        // Handle image size variants
        $metadata = wp_get_attachment_metadata($attachment_id);
        if ($metadata && isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                if (isset($size_data['file'])) {
                    $old_size_relative = dirname($old_relative_path) . '/' . str_replace($new_filename, $file_info['filename'], $size_data['file']);
                    $new_size_relative = dirname($new_relative_path) . '/' . $size_data['file'];
                    $redirects[$old_size_relative] = $new_size_relative;
                }
            }
            update_option('lumora_file_redirects', $redirects);
        }
    }
    
    /**
     * Handle file redirects for renamed files
     */
    public function lumora_handle_file_redirects() {
        // Don't handle redirects for admin AJAX requests
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }
        
        $request_uri = $_SERVER['REQUEST_URI'];
        $upload_dir = wp_upload_dir();
        $upload_path = parse_url($upload_dir['baseurl'], PHP_URL_PATH);
        
        // Check if this is a request for an uploaded file
        if (strpos($request_uri, $upload_path) !== 0) {
            return;
        }
        
        // Get the relative path
        $relative_path = substr($request_uri, strlen($upload_path) + 1);
        
        // Remove any query parameters
        $relative_path = strtok($relative_path, '?');
        
        // Check if we have a redirect for this file
        $redirects = get_option('lumora_file_redirects', array());
        
        if (isset($redirects[$relative_path])) {
            $new_url = $upload_dir['baseurl'] . '/' . $redirects[$relative_path];
            wp_redirect($new_url, 301);
            exit;
        }
    }
    
    /**
     * Simple test rename function for debugging
     */
    public function lumora_test_rename() {
        error_log('Lumora: Test rename function called');
        
        check_ajax_referer('lumora_rename_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $new_filename = sanitize_file_name($_POST['new_filename']);
        
        if (!$attachment_id || !$new_filename) {
            wp_send_json_error('Invalid parameters');
            return;
        }
        
        // Just return success without doing anything
        error_log('Lumora: Test rename completed successfully');
        wp_send_json_success('Test rename completed - no actual file changes made');
    }
}
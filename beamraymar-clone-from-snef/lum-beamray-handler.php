<?php
/**
 * Lumora BeamRay Handler
 * Exact clone of beamraymar page from Snefuruplin
 * 
 * @package Lumora
 * @version 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main lum_beamray_mar page function - completely independent
 */
function lumora_beamray_page() {
    // AGGRESSIVE NOTICE SUPPRESSION
    lumora_suppress_all_admin_notices();
    
    // Ensure post_status parameter is always present
    if (!isset($_GET['post_status'])) {
        wp_redirect(admin_url('admin.php?page=lum_beamray_mar&post_status=all'));
        exit;
    }

    global $wpdb;

    // Use current site's wpdb_prefix zen tables
    $zen_sitespren_table = $wpdb->prefix . 'zen_sitespren';
    
    // Get sitespren_base 
    $sitespren_row = $wpdb->get_row("SELECT sitespren_base FROM $zen_sitespren_table WHERE wppma_id = 1");
    $sitespren_base = $sitespren_row ? $sitespren_row->sitespren_base : '';

    // Handle post_status filtering
    $post_status_filter = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : 'all';
    
    // Build WHERE clause based on post_status
    $where_clause = '';
    if ($post_status_filter === 'publish') {
        $where_clause = " AND p.post_status = 'publish'";
    } elseif ($post_status_filter === 'draft') {
        $where_clause = " AND p.post_status = 'draft'";
    } elseif ($post_status_filter === 'all') {
        $where_clause = " AND p.post_status IN ('publish', 'draft', 'private', 'pending', 'auto-draft')";
    }

    // Query posts/pages with zen_orbitposts data
    $posts_query = "
        SELECT DISTINCT 
            p.ID,
            p.post_title,
            p.post_name,
            p.post_content,
            p.post_status,
            p.post_type,
            p.post_date,
            p.post_modified,
            p.post_author,
            p.post_parent,
            p.menu_order,
            p.comment_status,
            p.ping_status,
            pm_elementor.meta_value as elementor_data,
            zo.orbitpost_id,
            zo.redshift_datum,
            zo.rover_datum,
            zo.hudson_imgplanbatch_id,
            zo.is_pinned,
            zo.is_flagged,
            zo.is_starred,
            zo.is_squared,
            zo.created_at as zen_created_at,
            zo.updated_at as zen_updated_at
        FROM {$wpdb->prefix}posts p
        LEFT JOIN {$wpdb->prefix}zen_orbitposts zo ON p.ID = zo.rel_wp_post_id
        LEFT JOIN {$wpdb->prefix}postmeta pm_elementor ON p.ID = pm_elementor.post_id AND pm_elementor.meta_key = '_elementor_data'
        WHERE p.post_type IN ('post', 'page')
        {$where_clause}
        ORDER BY p.post_date DESC
    ";

    $posts_pages = $wpdb->get_results($posts_query);

    // If no posts found, display a message  
    if (empty($posts_pages)) {
        echo '<div class="wrap"><h1>No posts or pages found</h1></div>';
        return;
    }

    ?>
    <div class="wrap beamraymar-wrapper">
        <style>
            /* Beamraymar Custom Styles - Exact copy from Snefuruplin */
            .beamraymar-wrapper {
                margin: 0;
                padding: 0;
                max-width: 100%;
                background-color: #1e1e1e;
                color: #ffffff;
                font-family: 'Roboto', Arial, sans-serif;
                font-size: 11px;
                line-height: 1.2;
            }

            .beamraymar-wrapper * {
                box-sizing: border-box;
            }

            .beamraymar-wrapper h1 {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                margin: 0;
                padding: 15px 20px;
                font-size: 24px;
                font-weight: 300;
                text-align: center;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            }

            /* Top Controls Styling */
            .beamraymar-top-controls {
                background: #2c2c2c;
                padding: 15px 20px;
                border-bottom: 2px solid #404040;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 15px;
            }

            .beamraymar-controls-left {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .beamray_banner1 {
                display: flex;
                align-items: center;
                gap: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 8px 16px;
                border-radius: 6px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }

            .beamray-logo {
                width: 24px;
                height: 24px;
                filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
            }

            .beamray_banner1 strong {
                color: #ffffff;
                font-size: 16px;
                font-weight: 600;
                text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            }

            .beamraymar-info-text {
                color: #cccccc;
                font-size: 13px;
                padding: 5px 0;
            }

            .beamraymar-controls-right {
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: flex-end;
            }

            .beamraymar-create-buttons {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }

            .beamraymar-create-btn {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }

            .beamraymar-create-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                filter: brightness(1.1);
            }

            .beamraymar-duplicate-btn {
                background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
            }

            .beamraymar-nubra-kite {
                color: #888;
                font-size: 10px;
                font-style: italic;
            }

            /* Filter Controls */
            .beamraymar-filter-controls {
                background: #363636;
                padding: 10px 20px;
                border-bottom: 1px solid #505050;
                display: flex;
                gap: 20px;
                align-items: center;
                flex-wrap: wrap;
            }

            .beamraymar-filter-bar {
                display: flex;
                gap: 5px;
            }

            .beamraymar-filter-btn {
                background: #4a4a4a;
                color: #ffffff;
                border: 1px solid #606060;
                padding: 6px 12px;
                text-decoration: none;
                border-radius: 3px;
                font-size: 11px;
                transition: all 0.2s ease;
            }

            .beamraymar-filter-btn:hover {
                background: #5a5a5a;
                color: #ffffff;
                text-decoration: none;
            }

            .beamraymar-filter-btn.active {
                background: #1e3a8a;
                border-color: #3b82f6;
                color: #ffffff;
            }

            /* Rocket Chamber Styling */
            .rocket-chamber {
                background: #2a2a2a;
                border: 2px solid #444;
                margin: 10px 20px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }

            .rocket-chamber-header {
                background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%);
                padding: 8px 15px;
                display: flex;
                align-items: center;
                gap: 8px;
                border-radius: 4px 4px 0 0;
            }

            .rocket-icon {
                font-size: 18px;
                filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
            }

            .rocket-label {
                color: white;
                font-weight: 600;
                font-size: 14px;
                text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            }

            .rocket-chamber-content {
                padding: 15px;
            }

            .rocket-control-table {
                width: 100%;
                border-collapse: collapse;
            }

            .rocket-control-table td {
                padding: 8px 12px;
                border-bottom: 1px solid #404040;
                vertical-align: top;
            }

            .control-label {
                width: 150px;
                color: #ccc;
                font-weight: 600;
                font-size: 12px;
            }

            .control-content {
                color: #fff;
            }

            /* Pagination Controls */
            .beamraymar-pagination-controls {
                display: flex;
                gap: 15px;
                align-items: center;
                flex-wrap: wrap;
            }

            .beamraymar-pagination-bar {
                display: flex;
                gap: 3px;
            }

            .beamraymar-pagination-btn {
                background: #4a4a4a;
                color: #ffffff;
                border: 1px solid #606060;
                padding: 4px 8px;
                text-decoration: none;
                border-radius: 3px;
                font-size: 10px;
                transition: all 0.2s ease;
            }

            .beamraymar-pagination-btn:hover {
                background: #5a5a5a;
                color: #ffffff;
                text-decoration: none;
            }

            .beamraymar-pagination-btn.active {
                background: #1e3a8a;
                border-color: #3b82f6;
                color: #ffffff;
            }

            .beamraymar-pagination-divider {
                width: 1px;
                height: 20px;
                background: #606060;
            }

            /* Search Container */
            .beamraymar-search-container {
                display: flex;
                gap: 5px;
                align-items: center;
            }

            .beamraymar-search-input {
                background: #404040;
                border: 1px solid #606060;
                color: #ffffff;
                padding: 6px 10px;
                border-radius: 3px;
                font-size: 11px;
                width: 200px;
            }

            .beamraymar-search-input::placeholder {
                color: #aaa;
            }

            .beamraymar-clear-btn {
                background: #dc3545;
                color: white;
                border: none;
                padding: 6px 10px;
                border-radius: 3px;
                font-size: 10px;
                cursor: pointer;
                transition: background 0.2s ease;
            }

            .beamraymar-clear-btn:hover {
                background: #c82333;
            }

            /* Wolf and Template Buttons */
            .beamraymar-wolf-btn, .beamraymar-template-btn {
                background: #6c757d;
                color: white;
                border: none;
                padding: 6px 10px;
                border-radius: 3px;
                font-size: 10px;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .beamraymar-wolf-btn:hover, .beamraymar-template-btn:hover {
                background: #5a6268;
                transform: translateY(-1px);
            }

            /* Column Pagination Controls */
            .beamraymar-column-pagination-controls {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .beamraymar-column-pagination-bar {
                display: flex;
                gap: 3px;
                flex-wrap: wrap;
            }

            .beamraymar-column-pagination-btn {
                background: #4a4a4a;
                color: #ffffff;
                border: 1px solid #606060;
                padding: 4px 8px;
                text-decoration: none;
                border-radius: 3px;
                font-size: 10px;
                transition: all 0.2s ease;
            }

            .beamraymar-column-pagination-btn:hover {
                background: #5a5a5a;
                color: #ffffff;
                text-decoration: none;
            }

            .beamraymar-column-pagination-btn.active {
                background: #1e3a8a;
                border-color: #3b82f6;
                color: #ffffff;
            }

            /* Table Styling */
            .beamraymar-table-container {
                margin: 10px 20px;
                background: #1e1e1e;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }

            .beamraymar-table-scroll {
                overflow-x: auto;
                max-height: 70vh;
                overflow-y: auto;
            }

            .beamraymar-table {
                width: 100%;
                border-collapse: collapse;
                background: #1e1e1e;
                color: #ffffff;
                font-size: 10px;
            }

            .beamraymar-table th,
            .beamraymar-table td {
                border: 1px solid #404040;
                padding: 6px 8px;
                text-align: left;
                vertical-align: top;
                word-wrap: break-word;
                max-width: 200px;
            }

            .beamraymar-table th {
                background: #2c2c2c;
                font-weight: 600;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .beamraymar-table tbody tr:hover {
                background: #2a2a2a;
            }

            .beamraymar-table tbody tr:nth-child(even) {
                background: #252525;
            }

            .beamraymar-table tbody tr:nth-child(even):hover {
                background: #2f2f2f;
            }

            /* Column specific styling */
            .column_checkbox {
                width: 40px;
                text-align: center;
            }

            .column_tool_buttons {
                width: 100px;
            }

            .column_wp_posts_id {
                width: 60px;
                text-align: center;
            }

            .column_wp_posts_post_status {
                width: 80px;
            }

            .column_wp_posts_post_title {
                min-width: 200px;
                max-width: 300px;
            }

            .column_wp_posts_post_name {
                min-width: 150px;
                max-width: 200px;
            }

            .column_wp_posts_post_content {
                min-width: 200px;
                max-width: 250px;
            }

            .column_wp_postmeta_meta_key_elementor_data {
                min-width: 150px;
                max-width: 200px;
            }

            /* Table cell styling */
            .tcell_inner_wrapper_div {
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .beamraymar-checkbox {
                cursor: pointer;
                transform: scale(1.1);
            }

            /* Tool buttons styling */
            .beamraymar-tool-btn {
                background: #007cba;
                color: white;
                border: none;
                padding: 3px 6px;
                margin: 1px;
                border-radius: 2px;
                font-size: 9px;
                cursor: pointer;
                transition: background 0.2s ease;
            }

            .beamraymar-tool-btn:hover {
                background: #005a87;
            }

            .beamraymar-tool-btn.edit-btn {
                background: #28a745;
            }

            .beamraymar-tool-btn.edit-btn:hover {
                background: #218838;
            }

            .beamraymar-tool-btn.delete-btn {
                background: #dc3545;
            }

            .beamraymar-tool-btn.delete-btn:hover {
                background: #c82333;
            }

            /* Content preview styling */
            .content-preview {
                max-height: 60px;
                overflow: hidden;
                line-height: 1.3;
                color: #cccccc;
            }

            .content-preview.expanded {
                max-height: none;
            }

            .expand-toggle {
                color: #007cba;
                cursor: pointer;
                font-size: 9px;
                margin-top: 2px;
                display: inline-block;
            }

            .expand-toggle:hover {
                text-decoration: underline;
            }

            /* Status indicators */
            .status-publish {
                color: #28a745;
                font-weight: 600;
            }

            .status-draft {
                color: #ffc107;
                font-weight: 600;
            }

            .status-private {
                color: #dc3545;
                font-weight: 600;
            }

            .status-pending {
                color: #17a2b8;
                font-weight: 600;
            }

            /* Post type indicators */
            .post-type-post {
                background: #007cba;
                color: white;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 9px;
                font-weight: 600;
            }

            .post-type-page {
                background: #28a745;
                color: white;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 9px;
                font-weight: 600;
            }

            /* Responsive design */
            @media (max-width: 1200px) {
                .beamraymar-top-controls {
                    flex-direction: column;
                    align-items: stretch;
                }
                
                .beamraymar-controls-right {
                    align-items: flex-start;
                }
            }

            @media (max-width: 768px) {
                .beamraymar-wrapper {
                    font-size: 10px;
                }
                
                .beamraymar-table th,
                .beamraymar-table td {
                    padding: 4px 6px;
                    font-size: 9px;
                }
                
                .beamraymar-create-buttons {
                    flex-direction: column;
                    gap: 5px;
                }
            }

            /* Table header improvements */
            .row_obtain_db_table {
                background: #404040;
                color: #ffffff;
                font-weight: 700;
                text-align: center;
            }

            .row_obtain_db_column {
                background: #2c2c2c;
                font-weight: 600;
            }

            /* Additional styling for better UX */
            .beamraymar-table tbody td {
                transition: background-color 0.2s ease;
            }

            .tcell_inner_wrapper_div strong {
                color: #ffffff;
            }

            /* Scrollbar styling for webkit browsers */
            .beamraymar-table-scroll::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            .beamraymar-table-scroll::-webkit-scrollbar-track {
                background: #1e1e1e;
            }

            .beamraymar-table-scroll::-webkit-scrollbar-thumb {
                background: #555;
                border-radius: 4px;
            }

            .beamraymar-table-scroll::-webkit-scrollbar-thumb:hover {
                background: #777;
            }
        </style>

        <h1>🚀 Lumora BeamRay Table</h1>

        <!-- Top Controls -->
        <div class="beamraymar-top-controls">
            <div class="beamraymar-controls-left">
                <div class="beamray_banner1">
                    <svg class="beamray-logo" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L4 7v10l8 5 8-5V7l-8-5zm0 2.18L18.18 8 12 11.82 5.82 8 12 4.18zM6 9.42l5 2.5v8.16l-5-2.5V9.42zm12 0v8.16l-5 2.5v-8.16l5-2.5z"/>
                        <circle cx="12" cy="8" r="1.5" fill="white"/>
                        <line x1="12" y1="8" x2="12" y2="16" stroke="white" stroke-width="1" opacity="0.6"/>
                        <line x1="8" y1="10" x2="16" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                        <line x1="16" y1="10" x2="8" y2="14" stroke="white" stroke-width="0.5" opacity="0.4"/>
                    </svg>
                    <strong>Lumora BeamRay Control</strong>
                </div>
                <div class="beamraymar-info-text">
                    <span id="beamraymar-results-info">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
            </div>

            <div class="beamraymar-controls-right">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <?php
                    global $wpdb;
                    $sitespren_row = $wpdb->get_row("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
                    ?>
                    <div style="font-size: 16px; font-weight: bold; text-transform: lowercase; margin-bottom: 10px;">
                        <span style="font-weight: bold;"><?php echo esc_html($wpdb->prefix); ?></span>zen_sitespren.sitespren_base: <?php echo esc_html($sitespren_base ?: ''); ?>
                    </div>
                    <div class="beamraymar-create-buttons">
                        <button class="beamraymar-create-btn beamraymar-create-inline-post" id="create-post-inline">Create New (Inline)</button>
                        <button class="beamraymar-create-btn beamraymar-create-inline-page" id="create-page-inline">Create New (Inline) WP Page</button>
                        <button class="beamraymar-create-btn beamraymar-create-popup" id="create-popup">Create New (Popup)</button>
                        <button class="beamraymar-create-btn beamraymar-duplicate-btn" id="duplicate-selected">Duplicate</button>
                    </div>
                </div>
                
                <div class="beamraymar-nubra-kite">nubra-tableface-kite</div>
                
                <!-- Filter Controls -->
                <div class="beamraymar-filter-controls">
                    <div class="beamraymar-filter-bar" id="beamraymar-post-type-filter">
                        <a href="#" class="beamraymar-filter-btn" data-filter-type="post_type" data-filter-value="page">Page</a>
                        <a href="#" class="beamraymar-filter-btn" data-filter-type="post_type" data-filter-value="post">Post</a>
                    </div>
                    
                    <div class="beamraymar-filter-bar" id="beamraymar-post-status-filter">
                        <?php $current_status = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : 'all'; ?>
                        <a href="<?php echo admin_url('admin.php?page=lum_beamray_mar&post_status=all'); ?>" class="beamraymar-filter-btn <?php echo ($current_status === 'all') ? 'active' : ''; ?>" data-filter-type="post_status" data-filter-value="all">All</a>
                        <a href="<?php echo admin_url('admin.php?page=lum_beamray_mar&post_status=publish'); ?>" class="beamraymar-filter-btn <?php echo ($current_status === 'publish') ? 'active' : ''; ?>" data-filter-type="post_status" data-filter-value="publish">Published</a>
                        <a href="<?php echo admin_url('admin.php?page=lum_beamray_mar&post_status=draft'); ?>" class="beamraymar-filter-btn <?php echo ($current_status === 'draft') ? 'active' : ''; ?>" data-filter-type="post_status" data-filter-value="draft">Draft</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rocket Chamber -->
        <div class="rocket-chamber">
            <div class="rocket-chamber-header">
                <span class="rocket-icon">🚀</span>
                <span class="rocket-label">rocket_chamber</span>
            </div>
            <div class="rocket-chamber-content">
                <table class="rocket-control-table">
                    <tr>
                        <td class="control-label">row pagination</td>
                        <td class="control-content">
                            <!-- Move top row pagination controls here -->
                            <div class="beamraymar-pagination-controls">
                                <div class="beamraymar-pagination-bar" id="beamraymar-per-page-bar">
                                    <a href="#" class="beamraymar-pagination-btn" data-per-page="10">10</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-per-page="20">20</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-per-page="50">50</a>
                                    <a href="#" class="beamraymar-pagination-btn active" data-per-page="100">100</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-per-page="200">200</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-per-page="500">500</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-per-page="all">All</a>
                                </div>
                                
                                <div class="beamraymar-pagination-divider"></div>
                                
                                <div class="beamraymar-pagination-bar" id="beamraymar-page-bar">
                                    <a href="#" class="beamraymar-pagination-btn" data-page="first">First</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-page="prev">Prev</a>
                                    <a href="#" class="beamraymar-pagination-btn active" data-page="1">1</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-page="next">Next</a>
                                    <a href="#" class="beamraymar-pagination-btn" data-page="last">Last</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="control-label">search box</td>
                        <td class="control-content">
                            <!-- Move search box here -->
                            <div class="beamraymar-search-container">
                                <input type="text" id="beamraymar-search" class="beamraymar-search-input" placeholder="Search all fields...">
                                <button class="beamraymar-clear-btn" id="beamraymar-clear">CL</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="control-label">wolf exclusion band</td>
                        <td class="control-content">
                            <!-- Add wolf exclusion band buttons -->
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <button class="beamraymar-wolf-btn" data-wolf-action="enable">Enable Wolf Exclusion</button>
                                <button class="beamraymar-wolf-btn" data-wolf-action="disable">Disable Wolf Exclusion</button>
                                <button class="beamraymar-wolf-btn" data-wolf-action="status">Check Wolf Status</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="control-label">column templates</td>
                        <td class="control-content">
                            <!-- Add column template buttons -->
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <button class="beamraymar-template-btn" data-template="default">Default Template</button>
                                <button class="beamraymar-template-btn" data-template="compact">Compact View</button>
                                <button class="beamraymar-template-btn" data-template="detailed">Detailed View</button>
                                <button class="beamraymar-template-btn" data-template="custom">Custom Template</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="control-label">column pagination</td>
                        <td class="control-content">
                            <!-- Move column pagination controls here -->
                            <div class="beamraymar-column-pagination-controls">
                                <div class="beamraymar-column-pagination-bar" id="beamraymar-column-group-bar">
                                    <a href="#" class="beamraymar-column-pagination-btn active" data-column-group="1">Columns 1-7</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-group="2">Columns 8-14</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-group="3">Columns 15-21</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-group="4">Columns 22-28</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-group="5">Columns 29-35</a>
                                </div>
                                
                                <div class="beamraymar-column-pagination-bar" id="beamraymar-column-nav-bar">
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="first">First</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="prev">Prev</a>
                                    <a href="#" class="beamraymar-column-pagination-btn active" data-column-nav="1">1</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="2">2</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="3">3</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="4">4</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="5">5</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="next">Next</a>
                                    <a href="#" class="beamraymar-column-pagination-btn" data-column-nav="last">Last</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Table Container -->
        <div class="beamraymar-table-container">
            <div class="beamraymar-table-scroll">
                <table class="beamraymar-table utg_beamray" id="beamraymar-table">
                    <thead>
                        <tr>
                            <th class="column_checkbox row_obtain_db_table"><div class="tcell_inner_wrapper_div"></div></th>
                            <th class="column_tool_buttons row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong>misc-uicol-type</strong></div></th>
                            <th class="column_wp_posts_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_status row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_title row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_name row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_replex_submit row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong>non db ui column</strong></div></th>
                            <th class="column_wp_posts_post_content row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_postmeta_meta_key_elementor_data row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>postmeta</strong></div></th>
                            <th class="column_wp_posts_post_type row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_date row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_modified row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_author row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_post_parent row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_menu_order row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_comment_status row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_wp_posts_ping_status row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>posts</strong></div></th>
                            <th class="column_zen_orbitposts_rel_wp_post_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_orbitpost_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_redshift_datum row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_rover_datum row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_hudson_imgplanbatch_id row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_pinned row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_flagged row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_starred row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_is_squared row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_created_at row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                            <th class="column_zen_orbitposts_updated_at row_obtain_db_table"><div class="tcell_inner_wrapper_div"><strong><?php echo esc_html($wpdb->prefix); ?>zen_orbitposts</strong></div></th>
                        </tr>
                        <tr class="beamraymar-main-header-row">
                            <th class="beamraymar-checkbox-cell column_checkbox row_obtain_db_column">
                                <div class="tcell_inner_wrapper_div"><input type="checkbox" class="beamraymar-checkbox" id="select-all"></div>
                            </th>
                            <th class="column_tool_buttons row_obtain_db_column"><div class="tcell_inner_wrapper_div">tool_buttons</div></th>
                            <th data-field="ID" data-type="integer" class="column_wp_posts_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">id</div></th>
                            <th data-field="post_status" data-type="text" class="column_wp_posts_post_status row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_status</div></th>
                            <th data-field="post_title" data-type="text" class="column_wp_posts_post_title row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_title</div></th>
                            <th data-field="post_name" data-type="text" class="column_wp_posts_post_name row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_name</div></th>
                            <th class="column_replex_submit row_obtain_db_column"><div class="tcell_inner_wrapper_div">replex_submit</div></th>
                            <th data-field="post_content" data-type="longtext" class="column_wp_posts_post_content row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_content</div></th>
                            <th data-field="_elementor_data" data-type="text" class="column_wp_postmeta_meta_key_elementor_data row_obtain_db_column"><div class="tcell_inner_wrapper_div"><strong>meta_key:_elementor_data</strong></div></th>
                            <th data-field="post_type" data-type="text" class="column_wp_posts_post_type row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_type</div></th>
                            <th data-field="post_date" data-type="datetime" class="column_wp_posts_post_date row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_date</div></th>
                            <th data-field="post_modified" data-type="datetime" class="column_wp_posts_post_modified row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_modified</div></th>
                            <th data-field="post_author" data-type="integer" class="column_wp_posts_post_author row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_author</div></th>
                            <th data-field="post_parent" data-type="integer" class="column_wp_posts_post_parent row_obtain_db_column"><div class="tcell_inner_wrapper_div">post_parent</div></th>
                            <th data-field="menu_order" data-type="integer" class="column_wp_posts_menu_order row_obtain_db_column"><div class="tcell_inner_wrapper_div">menu_order</div></th>
                            <th data-field="comment_status" data-type="text" class="column_wp_posts_comment_status row_obtain_db_column"><div class="tcell_inner_wrapper_div">comment_status</div></th>
                            <th data-field="ping_status" data-type="text" class="column_wp_posts_ping_status row_obtain_db_column"><div class="tcell_inner_wrapper_div">ping_status</div></th>
                            <th data-field="rel_wp_post_id" data-type="integer" class="column_zen_orbitposts_rel_wp_post_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">rel_wp_post_id</div></th>
                            <th data-field="orbitpost_id" data-type="integer" class="column_zen_orbitposts_orbitpost_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">orbitpost_id</div></th>
                            <th data-field="redshift_datum" data-type="text" class="column_zen_orbitposts_redshift_datum row_obtain_db_column"><div class="tcell_inner_wrapper_div">redshift_datum</div></th>
                            <th data-field="rover_datum" data-type="json" class="column_zen_orbitposts_rover_datum row_obtain_db_column"><div class="tcell_inner_wrapper_div">rover_datum</div></th>
                            <th data-field="hudson_imgplanbatch_id" data-type="varchar" class="column_zen_orbitposts_hudson_imgplanbatch_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">hudson_imgplanbatch_id</div></th>
                            <th data-field="is_pinned" data-type="boolean" class="column_zen_orbitposts_is_pinned row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_pinned</div></th>
                            <th data-field="is_flagged" data-type="boolean" class="column_zen_orbitposts_is_flagged row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_flagged</div></th>
                            <th data-field="is_starred" data-type="boolean" class="column_zen_orbitposts_is_starred row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_starred</div></th>
                            <th data-field="is_squared" data-type="boolean" class="column_zen_orbitposts_is_squared row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_squared</div></th>
                            <th data-field="created_at" data-type="timestamp" class="column_zen_orbitposts_created_at row_obtain_db_column"><div class="tcell_inner_wrapper_div">created_at</div></th>
                            <th data-field="updated_at" data-type="timestamp" class="column_zen_orbitposts_updated_at row_obtain_db_column"><div class="tcell_inner_wrapper_div">updated_at</div></th>
                        </tr>
                    </thead>
                    <tbody id="beamraymar-table-body">
                        <?php
                        $count = 1;
                        foreach ($posts_pages as $post_page) {
                            $author_name = get_the_author_meta('display_name', $post_page->post_author);
                            
                            echo '<tr class="beamraymar-row" data-post-id="' . esc_attr($post_page->ID) . '">';
                            
                            // Checkbox column
                            echo '<td class="column_checkbox row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            echo '<input type="checkbox" class="beamraymar-checkbox" value="' . esc_attr($post_page->ID) . '">';
                            echo '</div>';
                            echo '</td>';
                            
                            // Tool buttons column
                            echo '<td class="column_tool_buttons row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            echo '<button class="beamraymar-tool-btn edit-btn" data-post-id="' . esc_attr($post_page->ID) . '">Edit</button>';
                            echo '<button class="beamraymar-tool-btn" data-post-id="' . esc_attr($post_page->ID) . '">View</button>';
                            echo '<button class="beamraymar-tool-btn delete-btn" data-post-id="' . esc_attr($post_page->ID) . '">Del</button>';
                            echo '</div>';
                            echo '</td>';
                            
                            // ID column
                            echo '<td class="column_wp_posts_id row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->ID) . '</div>';
                            echo '</td>';
                            
                            // Post status column
                            echo '<td class="column_wp_posts_post_status row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            echo '<span class="status-' . esc_attr($post_page->post_status) . '">' . esc_html(ucfirst($post_page->post_status)) . '</span>';
                            echo '</div>';
                            echo '</td>';
                            
                            // Post title column
                            echo '<td class="column_wp_posts_post_title row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->post_title ?: '(no title)') . '</div>';
                            echo '</td>';
                            
                            // Post name (slug) column
                            echo '<td class="column_wp_posts_post_name row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->post_name ?: '') . '</div>';
                            echo '</td>';
                            
                            // Replex submit column (non-DB UI column)
                            echo '<td class="column_replex_submit row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            echo '<button class="beamraymar-tool-btn" data-post-id="' . esc_attr($post_page->ID) . '">Submit</button>';
                            echo '</div>';
                            echo '</td>';
                            
                            // Post content column
                            echo '<td class="column_wp_posts_post_content row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            $content_preview = wp_trim_words(strip_tags($post_page->post_content), 10, '...');
                            echo '<div class="content-preview">' . esc_html($content_preview) . '</div>';
                            if (strlen($post_page->post_content) > 100) {
                                echo '<span class="expand-toggle" data-post-id="' . esc_attr($post_page->ID) . '">Expand</span>';
                            }
                            echo '</div>';
                            echo '</td>';
                            
                            // Elementor data column
                            echo '<td class="column_wp_postmeta_meta_key_elementor_data row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            if (!empty($post_page->elementor_data)) {
                                $elementor_preview = wp_trim_words($post_page->elementor_data, 5, '...');
                                echo esc_html($elementor_preview);
                            } else {
                                echo '<em>No Elementor data</em>';
                            }
                            echo '</div>';
                            echo '</td>';
                            
                            // Post type column
                            echo '<td class="column_wp_posts_post_type row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">';
                            echo '<span class="post-type-' . esc_attr($post_page->post_type) . '">' . esc_html(ucfirst($post_page->post_type)) . '</span>';
                            echo '</div>';
                            echo '</td>';
                            
                            // Post date column
                            echo '<td class="column_wp_posts_post_date row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->post_date) . '</div>';
                            echo '</td>';
                            
                            // Post modified column
                            echo '<td class="column_wp_posts_post_modified row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->post_modified) . '</div>';
                            echo '</td>';
                            
                            // Post author column
                            echo '<td class="column_wp_posts_post_author row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($author_name ?: $post_page->post_author) . '</div>';
                            echo '</td>';
                            
                            // Post parent column
                            echo '<td class="column_wp_posts_post_parent row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->post_parent ?: '0') . '</div>';
                            echo '</td>';
                            
                            // Menu order column
                            echo '<td class="column_wp_posts_menu_order row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->menu_order ?: '0') . '</div>';
                            echo '</td>';
                            
                            // Comment status column
                            echo '<td class="column_wp_posts_comment_status row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->comment_status ?: 'closed') . '</div>';
                            echo '</td>';
                            
                            // Ping status column
                            echo '<td class="column_wp_posts_ping_status row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->ping_status ?: 'closed') . '</div>';
                            echo '</td>';
                            
                            // Zen OrbitPosts columns
                            echo '<td class="column_zen_orbitposts_rel_wp_post_id row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->ID) . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_orbitpost_id row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->orbitpost_id ?: '') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_redshift_datum row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->redshift_datum ?: '') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_rover_datum row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->rover_datum ?: '') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_hudson_imgplanbatch_id row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->hudson_imgplanbatch_id ?: '') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_is_pinned row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . ($post_page->is_pinned ? 'Yes' : 'No') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_is_flagged row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . ($post_page->is_flagged ? 'Yes' : 'No') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_is_starred row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . ($post_page->is_starred ? 'Yes' : 'No') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_is_squared row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . ($post_page->is_squared ? 'Yes' : 'No') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_created_at row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->zen_created_at ?: '') . '</div>';
                            echo '</td>';
                            
                            echo '<td class="column_zen_orbitposts_updated_at row_obtain_db_column">';
                            echo '<div class="tcell_inner_wrapper_div">' . esc_html($post_page->zen_updated_at ?: '') . '</div>';
                            echo '</td>';
                            
                            echo '</tr>';
                            $count++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script type="text/javascript">
            (function($) {
                'use strict';
                
                var selectedRows = new Set();
                var allPosts = <?php echo wp_json_encode($posts_pages); ?>;
                var filteredPosts = allPosts;
                var currentPage = 1;
                var postsPerPage = 100;
                var editingCell = null;
                
                $(document).ready(function() {
                    initializeLumoraBeamray();
                });
                
                function initializeLumoraBeamray() {
                    bindEventHandlers();
                    renderTable();
                    updatePaginationInfo();
                }
                
                function bindEventHandlers() {
                    // Pagination buttons
                    $(document).on('click', '.beamraymar-pagination-btn', function(e) {
                        e.preventDefault();
                        var $this = $(this);
                        
                        if ($this.data('per-page')) {
                            postsPerPage = $this.data('per-page') === 'all' ? filteredPosts.length : parseInt($this.data('per-page'));
                            currentPage = 1;
                            $this.siblings().removeClass('active');
                            $this.addClass('active');
                        } else if ($this.data('page')) {
                            var action = $this.data('page');
                            var totalPages = Math.ceil(filteredPosts.length / postsPerPage);
                            
                            switch(action) {
                                case 'first':
                                    currentPage = 1;
                                    break;
                                case 'prev':
                                    currentPage = Math.max(1, currentPage - 1);
                                    break;
                                case 'next':
                                    currentPage = Math.min(totalPages, currentPage + 1);
                                    break;
                                case 'last':
                                    currentPage = totalPages;
                                    break;
                                default:
                                    currentPage = parseInt(action);
                            }
                        }
                        
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Search functionality
                    $('#beamraymar-search').on('input', function() {
                        var searchTerm = $(this).val().toLowerCase();
                        
                        if (searchTerm === '') {
                            filteredPosts = allPosts;
                        } else {
                            filteredPosts = allPosts.filter(function(post) {
                                return Object.values(post).some(function(value) {
                                    return value != null && value.toString().toLowerCase().includes(searchTerm);
                                });
                            });
                        }
                        
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Clear search
                    $('#beamraymar-clear').on('click', function() {
                        $('#beamraymar-search').val('');
                        filteredPosts = allPosts;
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Select all checkbox
                    $(document).on('change', '#select-all', function() {
                        var isChecked = $(this).is(':checked');
                        $('.beamraymar-checkbox').not('#select-all').prop('checked', isChecked);
                        updateSelectedRows();
                    });
                    
                    // Individual checkboxes
                    $(document).on('change', '.beamraymar-checkbox', function() {
                        updateSelectedRows();
                        
                        var totalCheckboxes = $('.beamraymar-checkbox').not('#select-all').length;
                        var checkedCheckboxes = $('.beamraymar-checkbox:checked').not('#select-all').length;
                        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
                    });
                    
                    // Tool buttons
                    $(document).on('click', '.beamraymar-tool-btn', function() {
                        var postId = $(this).data('post-id');
                        var $button = $(this);
                        
                        if ($button.hasClass('edit-btn')) {
                            window.open(ajaxurl.replace('admin-ajax.php', 'post.php?post=' + postId + '&action=edit'), '_blank');
                        } else if ($button.hasClass('delete-btn')) {
                            if (confirm('Are you sure you want to delete this post?')) {
                                deletePost(postId);
                            }
                        } else {
                            // View button
                            window.open('/?p=' + postId, '_blank');
                        }
                    });
                    
                    // Content expand/collapse
                    $(document).on('click', '.expand-toggle', function() {
                        var postId = $(this).data('post-id');
                        var $preview = $(this).siblings('.content-preview');
                        
                        if ($(this).text() === 'Expand') {
                            $preview.addClass('expanded');
                            $(this).text('Collapse');
                        } else {
                            $preview.removeClass('expanded');
                            $(this).text('Expand');
                        }
                    });
                    
                    // Filter buttons
                    $(document).on('click', '.beamraymar-filter-btn', function(e) {
                        e.preventDefault();
                        var $this = $(this);
                        var filterType = $this.data('filter-type');
                        var filterValue = $this.data('filter-value');
                        
                        if (filterType === 'post_status') {
                            // For post status, reload the page with the new parameter
                            window.location.href = $this.attr('href');
                        } else if (filterType === 'post_type') {
                            // For post type, filter client-side
                            $this.siblings().removeClass('active');
                            $this.addClass('active');
                            
                            if (filterValue === 'all') {
                                filteredPosts = allPosts;
                            } else {
                                filteredPosts = allPosts.filter(function(post) {
                                    return post.post_type === filterValue;
                                });
                            }
                            
                            currentPage = 1;
                            renderTable();
                            updatePaginationInfo();
                        }
                    });
                    
                    // Create buttons
                    $('#create-post-inline').on('click', function() {
                        window.open(ajaxurl.replace('admin-ajax.php', 'post-new.php'), '_blank');
                    });
                    
                    $('#create-page-inline').on('click', function() {
                        window.open(ajaxurl.replace('admin-ajax.php', 'post-new.php?post_type=page'), '_blank');
                    });
                    
                    $('#create-popup').on('click', function() {
                        alert('Popup creation functionality coming soon!');
                    });
                    
                    $('#duplicate-selected').on('click', function() {
                        var selectedIds = Array.from(selectedRows);
                        if (selectedIds.length === 0) {
                            alert('Please select posts to duplicate.');
                            return;
                        }
                        alert('Duplicate functionality for ' + selectedIds.length + ' posts coming soon!');
                    });
                    
                    // Wolf exclusion buttons
                    $(document).on('click', '.beamraymar-wolf-btn', function() {
                        var action = $(this).data('wolf-action');
                        switch(action) {
                            case 'enable':
                                alert('Wolf exclusion enabled!');
                                break;
                            case 'disable':
                                alert('Wolf exclusion disabled!');
                                break;
                            case 'status':
                                alert('Wolf exclusion status: Disabled');
                                break;
                        }
                    });
                    
                    // Template buttons
                    $(document).on('click', '.beamraymar-template-btn', function() {
                        var template = $(this).data('template');
                        $(this).siblings().removeClass('active');
                        $(this).addClass('active');
                        alert('Switched to ' + template + ' template!');
                    });
                    
                    // Column pagination buttons
                    $(document).on('click', '.beamraymar-column-pagination-btn', function(e) {
                        e.preventDefault();
                        $(this).siblings().removeClass('active');
                        $(this).addClass('active');
                        alert('Column view functionality coming soon!');
                    });
                }
                
                function renderTable() {
                    var startIndex = (currentPage - 1) * postsPerPage;
                    var endIndex = startIndex + postsPerPage;
                    var pageData = filteredPosts.slice(startIndex, endIndex);
                    
                    var tableBody = $('#beamraymar-table-body');
                    tableBody.empty();
                    
                    pageData.forEach(function(post, index) {
                        var row = createTableRow(post, startIndex + index + 1);
                        tableBody.append(row);
                    });
                    
                    updatePaginationButtons();
                }
                
                function createTableRow(post, rowNumber) {
                    var row = $('<tr>').addClass('beamraymar-row').attr('data-post-id', post.ID);
                    
                    // Add all the table cells similar to the PHP version
                    // This is a simplified version - you can expand this based on your needs
                    row.append('<td class="column_checkbox"><input type="checkbox" class="beamraymar-checkbox" value="' + post.ID + '"></td>');
                    row.append('<td class="column_tool_buttons"><button class="beamraymar-tool-btn edit-btn" data-post-id="' + post.ID + '">Edit</button></td>');
                    row.append('<td class="column_wp_posts_id">' + post.ID + '</td>');
                    row.append('<td class="column_wp_posts_post_status"><span class="status-' + post.post_status + '">' + post.post_status + '</span></td>');
                    row.append('<td class="column_wp_posts_post_title">' + (post.post_title || '(no title)') + '</td>');
                    
                    return row;
                }
                
                function updatePaginationInfo() {
                    var totalPosts = filteredPosts.length;
                    var startIndex = (currentPage - 1) * postsPerPage + 1;
                    var endIndex = Math.min(startIndex + postsPerPage - 1, totalPosts);
                    
                    $('#beamraymar-results-info').text(startIndex + '-' + endIndex + ' of ' + totalPosts + ' posts/pages');
                }
                
                function updatePaginationButtons() {
                    var totalPages = Math.ceil(filteredPosts.length / postsPerPage);
                    
                    $('.beamraymar-pagination-btn[data-page]').removeClass('active');
                    $('.beamraymar-pagination-btn[data-page="' + currentPage + '"]').addClass('active');
                    
                    // Update page buttons dynamically
                    var pageBar = $('#beamraymar-page-bar');
                    pageBar.find('.beamraymar-pagination-btn:not([data-page="first"]):not([data-page="prev"]):not([data-page="next"]):not([data-page="last"])').remove();
                    
                    var startPage = Math.max(1, currentPage - 2);
                    var endPage = Math.min(totalPages, startPage + 4);
                    
                    var nextBtn = pageBar.find('[data-page="next"]');
                    for (var i = startPage; i <= endPage; i++) {
                        var pageBtn = $('<a href="#" class="beamraymar-pagination-btn" data-page="' + i + '">' + i + '</a>');
                        if (i === currentPage) {
                            pageBtn.addClass('active');
                        }
                        nextBtn.before(pageBtn);
                    }
                }
                
                function deletePost(postId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'delete_post',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce("delete_post"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error deleting post: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('Error deleting post. Please try again.');
                        }
                    });
                }
                
                function saveCell(cell, newValue) {
                    var $cell = $(cell);
                    var postId = $cell.closest('tr').data('post-id');
                    var field = $cell.closest('th').data('field') || $cell.attr('data-field');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'update_post_field',
                            post_id: postId,
                            field: field,
                            value: newValue,
                            nonce: '<?php echo wp_create_nonce("update_post_field"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $cell.find('.tcell_inner_wrapper_div').text(newValue);
                            } else {
                                alert('Error saving: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('Error saving. Please try again.');
                        }
                    });
                    
                    editingCell = null;
                }
                
                function updateSelectedRows() {
                    selectedRows.clear();
                    $('.beamraymar-checkbox:checked').not('#select-all').each(function() {
                        selectedRows.add($(this).val());
                    });
                }
                
            })(jQuery);
        </script>
        
    </div>
    <?php
}

/**
 * Helper function to suppress admin notices
 */
function lumora_suppress_all_admin_notices() {
    // Remove all admin notices for a clean beamray interface
    remove_all_actions('admin_notices');
    remove_all_actions('network_admin_notices');
    remove_all_actions('all_admin_notices');
    
    // Additional notice suppression
    add_action('admin_notices', function() { 
        echo '<style>.notice, .error, .updated, .update-nag { display: none !important; }</style>'; 
    }, 1);
}
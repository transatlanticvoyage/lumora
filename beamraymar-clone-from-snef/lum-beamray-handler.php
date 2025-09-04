<?php
/**
 * Lumora BeamRay Handler - Exact clone of Snefuruplin beamraymar page
 * Independent beamraymar page for Lumora plugin
 * URL: =lum_beamray_mar
 */

if (!defined('ABSPATH')) {
    exit;
}

// Make sure we can access WordPress functions and globals
global $wpdb;

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
    
    // Handle AJAX requests - they are now handled by the main admin class
    // if (isset($_POST['action']) && in_array($_POST['action'], ['create_new_post', 'update_post_field', 'create', 'edit'])) {
    //     lumora_handle_beamray_ajax();
    //     return;
    // }
    
    // Get posts and pages data
    $posts_pages = lumora_get_posts_and_pages_data();
    
    ?>
    <div class="wrap beamraymar-wrapper">
        <style>
            /* Beamraymar Custom Styles - Exact copy from Snefuruplin */
            .beamraymar-wrapper {
                background: white;
                padding: 0;
                margin: 0 0 0 -20px;
            }
            
            .beamraymar-top-controls {
                background-color: white;
                border-bottom: 1px solid #ddd;
                padding: 12px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 16px;
            }
            
            .beamraymar-controls-left {
                display: flex;
                align-items: center;
                gap: 24px;
            }
            
            .beamray_banner1 {
                background: black;
                color: white;
                font-size: 18px;
                font-weight: bold;
                padding: 8px 12px;
                border: 1px solid gray;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                white-space: nowrap;
            }
            
            .beamray-logo {
                width: 20px;
                height: 20px;
                display: inline-block;
            }
            
            .beamraymar-controls-right {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .beamraymar-info-text {
                font-size: 14px;
                color: #666;
            }
            
            .beamraymar-pagination-controls {
                display: flex;
                align-items: center;
            }
            
            .beamraymar-pagination-bar {
                display: flex;
            }
            
            .beamraymar-pagination-btn {
                padding: 10px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                margin-right: -1px;
                text-decoration: none;
                color: #333;
                transition: background-color 0.2s;
            }
            
            .beamraymar-pagination-btn:hover {
                background-color: #f5f5f5;
            }
            
            .beamraymar-pagination-btn.active {
                background-color: #0073aa;
                color: white;
            }
            
            .beamraymar-pagination-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .beamraymar-pagination-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            .beamraymar-pagination-divider {
                width: 1px;
                height: 20px;
                background: #ddd;
                margin: 0 12px;
            }
            
            .beamraymar-search-container {
                position: relative;
            }
            
            .beamraymar-search-input {
                width: 320px;
                padding: 8px 40px 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .beamraymar-clear-btn {
                position: absolute;
                right: 6px;
                top: 50%;
                transform: translateY(-50%);
                background: #ffd700;
                color: black;
                border: none;
                padding: 4px 8px;
                border-radius: 2px;
                font-size: 12px;
                font-weight: bold;
                cursor: pointer;
            }
            
            .beamraymar-clear-btn:hover {
                background: #ffed4e;
            }
            
            .beamraymar-create-buttons {
                display: flex;
                gap: 8px;
            }
            
            .beamraymar-create-btn {
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
            }
            
            .beamraymar-create-inline-post {
                background: #16a085;
                color: white;
            }
            
            .beamraymar-create-inline-post:hover {
                background: #138d75;
            }
            
            .beamraymar-create-inline-page {
                background: #2980b9;
                color: white;
            }
            
            .beamraymar-create-inline-page:hover {
                background: #2471a3;
            }
            
            .beamraymar-create-popup {
                background: #8e44ad;
                color: white;
            }
            
            .beamraymar-create-popup:hover {
                background: #7d3c98;
            }
            
            .beamraymar-nubra-kite {
                background: #f39c12;
                color: black;
                padding: 6px 12px;
                border: 1px solid black;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            
            .beamraymar-table-container {
                background: white;
                overflow: hidden;
            }
            
            .beamraymar-table-scroll {
                overflow-x: auto;
            }
            
            .beamraymar-table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #ddd;
                min-width: 1600px;
            }
            
            .beamraymar-table thead {
                background: #f8f9fa;
            }
            
            .beamraymar-table th,
            .beamraymar-table td {
                border: 1px solid #ddd;
                padding: 8px 12px;
                text-align: left;
                font-size: 14px;
            }
            
            .beamraymar-table th {
                font-weight: bold;
                font-size: 12px;
                text-transform: lowercase;
                cursor: pointer;
                position: relative;
            }
            
            .beamraymar-table th:hover {
                background: #e9ecef;
            }
            
            .beamraymar-table tbody tr:hover {
                background: #f8f9fa;
            }
            
            .beamraymar-checkbox-cell {
                width: 40px;
                text-align: center;
                cursor: pointer;
            }
            
            .beamraymar-checkbox {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
            
            .beamraymar-editable-cell {
                cursor: pointer;
                min-height: 20px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            .beamraymar-editable-cell:hover {
                background: #f0f8ff;
                outline: 1px solid #cce7ff;
            }
            
            .beamraymar-editing-input {
                width: 100%;
                padding: 4px 6px;
                border: 2px solid #0073aa;
                border-radius: 3px;
                font-size: 14px;
                background: white;
            }
            
            .beamraymar-editing-textarea {
                width: 100%;
                padding: 4px 6px;
                border: 2px solid #0073aa;
                border-radius: 3px;
                font-size: 14px;
                background: white;
                resize: none;
                min-height: 60px;
            }
            
            .beamraymar-toggle-switch {
                width: 48px;
                height: 24px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s;
                position: relative;
                display: inline-block;
            }
            
            .beamraymar-toggle-switch.on {
                background: #16a085;
            }
            
            .beamraymar-toggle-switch.off {
                background: #bdc3c7;
            }
            
            .beamraymar-toggle-handle {
                width: 20px;
                height: 20px;
                background: white;
                border-radius: 50%;
                position: absolute;
                top: 2px;
                transition: transform 0.3s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            }
            
            .beamraymar-toggle-switch.on .beamraymar-toggle-handle {
                transform: translateX(24px);
            }
            
            .beamraymar-toggle-switch.off .beamraymar-toggle-handle {
                transform: translateX(2px);
            }
            
            .beamraymar-sort-indicator {
                margin-left: 8px;
                color: #666;
            }
            
            .beamraymar-loading {
                text-align: center;
                padding: 40px;
                font-size: 16px;
                color: #666;
            }
            
            /* Bottom controls */
            .beamraymar-bottom-controls {
                background-color: white;
                border-top: 1px solid #ddd;
                padding: 12px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            /* Modal styles */
            .beamraymar-modal {
                display: none;
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
            }
            
            .beamraymar-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .beamraymar-modal-content {
                background: white;
                padding: 30px;
                border-radius: 6px;
                max-width: 800px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .beamraymar-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 15px;
            }
            
            .beamraymar-modal-title {
                font-size: 20px;
                font-weight: bold;
                margin: 0;
            }
            
            .beamraymar-modal-close {
                font-size: 24px;
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
            }
            
            .beamraymar-modal-close:hover {
                color: #333;
            }
            
            .beamraymar-form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .beamraymar-form-field {
                display: flex;
                flex-direction: column;
            }
            
            .beamraymar-form-field.full-width {
                grid-column: 1 / -1;
            }
            
            .beamraymar-form-label {
                font-weight: 600;
                margin-bottom: 6px;
                color: #333;
            }
            
            .beamraymar-form-input,
            .beamraymar-form-textarea,
            .beamraymar-form-select {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .beamraymar-form-textarea {
                min-height: 100px;
                resize: vertical;
            }
            
            .beamraymar-modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                margin-top: 24px;
            }
            
            .beamraymar-modal-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .beamraymar-modal-btn.primary {
                background: #0073aa;
                color: white;
            }
            
            .beamraymar-modal-btn.primary:hover {
                background: #005a87;
            }
            
            .beamraymar-modal-btn.secondary {
                background: #f1f1f1;
                color: #333;
                border: 1px solid #ddd;
            }
            
            .beamraymar-modal-btn.secondary:hover {
                background: #e0e0e0;
            }
            
            .beamraymar-table td .tcell_inner_wrapper_div {
                height: 38px;
            }
            
            .column_tool_buttons {
                min-width: 230px;
                white-space: nowrap;
            }
            
            .column_tool_buttons .tcell_inner_wrapper_div {
                display: flex;
                align-items: center;
                gap: 2px;
                height: auto;
            }
            
            .beamraymar-table td.column_wp_posts_post_title {
                font-size: 18px;
                font-weight: bold;
            }
            
            .beamraymar-table td.column_wp_posts_post_status[data-status="publish"] {
                background-color: #efddbb;
            }
            
            /* Column pagination styles */
            .beamraymar-column-pagination-controls {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-left: 15px;
            }
            
            .beamraymar-column-pagination-bar {
                display: flex;
            }
            
            .beamraymar-column-pagination-btn {
                padding: 10px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                margin-right: -1px;
                text-decoration: none;
                color: #333;
                transition: background-color 0.2s;
            }
            
            .beamraymar-column-pagination-btn:hover {
                background-color: #f5f5f5;
            }
            
            .beamraymar-column-pagination-btn.active {
                background-color: #ffd700;
                color: black;
            }
            
            .beamraymar-column-pagination-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .beamraymar-column-pagination-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            /* Filter button bars */
            .beamraymar-filter-controls {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                margin-left: 15px;
            }
            
            .beamraymar-filter-bar {
                display: flex;
            }
            
            .beamraymar-filter-btn {
                padding: 10px 12px;
                font-size: 14px;
                border: 1px solid #ddd;
                background: white;
                cursor: pointer;
                margin-right: -1px;
                text-decoration: none;
                color: #333;
                transition: background-color 0.2s;
            }
            
            .beamraymar-filter-btn:hover {
                background-color: #f5f5f5;
            }
            
            .beamraymar-filter-btn.active {
                background: navy !important;
                color: white !important;
                border-color: navy !important;
            }
            
            /* Rocket Chamber Styles */
            .rocket-chamber {
                background: linear-gradient(135deg, #1e3c72, #2a5298);
                border: 2px solid #4a5568;
                border-radius: 8px;
                margin: 0 0 0 0;
                padding: 0;
                position: relative;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            .rocket-chamber-header {
                background: linear-gradient(90deg, #2d3748, #4a5568);
                padding: 8px 15px;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
                border-bottom: 1px solid #718096;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .rocket-icon {
                font-size: 16px;
                color: #ffd700;
            }
            
            .rocket-label {
                font-size: 13px;
                font-weight: bold;
                color: #e2e8f0;
                text-transform: lowercase;
            }
            
            .rocket-chamber-content {
                padding: 15px;
                background: white;
                border-bottom-left-radius: 6px;
                border-bottom-right-radius: 6px;
            }
            
            .rocket-control-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
            }
            
            .rocket-control-table td {
                padding: 8px 12px;
                border: 1px solid #e2e8f0;
                vertical-align: top;
                font-size: 12px;
            }
            
            .rocket-control-table .control-label {
                background: #f7fafc;
                font-weight: bold;
                color: #4a5568;
                width: 120px;
                text-align: right;
            }
            
            .rocket-control-table .control-content {
                background: white;
            }
            
            /* Wolf exclusion and template buttons */
            .beamraymar-wolf-btn,
            .beamraymar-template-btn {
                padding: 4px 8px;
                font-size: 12px;
                border: 1px solid #d1d5db;
                background: #f9fafb;
                color: #374151;
                cursor: pointer;
                border-radius: 4px;
                transition: background-color 0.2s;
            }
            
            .beamraymar-wolf-btn:hover,
            .beamraymar-template-btn:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
            }
            
            .beamraymar-filter-btn:first-child {
                border-top-left-radius: 4px;
                border-bottom-left-radius: 4px;
            }
            
            .beamraymar-filter-btn:last-child {
                border-top-right-radius: 4px;
                border-bottom-right-radius: 4px;
            }
            
            /* ED button for post_content */
            .beamraymar-content-edit-btn {
                width: 20px;
                height: 20px;
                background: #0073aa;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 10px;
                font-weight: bold;
                float: right;
                margin-left: 8px;
            }
            
            .beamraymar-content-edit-btn:hover {
                background: #005a87;
            }
            
            /* Post content editor popup */
            .beamraymar-content-editor-modal {
                display: none;
                position: fixed;
                z-index: 999999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
            }
            
            .beamraymar-content-editor-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .beamraymar-content-editor-content {
                background: white;
                padding: 30px;
                border-radius: 6px;
                width: 90%;
                height: 85%;
                display: flex;
                flex-direction: column;
            }
            
            .beamraymar-content-editor-header {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 15px;
            }
            
            .beamraymar-content-editor-textarea {
                width: 100%;
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                font-family: monospace;
                font-size: 14px;
                resize: none;
            }
            
            .beamraymar-content-editor-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 15px;
            }
            
            .beamraymar-content-editor-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 600;
            }
            
            .beamraymar-content-editor-btn.save {
                background: #0073aa;
                color: white;
            }
            
            .beamraymar-content-editor-btn.cancel {
                background: #f1f1f1;
                color: #333;
            }
            
            /* Tool button styling */
            .beamraymar-tool-btn {
                width: 36px;
                height: 36px;
                background: #0073aa;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 10px;
                font-weight: bold;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                border-radius: 2px;
            }
            
            .beamraymar-tool-btn:hover {
                background: #005a87;
                color: white;
                text-decoration: none;
            }
            
            /* Pendulum button styling */
            .beamraymar-pendulum-btn {
                width: 36px;
                height: 36px;
                background: #0073aa;
                color: white;
                border: none;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                cursor: pointer;
                margin-right: 2px;
                border-radius: 2px;
            }
            
            .beamraymar-pendulum-btn:hover {
                background: #005a87;
                color: white;
                text-decoration: none;
            }
            
            /* Elementor button styling */
            .beamraymar-elementor-btn {
                width: 36px;
                height: 36px;
                background: #800020;
                color: white;
                border: none;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                cursor: pointer;
                margin-right: 2px;
                border-radius: 2px;
            }
            
            .beamraymar-elementor-btn:hover {
                background: #5c0016;
                color: white;
                text-decoration: none;
            }
            
            .beamraymar-elementor-btn.disabled {
                background: #ccc;
                color: #999;
                cursor: not-allowed;
                pointer-events: none;
            }
            
            /* C1, C2, C3 button styling */
            .beamraymar-c-btn {
                width: 36px;
                height: 36px;
                background: #f0f0f0;
                color: #333;
                border: 1px solid #999;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                cursor: pointer;
                margin-right: 2px;
                border-radius: 2px;
            }
            
            /* Icon button styling */
            .beamray-icon-btn {
                display: inline-block;
                font-size: 16px;
                cursor: pointer;
                padding: 2px;
                opacity: 0.3;
                transition: all 0.2s ease;
                user-select: none;
            }
            
            .beamray-icon-btn:hover {
                opacity: 0.7;
                transform: scale(1.1);
            }
            
            .beamray-icon-btn.active {
                opacity: 1.0;
            }
            
            .beamray-icon-btn.active:hover {
                opacity: 1.0;
                transform: scale(1.2);
            }
        </style>
        
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
                    BeamRay Table
                </div>
                <div class="beamraymar-info-text">
                    <span id="beamraymar-results-info">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
            </div>
            
            <div class="beamraymar-controls-right">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <?php
                    global $wpdb;
                    $sitespren_base = $wpdb->get_var("SELECT sitespren_base FROM {$wpdb->prefix}zen_sitespren WHERE wppma_id = 1");
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
                            <th data-field="rover_datum" data-type="text" class="column_zen_orbitposts_rover_datum row_obtain_db_column"><div class="tcell_inner_wrapper_div">rover_datum</div></th>
                            <th data-field="hudson_imgplanbatch_id" data-type="integer" class="column_zen_orbitposts_hudson_imgplanbatch_id row_obtain_db_column"><div class="tcell_inner_wrapper_div">hudson_imgplanbatch_id</div></th>
                            <th data-field="is_pinned" data-type="boolean" class="column_zen_orbitposts_is_pinned row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_pinned</div></th>
                            <th data-field="is_flagged" data-type="boolean" class="column_zen_orbitposts_is_flagged row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_flagged</div></th>
                            <th data-field="is_starred" data-type="boolean" class="column_zen_orbitposts_is_starred row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_starred</div></th>
                            <th data-field="is_squared" data-type="boolean" class="column_zen_orbitposts_is_squared row_obtain_db_column"><div class="tcell_inner_wrapper_div">is_squared</div></th>
                            <th data-field="created_at" data-type="datetime" class="column_zen_orbitposts_created_at row_obtain_db_column"><div class="tcell_inner_wrapper_div">created_at</div></th>
                            <th data-field="updated_at" data-type="datetime" class="column_zen_orbitposts_updated_at row_obtain_db_column"><div class="tcell_inner_wrapper_div">updated_at</div></th>
                        </tr>
                    </thead>
                    <tbody id="beamraymar-tbody">
                        <?php lumora_render_beamray_table_rows($posts_pages); ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Bottom Controls -->
        <div class="beamraymar-bottom-controls">
            <div class="beamraymar-controls-left">
                <div class="beamraymar-info-text">
                    <span id="beamraymar-results-info-bottom">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="beamraymar-pagination-controls">
                    <div class="beamraymar-pagination-bar" id="beamraymar-per-page-bar-bottom">
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="beamraymar-pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="beamraymar-pagination-divider"></div>
                    
                    <div class="beamraymar-pagination-bar" id="beamraymar-page-bar-bottom">
                        <a href="#" class="beamraymar-pagination-btn" data-page="first">First</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="beamraymar-pagination-btn active" data-page="1">1</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="next">Next</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="beamraymar-search-container">
                    <input type="text" id="beamraymar-search-bottom" class="beamraymar-search-input" placeholder="Search all fields...">
                    <button class="beamraymar-clear-btn" id="beamraymar-clear-bottom">CL</button>
                </div>
            </div>
            <div></div>
        </div>
        
        <!-- Modals -->
        <!-- Create/Edit Modal -->
        <div id="beamraymar-modal" class="beamraymar-modal">
            <div class="beamraymar-modal-content">
                <div class="beamraymar-modal-header">
                    <h2 class="beamraymar-modal-title" id="beamraymar-modal-title">Create New Post/Page</h2>
                    <button class="beamraymar-modal-close" id="beamraymar-modal-close">&times;</button>
                </div>
                
                <form id="beamraymar-modal-form">
                    <input type="hidden" id="modal-post-id" name="post_id">
                    <input type="hidden" id="modal-action" name="action" value="create">
                    <?php wp_nonce_field('lumora_beamray_nonce', 'nonce'); ?>
                    
                    <div class="beamraymar-form-grid">
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Post Type</label>
                            <select id="modal-post-type" name="post_type" class="beamraymar-form-select">
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                            </select>
                        </div>
                        
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Status</label>
                            <select id="modal-post-status" name="post_status" class="beamraymar-form-select">
                                <option value="draft">Draft</option>
                                <option value="publish">Published</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        
                        <div class="beamraymar-form-field full-width">
                            <label class="beamraymar-form-label">Title</label>
                            <input type="text" id="modal-post-title" name="post_title" class="beamraymar-form-input">
                        </div>
                        
                        <div class="beamraymar-form-field full-width">
                            <label class="beamraymar-form-label">Content</label>
                            <textarea id="modal-post-content" name="post_content" class="beamraymar-form-textarea"></textarea>
                        </div>
                        
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Slug</label>
                            <input type="text" id="modal-post-name" name="post_name" class="beamraymar-form-input">
                        </div>
                        
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Parent ID</label>
                            <input type="number" id="modal-post-parent" name="post_parent" class="beamraymar-form-input" value="0">
                        </div>
                    </div>
                    
                    <div class="beamraymar-modal-actions">
                        <button type="button" class="beamraymar-modal-btn secondary" id="beamraymar-modal-cancel">Cancel</button>
                        <button type="submit" class="beamraymar-modal-btn primary" id="beamraymar-modal-save">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Post Content Editor Modal -->
        <div id="beamraymar-content-editor-modal" class="beamraymar-content-editor-modal">
            <div class="beamraymar-content-editor-content">
                <div class="beamraymar-content-editor-header">post_content</div>
                <textarea id="beamraymar-content-editor-textarea" class="beamraymar-content-editor-textarea"></textarea>
                <div class="beamraymar-content-editor-actions">
                    <button type="button" class="beamraymar-content-editor-btn cancel" id="beamraymar-content-editor-cancel">Cancel</button>
                    <button type="button" class="beamraymar-content-editor-btn save" id="beamraymar-content-editor-save">Save</button>
                </div>
            </div>
        </div>

        <!-- Elementor Data Editor Modal -->
        <div id="beamraymar-elementor-editor-modal" class="beamraymar-content-editor-modal">
            <div class="beamraymar-content-editor-content">
                <div class="beamraymar-content-editor-header">_elementor_data</div>
                <textarea id="beamraymar-elementor-editor-textarea" class="beamraymar-content-editor-textarea"></textarea>
                <div class="beamraymar-content-editor-actions">
                    <button type="button" class="beamraymar-content-editor-btn cancel" id="beamraymar-elementor-editor-cancel">Cancel</button>
                    <button type="button" class="beamraymar-content-editor-btn save" id="beamraymar-elementor-editor-save">Save</button>
                </div>
            </div>
        </div>
        
        <script>
            // Lumora BeamRay JavaScript functionality - Exact clone with adapted endpoints
            (function($) {
                'use strict';
                
                let currentPage = 1;
                let itemsPerPage = 100;
                let currentSearch = '';
                let allData = <?php echo json_encode($posts_pages); ?>;
                let filteredData = [...allData];
                let selectedRows = new Set();
                let editingCell = null;
                let currentContentEditPostId = null;
                let currentElementorEditPostId = null;
                
                // Initialize page
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
                        const $this = $(this);
                        
                        // Handle per-page buttons
                        if ($this.data('per-page')) {
                            itemsPerPage = $this.data('per-page') === 'all' ? filteredData.length : parseInt($this.data('per-page'));
                            currentPage = 1;
                            $('.beamraymar-pagination-btn[data-per-page]').removeClass('active');
                            $this.addClass('active');
                            renderTable();
                            updatePaginationInfo();
                        }
                        
                        // Handle page navigation
                        if ($this.data('page')) {
                            const pageAction = $this.data('page');
                            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                            
                            switch(pageAction) {
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
                                    currentPage = parseInt(pageAction);
                            }
                            renderTable();
                            updatePaginationInfo();
                        }
                    });
                    
                    // Search functionality
                    $(document).on('input', '.beamraymar-search-input', function() {
                        currentSearch = $(this).val().toLowerCase();
                        $('#beamraymar-search, #beamraymar-search-bottom').val($(this).val());
                        filterData();
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Clear search buttons
                    $(document).on('click', '.beamraymar-clear-btn', function(e) {
                        e.preventDefault();
                        $('.beamraymar-search-input').val('');
                        currentSearch = '';
                        filterData();
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Create buttons
                    $('#create-post-inline').on('click', function() {
                        openCreateModal('post');
                    });
                    
                    $('#create-page-inline').on('click', function() {
                        openCreateModal('page');
                    });
                    
                    $('#create-popup').on('click', function() {
                        openCreateModal('post');
                    });
                    
                    // Modal handlers
                    $('#beamraymar-modal-close, #beamraymar-modal-cancel').on('click', function() {
                        closeCreateModal();
                    });
                    
                    $(document).on('click', function(e) {
                        if ($(e.target).hasClass('beamraymar-modal')) {
                            closeCreateModal();
                        }
                    });
                    
                    // Form submission
                    $('#beamraymar-modal-form').on('submit', function(e) {
                        e.preventDefault();
                        submitForm();
                    });
                    
                    // Content editor buttons
                    $(document).on('click', '.beamraymar-content-edit-btn', function() {
                        const postId = $(this).data('post-id');
                        openContentEditor(postId);
                    });
                    
                    // Content editor modal handlers
                    $('#beamraymar-content-editor-cancel').on('click', function() {
                        closeContentEditor();
                    });
                    
                    $('#beamraymar-content-editor-save').on('click', function() {
                        saveContentEdit();
                    });
                    
                    // Elementor buttons
                    $(document).on('click', '.beamraymar-elementor-btn:not(.disabled)', function() {
                        const postId = $(this).data('post-id');
                        openElementorEditor(postId);
                    });
                    
                    // Elementor editor modal handlers
                    $('#beamraymar-elementor-editor-cancel').on('click', function() {
                        closeElementorEditor();
                    });
                    
                    $('#beamraymar-elementor-editor-save').on('click', function() {
                        saveElementorEdit();
                    });
                    
                    // Inline cell editing
                    $(document).on('click', '.beamraymar-editable-cell', function() {
                        if (editingCell) return;
                        startInlineEdit(this);
                    });
                    
                    // Checkbox selection
                    $('#select-all').on('change', function() {
                        const isChecked = $(this).is(':checked');
                        $('.beamraymar-checkbox').not('#select-all').prop('checked', isChecked);
                        updateSelectedRows();
                    });
                    
                    $(document).on('change', '.beamraymar-checkbox:not(#select-all)', function() {
                        updateSelectedRows();
                    });
                }
                
                function filterData() {
                    if (!currentSearch) {
                        filteredData = [...allData];
                        return;
                    }
                    
                    filteredData = allData.filter(item => {
                        return Object.values(item).some(value => 
                            String(value).toLowerCase().includes(currentSearch)
                        );
                    });
                }
                
                function renderTable() {
                    // Table already rendered server-side, just update info display
                    const start = (currentPage - 1) * itemsPerPage + 1;
                    const end = Math.min(currentPage * itemsPerPage, filteredData.length);
                    
                    $('#beamraymar-results-info, #beamraymar-results-info-bottom')
                        .text(`${start}-${end} of ${filteredData.length} posts/pages`);
                }
                
                function updatePaginationInfo() {
                    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                    
                    // Update pagination display
                    $('.beamraymar-pagination-btn[data-page]').removeClass('active');
                    $(`.beamraymar-pagination-btn[data-page="${currentPage}"]`).addClass('active');
                }
                
                function openCreateModal(postType = 'post') {
                    $('#modal-post-type').val(postType);
                    $('#beamraymar-modal-title').text(`Create New ${postType.charAt(0).toUpperCase() + postType.slice(1)}`);
                    $('#beamraymar-modal').addClass('active');
                }
                
                function closeCreateModal() {
                    $('#beamraymar-modal').removeClass('active');
                    $('#beamraymar-modal-form')[0].reset();
                }
                
                function submitForm() {
                    const formData = new FormData($('#beamraymar-modal-form')[0]);
                    formData.append('action', 'lumora_beamray_create_new_post');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                closeCreateModal();
                                location.reload(); // Refresh to show new post
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('AJAX request failed');
                        }
                    });
                }
                
                function openContentEditor(postId) {
                    currentContentEditPostId = postId;
                    const post = allData.find(p => p.ID == postId);
                    if (post) {
                        $('#beamraymar-content-editor-textarea').val(post.post_content || '');
                        $('#beamraymar-content-editor-modal').addClass('active');
                    }
                }
                
                function closeContentEditor() {
                    $('#beamraymar-content-editor-modal').removeClass('active');
                    currentContentEditPostId = null;
                }
                
                function saveContentEdit() {
                    if (!currentContentEditPostId) return;
                    
                    const content = $('#beamraymar-content-editor-textarea').val();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lumora_beamray_update_post_field',
                            post_id: currentContentEditPostId,
                            field: 'post_content',
                            value: content,
                            nonce: $('[name="nonce"]').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                closeContentEditor();
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('AJAX request failed');
                        }
                    });
                }
                
                function openElementorEditor(postId) {
                    currentElementorEditPostId = postId;
                    const post = allData.find(p => p.ID == postId);
                    if (post) {
                        $('#beamraymar-elementor-editor-textarea').val(post._elementor_data || '');
                        $('#beamraymar-elementor-editor-modal').addClass('active');
                    }
                }
                
                function closeElementorEditor() {
                    $('#beamraymar-elementor-editor-modal').removeClass('active');
                    currentElementorEditPostId = null;
                }
                
                function saveElementorEdit() {
                    if (!currentElementorEditPostId) return;
                    
                    const elementorData = $('#beamraymar-elementor-editor-textarea').val();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lumora_beamray_update_meta_field',
                            post_id: currentElementorEditPostId,
                            meta_key: '_elementor_data',
                            meta_value: elementorData,
                            nonce: $('[name="nonce"]').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                closeElementorEditor();
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('AJAX request failed');
                        }
                    });
                }
                
                function startInlineEdit(cell) {
                    const $cell = $(cell);
                    const field = $cell.data('field');
                    const type = $cell.data('type');
                    const currentValue = $cell.find('.tcell_inner_wrapper_div').text();
                    
                    if (type === 'longtext') {
                        const $textarea = $('<textarea class="beamraymar-editing-textarea"></textarea>');
                        $textarea.val(currentValue);
                        $cell.find('.tcell_inner_wrapper_div').html($textarea);
                        $textarea.focus();
                        editingCell = { cell: $cell, field: field, original: currentValue };
                        
                        $textarea.on('blur keydown', function(e) {
                            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Escape')) {
                                finishInlineEdit(false);
                            } else if (e.type === 'keydown' && e.ctrlKey && e.key === 'Enter') {
                                finishInlineEdit(true);
                            }
                        });
                    } else {
                        const $input = $('<input type="text" class="beamraymar-editing-input">');
                        $input.val(currentValue);
                        $cell.find('.tcell_inner_wrapper_div').html($input);
                        $input.focus().select();
                        editingCell = { cell: $cell, field: field, original: currentValue };
                        
                        $input.on('blur keydown', function(e) {
                            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Escape')) {
                                finishInlineEdit(false);
                            } else if (e.type === 'keydown' && e.key === 'Enter') {
                                finishInlineEdit(true);
                            }
                        });
                    }
                }
                
                function finishInlineEdit(save) {
                    if (!editingCell) return;
                    
                    const $cell = editingCell.cell;
                    const field = editingCell.field;
                    const original = editingCell.original;
                    const $input = $cell.find('input, textarea');
                    const newValue = $input.val();
                    
                    if (save && newValue !== original) {
                        const postId = $cell.closest('tr').data('post-id');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'lumora_beamray_update_post_field',
                                post_id: postId,
                                field: field,
                                value: newValue,
                                nonce: $('[name="nonce"]').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    $cell.find('.tcell_inner_wrapper_div').text(newValue);
                                } else {
                                    $cell.find('.tcell_inner_wrapper_div').text(original);
                                    alert('Error: ' + response.data);
                                }
                            },
                            error: function() {
                                $cell.find('.tcell_inner_wrapper_div').text(original);
                                alert('AJAX request failed');
                            }
                        });
                    } else {
                        $cell.find('.tcell_inner_wrapper_div').text(original);
                    }
                    
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
 * Helper functions - exact copies from Snefuruplin
 */

/**
 * Helper function to suppress admin notices
 */
function lumora_suppress_all_admin_notices() {
    // Remove all admin notices immediately
    add_action('admin_print_styles', function() {
        // Remove all notice actions
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('network_admin_notices');
        
        // Remove user admin notices
        global $wp_filter;
        if (isset($wp_filter['user_admin_notices'])) {
            unset($wp_filter['user_admin_notices']);
        }
    }, 0);
    
    // Additional cleanup for persistent notices
    add_action('admin_head', function() {
        // Hide any notices that slip through via CSS
        echo '<style type="text/css">
            .notice, .notice-warning, .notice-error, .notice-success, .notice-info,
            .updated, .error, .update-nag, .admin-notice,
            .wrap > .notice, .wrap > .error, .wrap > .updated {
                display: none !important;
            }
        </style>';
    });
}

/**
 * Helper function to handle AJAX requests
 */
function lumora_handle_beamray_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lumora_beamray_nonce')) {
        echo json_encode(array('success' => false, 'message' => 'Security check failed'));
        wp_die();
    }

    $action = sanitize_text_field($_POST['action']);

    switch ($action) {
        case 'create_new_post':
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
                echo json_encode(array('success' => true, 'message' => 'Post created successfully', 'post_id' => $post_id));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to create post'));
            }
            break;

        case 'update_post_field':
            $post_id = intval($_POST['post_id']);
            $field = sanitize_text_field($_POST['field']);
            $value = sanitize_textarea_field($_POST['value']);

            $update_data = array(
                'ID' => $post_id,
                $field => $value
            );

            $result = wp_update_post($update_data);
            if ($result && !is_wp_error($result)) {
                echo json_encode(array('success' => true, 'message' => 'Field updated successfully'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to update field'));
            }
            break;

        case 'update_meta_field':
            $post_id = intval($_POST['post_id']);
            $meta_key = sanitize_text_field($_POST['meta_key']);
            $meta_value = sanitize_textarea_field($_POST['meta_value']);

            $result = update_post_meta($post_id, $meta_key, $meta_value);
            if ($result !== false) {
                echo json_encode(array('success' => true, 'message' => 'Meta field updated successfully'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to update meta field'));
            }
            break;

        default:
            echo json_encode(array('success' => false, 'message' => 'Invalid action'));
    }
    
    wp_die();
}

/**
 * Helper function to get posts and pages data
 */
function lumora_get_posts_and_pages_data() {
    global $wpdb;
    
    // Force table creation first (debug)
    $lumora_db = new Lumora_Database();
    $lumora_db->create_zen_tables();
    
    // Get post_status parameter from URL
    $post_status = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : 'all';
    
    // Build WHERE clause based on post_status parameter
    $where_clause = "p.post_type IN ('post', 'page')";
    if ($post_status !== 'all') {
        $where_clause .= $wpdb->prepare(" AND p.post_status = %s", $post_status);
    }
    
    // Start with a very simple query to test basic functionality
    $results = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_content, p.post_type, p.post_status, p.post_name, 
                p.post_date, p.post_modified, p.post_author, p.post_parent, p.menu_order,
                p.comment_status, p.ping_status
         FROM {$wpdb->prefix}posts p
         WHERE {$where_clause}
         ORDER BY p.post_modified DESC",
        ARRAY_A
    );
    
    // Add debugging info
    error_log('Lumora BeamRay: Retrieved ' . count($results) . ' posts/pages from basic query (post_status: ' . $post_status . ')');
    if ($wpdb->last_error) {
        error_log('Lumora BeamRay SQL Error: ' . $wpdb->last_error);
    }
    
    // Add the missing fields with NULL values for now
    foreach ($results as &$result) {
        $result['_elementor_data'] = null;
        $result['rel_wp_post_id'] = null;
        $result['orbitpost_id'] = null;
        $result['redshift_datum'] = null;
        $result['rover_datum'] = null;
        $result['hudson_imgplanbatch_id'] = null;
        $result['is_pinned'] = null;
        $result['is_flagged'] = null;
        $result['is_starred'] = null;
        $result['is_squared'] = null;
        $result['created_at'] = null;
        $result['updated_at'] = null;
    }
    
    return $results ?: array();
}

/**
 * Helper function to render beamray table rows
 */
function lumora_render_beamray_table_rows($posts_pages) {
    if (empty($posts_pages)) {
        echo '<tr><td colspan="28" class="beamraymar-loading">No posts or pages found.</td></tr>';
        return;
    }

    $count = 0;
    foreach ($posts_pages as $item) {
        if ($count >= 100) break; // Limit initial display
        
        $post_id = $item['ID'];
        $content_preview = wp_trim_words(strip_tags($item['post_content'] ?: ''), 10, '...');
        
        echo '<tr data-post-id="' . esc_attr($post_id) . '">';
        
        // Checkbox
        echo '<td class="beamraymar-checkbox-cell column_checkbox"><div class="tcell_inner_wrapper_div">';
        echo '<input type="checkbox" class="beamraymar-checkbox" value="' . esc_attr($post_id) . '">';
        echo '</div></td>';
        
        // Tool buttons
        echo '<td class="readonly-cell column_tool_buttons"><div class="tcell_inner_wrapper_div">';
        echo '<a href="' . get_edit_post_link($post_id) . '" class="beamraymar-tool-btn" title="Edit">ED</a>';
        echo '<a href="' . get_permalink($post_id) . '" class="beamraymar-pendulum-btn" title="View">VW</a>';
        if ($item['_elementor_data']) {
            echo '<button class="beamraymar-elementor-btn" data-post-id="' . esc_attr($post_id) . '" title="Elementor">EL</button>';
        } else {
            echo '<button class="beamraymar-elementor-btn disabled" title="No Elementor Data">EL</button>';
        }
        echo '<button class="beamraymar-c-btn" title="C1">C1</button>';
        echo '<button class="beamraymar-c-btn" title="C2">C2</button>';
        echo '<button class="beamraymar-c-btn" title="C3">C3</button>';
        echo '</div></td>';
        
        // ID
        echo '<td class="readonly-cell column_wp_posts_id"><div class="tcell_inner_wrapper_div">' . esc_html($post_id) . '</div></td>';
        
        // Status  
        echo '<td class="beamraymar-editable-cell column_wp_posts_post_status" data-field="post_status" data-type="text" data-status="' . esc_attr($item['post_status']) . '">';
        echo '<div class="tcell_inner_wrapper_div">' . esc_html($item['post_status']) . '</div></td>';
        
        // Title
        echo '<td class="beamraymar-editable-cell column_wp_posts_post_title" data-field="post_title" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_title']) . '</div></td>';
        
        // Name/Slug
        echo '<td class="beamraymar-editable-cell column_wp_posts_post_name" data-field="post_name" data-type="text"><div class="tcell_inner_wrapper_div">' . esc_html($item['post_name']) . '</div></td>';
        
        // Replex Submit (empty UI column)
        echo '<td class="readonly-cell column_replex_submit"><div class="tcell_inner_wrapper_div"></div></td>';
        
        // Content with ED button
        echo '<td class="readonly-cell column_wp_posts_post_content"><div class="tcell_inner_wrapper_div">' . esc_html($content_preview) . '<button class="beamraymar-content-edit-btn" data-post-id="' . esc_attr($post_id) . '">ED</button></div></td>';
        
        // Add remaining columns with basic data
        $remaining_fields = [
            '_elementor_data', 'post_type', 'post_date', 'post_modified', 'post_author', 
            'post_parent', 'menu_order', 'comment_status', 'ping_status',
            'rel_wp_post_id', 'orbitpost_id', 'redshift_datum', 'rover_datum', 
            'hudson_imgplanbatch_id', 'is_pinned', 'is_flagged', 'is_starred', 
            'is_squared', 'created_at', 'updated_at'
        ];
        
        foreach ($remaining_fields as $field) {
            $value = $item[$field] ?? '';
            if (in_array($field, ['is_pinned', 'is_flagged', 'is_starred', 'is_squared'])) {
                $value = $value ? '1' : '0';
            } elseif ($field === '_elementor_data') {
                $value = $value ? 'Yes' : 'No';
            }
            echo '<td class="readonly-cell"><div class="tcell_inner_wrapper_div">' . esc_html($value) . '</div></td>';
        }
        
        echo '</tr>';
        $count++;
    }
}

    // Continue inside the main lumora_beamray_page() function 
    // This HTML structure was disconnected during our edits
    ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Bottom Controls -->
        <div class="beamraymar-bottom-controls">
            <div class="beamraymar-controls-left">
                <div class="beamraymar-info-text">
                    <span id="beamraymar-results-info-bottom">1-<?php echo min(100, count($posts_pages)); ?> of <?php echo count($posts_pages); ?> posts/pages</span>
                </div>
                
                <div class="beamraymar-pagination-controls">
                    <div class="beamraymar-pagination-bar" id="beamraymar-per-page-bar-bottom">
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="10">10</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="20">20</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="50">50</a>
                        <a href="#" class="beamraymar-pagination-btn active" data-per-page="100">100</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="200">200</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="500">500</a>
                        <a href="#" class="beamraymar-pagination-btn" data-per-page="all">All</a>
                    </div>
                    
                    <div class="beamraymar-pagination-divider"></div>
                    
                    <div class="beamraymar-pagination-bar" id="beamraymar-page-bar-bottom">
                        <a href="#" class="beamraymar-pagination-btn" data-page="first">First</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="prev">Prev</a>
                        <a href="#" class="beamraymar-pagination-btn active" data-page="1">1</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="next">Next</a>
                        <a href="#" class="beamraymar-pagination-btn" data-page="last">Last</a>
                    </div>
                </div>
                
                <div class="beamraymar-search-container">
                    <input type="text" id="beamraymar-search-bottom" class="beamraymar-search-input" placeholder="Search all fields...">
                    <button class="beamraymar-clear-btn" id="beamraymar-clear-bottom">CL</button>
                </div>
            </div>
            <div></div>
        </div>
        
        <!-- Modals -->
        <!-- Create/Edit Modal -->
        <div id="beamraymar-modal" class="beamraymar-modal">
            <div class="beamraymar-modal-content">
                <div class="beamraymar-modal-header">
                    <h2 class="beamraymar-modal-title" id="beamraymar-modal-title">Create New Post/Page</h2>
                    <button class="beamraymar-modal-close" id="beamraymar-modal-close">&times;</button>
                </div>
                
                <form id="beamraymar-modal-form">
                    <input type="hidden" id="modal-post-id" name="post_id">
                    <input type="hidden" id="modal-action" name="action" value="create">
                    <?php wp_nonce_field('lumora_beamray_nonce', 'nonce'); ?>
                    
                    <div class="beamraymar-form-grid">
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Post Type</label>
                            <select id="modal-post-type" name="post_type" class="beamraymar-form-select">
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                            </select>
                        </div>
                        
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Status</label>
                            <select id="modal-post-status" name="post_status" class="beamraymar-form-select">
                                <option value="draft">Draft</option>
                                <option value="publish">Published</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        
                        <div class="beamraymar-form-field full-width">
                            <label class="beamraymar-form-label">Title</label>
                            <input type="text" id="modal-post-title" name="post_title" class="beamraymar-form-input">
                        </div>
                        
                        <div class="beamraymar-form-field full-width">
                            <label class="beamraymar-form-label">Content</label>
                            <textarea id="modal-post-content" name="post_content" class="beamraymar-form-textarea"></textarea>
                        </div>
                        
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Slug</label>
                            <input type="text" id="modal-post-name" name="post_name" class="beamraymar-form-input">
                        </div>
                        
                        <div class="beamraymar-form-field">
                            <label class="beamraymar-form-label">Parent ID</label>
                            <input type="number" id="modal-post-parent" name="post_parent" class="beamraymar-form-input" value="0">
                        </div>
                    </div>
                    
                    <div class="beamraymar-modal-actions">
                        <button type="button" class="beamraymar-modal-btn secondary" id="beamraymar-modal-cancel">Cancel</button>
                        <button type="submit" class="beamraymar-modal-btn primary" id="beamraymar-modal-save">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Post Content Editor Modal -->
        <div id="beamraymar-content-editor-modal" class="beamraymar-content-editor-modal">
            <div class="beamraymar-content-editor-content">
                <div class="beamraymar-content-editor-header">post_content</div>
                <textarea id="beamraymar-content-editor-textarea" class="beamraymar-content-editor-textarea"></textarea>
                <div class="beamraymar-content-editor-actions">
                    <button type="button" class="beamraymar-content-editor-btn cancel" id="beamraymar-content-editor-cancel">Cancel</button>
                    <button type="button" class="beamraymar-content-editor-btn save" id="beamraymar-content-editor-save">Save</button>
                </div>
            </div>
        </div>

        <!-- Elementor Data Editor Modal -->
        <div id="beamraymar-elementor-editor-modal" class="beamraymar-content-editor-modal">
            <div class="beamraymar-content-editor-content">
                <div class="beamraymar-content-editor-header">_elementor_data</div>
                <textarea id="beamraymar-elementor-editor-textarea" class="beamraymar-content-editor-textarea"></textarea>
                <div class="beamraymar-content-editor-actions">
                    <button type="button" class="beamraymar-content-editor-btn cancel" id="beamraymar-elementor-editor-cancel">Cancel</button>
                    <button type="button" class="beamraymar-content-editor-btn save" id="beamraymar-elementor-editor-save">Save</button>
                </div>
            </div>
        </div>
        
        <script>
            // Lumora BeamRay JavaScript functionality - Exact clone with adapted endpoints
            (function($) {
                'use strict';
                
                let currentPage = 1;
                let itemsPerPage = 100;
                let currentSearch = '';
                let allData = <?php echo json_encode($posts_pages); ?>;
                let filteredData = [...allData];
                let selectedRows = new Set();
                let editingCell = null;
                let currentContentEditPostId = null;
                let currentElementorEditPostId = null;
                
                // Initialize page
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
                        const $this = $(this);
                        
                        // Handle per-page buttons
                        if ($this.data('per-page')) {
                            itemsPerPage = $this.data('per-page') === 'all' ? filteredData.length : parseInt($this.data('per-page'));
                            currentPage = 1;
                            $('.beamraymar-pagination-btn[data-per-page]').removeClass('active');
                            $this.addClass('active');
                            renderTable();
                            updatePaginationInfo();
                        }
                        
                        // Handle page navigation
                        if ($this.data('page')) {
                            const pageAction = $this.data('page');
                            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                            
                            switch(pageAction) {
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
                                    currentPage = parseInt(pageAction);
                            }
                            renderTable();
                            updatePaginationInfo();
                        }
                    });
                    
                    // Search functionality
                    $(document).on('input', '.beamraymar-search-input', function() {
                        currentSearch = $(this).val().toLowerCase();
                        $('#beamraymar-search, #beamraymar-search-bottom').val($(this).val());
                        filterData();
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Clear search buttons
                    $(document).on('click', '.beamraymar-clear-btn', function(e) {
                        e.preventDefault();
                        $('.beamraymar-search-input').val('');
                        currentSearch = '';
                        filterData();
                        currentPage = 1;
                        renderTable();
                        updatePaginationInfo();
                    });
                    
                    // Create buttons
                    $('#create-post-inline').on('click', function() {
                        openCreateModal('post');
                    });
                    
                    $('#create-page-inline').on('click', function() {
                        openCreateModal('page');
                    });
                    
                    $('#create-popup').on('click', function() {
                        openCreateModal('post');
                    });
                    
                    // Modal handlers
                    $('#beamraymar-modal-close, #beamraymar-modal-cancel').on('click', function() {
                        closeCreateModal();
                    });
                    
                    $(document).on('click', function(e) {
                        if ($(e.target).hasClass('beamraymar-modal')) {
                            closeCreateModal();
                        }
                    });
                    
                    // Form submission
                    $('#beamraymar-modal-form').on('submit', function(e) {
                        e.preventDefault();
                        submitForm();
                    });
                    
                    // Content editor buttons
                    $(document).on('click', '.beamraymar-content-edit-btn', function() {
                        const postId = $(this).data('post-id');
                        openContentEditor(postId);
                    });
                    
                    // Content editor modal handlers
                    $('#beamraymar-content-editor-cancel').on('click', function() {
                        closeContentEditor();
                    });
                    
                    $('#beamraymar-content-editor-save').on('click', function() {
                        saveContentEdit();
                    });
                    
                    // Elementor buttons
                    $(document).on('click', '.beamraymar-elementor-btn:not(.disabled)', function() {
                        const postId = $(this).data('post-id');
                        openElementorEditor(postId);
                    });
                    
                    // Elementor editor modal handlers
                    $('#beamraymar-elementor-editor-cancel').on('click', function() {
                        closeElementorEditor();
                    });
                    
                    $('#beamraymar-elementor-editor-save').on('click', function() {
                        saveElementorEdit();
                    });
                    
                    // Inline cell editing
                    $(document).on('click', '.beamraymar-editable-cell', function() {
                        if (editingCell) return;
                        startInlineEdit(this);
                    });
                    
                    // Checkbox selection
                    $('#select-all').on('change', function() {
                        const isChecked = $(this).is(':checked');
                        $('.beamraymar-checkbox').not('#select-all').prop('checked', isChecked);
                        updateSelectedRows();
                    });
                    
                    $(document).on('change', '.beamraymar-checkbox:not(#select-all)', function() {
                        updateSelectedRows();
                    });
                }
                
                function filterData() {
                    if (!currentSearch) {
                        filteredData = [...allData];
                        return;
                    }
                    
                    filteredData = allData.filter(item => {
                        return Object.values(item).some(value => 
                            String(value).toLowerCase().includes(currentSearch)
                        );
                    });
                }
                
                function renderTable() {
                    // Table already rendered server-side, just update info display
                    const start = (currentPage - 1) * itemsPerPage + 1;
                    const end = Math.min(currentPage * itemsPerPage, filteredData.length);
                    
                    $('#beamraymar-results-info, #beamraymar-results-info-bottom')
                        .text(`${start}-${end} of ${filteredData.length} posts/pages`);
                }
                
                function updatePaginationInfo() {
                    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                    
                    // Update pagination display
                    $('.beamraymar-pagination-btn[data-page]').removeClass('active');
                    $(`.beamraymar-pagination-btn[data-page="${currentPage}"]`).addClass('active');
                }
                
                function openCreateModal(postType = 'post') {
                    $('#modal-post-type').val(postType);
                    $('#beamraymar-modal-title').text(`Create New ${postType.charAt(0).toUpperCase() + postType.slice(1)}`);
                    $('#beamraymar-modal').addClass('active');
                }
                
                function closeCreateModal() {
                    $('#beamraymar-modal').removeClass('active');
                    $('#beamraymar-modal-form')[0].reset();
                }
                
                function submitForm() {
                    const formData = new FormData($('#beamraymar-modal-form')[0]);
                    formData.append('action', 'lumora_beamray_create_new_post');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                closeCreateModal();
                                location.reload(); // Refresh to show new post
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('AJAX request failed');
                        }
                    });
                }
                
                function openContentEditor(postId) {
                    currentContentEditPostId = postId;
                    const post = allData.find(p => p.ID == postId);
                    if (post) {
                        $('#beamraymar-content-editor-textarea').val(post.post_content || '');
                        $('#beamraymar-content-editor-modal').addClass('active');
                    }
                }
                
                function closeContentEditor() {
                    $('#beamraymar-content-editor-modal').removeClass('active');
                    currentContentEditPostId = null;
                }
                
                function saveContentEdit() {
                    if (!currentContentEditPostId) return;
                    
                    const content = $('#beamraymar-content-editor-textarea').val();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lumora_beamray_update_post_field',
                            post_id: currentContentEditPostId,
                            field: 'post_content',
                            value: content,
                            nonce: $('[name="nonce"]').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                closeContentEditor();
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('AJAX request failed');
                        }
                    });
                }
                
                function openElementorEditor(postId) {
                    currentElementorEditPostId = postId;
                    const post = allData.find(p => p.ID == postId);
                    if (post) {
                        $('#beamraymar-elementor-editor-textarea').val(post._elementor_data || '');
                        $('#beamraymar-elementor-editor-modal').addClass('active');
                    }
                }
                
                function closeElementorEditor() {
                    $('#beamraymar-elementor-editor-modal').removeClass('active');
                    currentElementorEditPostId = null;
                }
                
                function saveElementorEdit() {
                    if (!currentElementorEditPostId) return;
                    
                    const elementorData = $('#beamraymar-elementor-editor-textarea').val();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lumora_beamray_update_meta_field',
                            post_id: currentElementorEditPostId,
                            meta_key: '_elementor_data',
                            meta_value: elementorData,
                            nonce: $('[name="nonce"]').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                closeElementorEditor();
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('AJAX request failed');
                        }
                    });
                }
                
                function startInlineEdit(cell) {
                    const $cell = $(cell);
                    const field = $cell.data('field');
                    const type = $cell.data('type');
                    const currentValue = $cell.find('.tcell_inner_wrapper_div').text();
                    
                    if (type === 'longtext') {
                        const $textarea = $('<textarea class="beamraymar-editing-textarea"></textarea>');
                        $textarea.val(currentValue);
                        $cell.find('.tcell_inner_wrapper_div').html($textarea);
                        $textarea.focus();
                        editingCell = { cell: $cell, field: field, original: currentValue };
                        
                        $textarea.on('blur keydown', function(e) {
                            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Escape')) {
                                finishInlineEdit(false);
                            } else if (e.type === 'keydown' && e.ctrlKey && e.key === 'Enter') {
                                finishInlineEdit(true);
                            }
                        });
                    } else {
                        const $input = $('<input type="text" class="beamraymar-editing-input">');
                        $input.val(currentValue);
                        $cell.find('.tcell_inner_wrapper_div').html($input);
                        $input.focus().select();
                        editingCell = { cell: $cell, field: field, original: currentValue };
                        
                        $input.on('blur keydown', function(e) {
                            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Escape')) {
                                finishInlineEdit(false);
                            } else if (e.type === 'keydown' && e.key === 'Enter') {
                                finishInlineEdit(true);
                            }
                        });
                    }
                }
                
                function finishInlineEdit(save) {
                    if (!editingCell) return;
                    
                    const $cell = editingCell.cell;
                    const field = editingCell.field;
                    const original = editingCell.original;
                    const $input = $cell.find('input, textarea');
                    const newValue = $input.val();
                    
                    if (save && newValue !== original) {
                        const postId = $cell.closest('tr').data('post-id');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'lumora_beamray_update_post_field',
                                post_id: postId,
                                field: field,
                                value: newValue,
                                nonce: $('[name="nonce"]').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    $cell.find('.tcell_inner_wrapper_div').text(newValue);
                                } else {
                                    $cell.find('.tcell_inner_wrapper_div').text(original);
                                    alert('Error: ' + response.data);
                                }
                            },
                            error: function() {
                                $cell.find('.tcell_inner_wrapper_div').text(original);
                                alert('AJAX request failed');
                            }
                        });
                    } else {
                        $cell.find('.tcell_inner_wrapper_div').text(original);
                    }
                    
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

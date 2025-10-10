<?php
/**
 * Debug script for Lumora rename functionality
 * Visit: yoursite.local/wp-content/plugins/lumora/debug-rename.php
 */

// WordPress bootstrap
require_once '../../../wp-config.php';

if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>Lumora Rename Debug</h1>';

// Test AJAX endpoint availability
echo '<h2>1. Testing AJAX Endpoints</h2>';
if (has_action('wp_ajax_lumora_rename_media_file')) {
    echo '✅ lumora_rename_media_file action is registered<br>';
} else {
    echo '❌ lumora_rename_media_file action is NOT registered<br>';
}

if (has_action('wp_ajax_lumora_get_attachment_details')) {
    echo '✅ lumora_get_attachment_details action is registered<br>';
} else {
    echo '❌ lumora_get_attachment_details action is NOT registered<br>';
}

// Test upload directory
echo '<h2>2. Testing Upload Directory</h2>';
$upload_dir = wp_upload_dir();
echo 'Upload path: ' . $upload_dir['basedir'] . '<br>';
echo 'Upload URL: ' . $upload_dir['baseurl'] . '<br>';
echo 'Directory writable: ' . (is_writable($upload_dir['basedir']) ? '✅ Yes' : '❌ No') . '<br>';

// Find test images
echo '<h2>3. Available Images for Testing</h2>';
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => 10,
    'post_status' => 'inherit'
));

if ($images) {
    echo '<table border="1" style="border-collapse: collapse;">';
    echo '<tr><th>ID</th><th>Title</th><th>Filename</th><th>URL</th><th>File Exists</th></tr>';
    foreach ($images as $image) {
        $file_path = get_attached_file($image->ID);
        $file_exists = file_exists($file_path) ? '✅' : '❌';
        $url = wp_get_attachment_url($image->ID);
        $filename = basename($file_path);
        echo "<tr>";
        echo "<td>{$image->ID}</td>";
        echo "<td>{$image->post_title}</td>";
        echo "<td>{$filename}</td>";
        echo "<td><a href='{$url}' target='_blank'>View</a></td>";
        echo "<td>{$file_exists}</td>";
        echo "</tr>";
    }
    echo '</table>';
} else {
    echo 'No images found in media library';
}

// Test JavaScript console output
echo '<h2>4. JavaScript Console Test</h2>';
echo '<p>Open browser console and check for any JavaScript errors when you try to rename.</p>';
echo '<script>console.log("Lumora debug script loaded successfully");</script>';

// Test permissions
echo '<h2>5. Current User Permissions</h2>';
echo 'Can upload files: ' . (current_user_can('upload_files') ? '✅ Yes' : '❌ No') . '<br>';
echo 'Can manage options: ' . (current_user_can('manage_options') ? '✅ Yes' : '❌ No') . '<br>';
echo 'User ID: ' . get_current_user_id() . '<br>';

// Test nonce generation
echo '<h2>6. Nonce Test</h2>';
$nonce = wp_create_nonce('lumora_rename_nonce');
echo 'Generated nonce: ' . $nonce . '<br>';
echo 'Nonce verify: ' . (wp_verify_nonce($nonce, 'lumora_rename_nonce') ? '✅ Valid' : '❌ Invalid') . '<br>';

echo '<h2>7. AJAX Test</h2>';
$nonce = wp_create_nonce('lumora_rename_nonce');
echo '<button id="test-ajax" data-nonce="' . $nonce . '">Test AJAX Connection</button>';
echo '<div id="ajax-result" style="margin-top: 10px; padding: 10px; border: 1px solid #ccc; display: none;"></div>';

echo '<script src="' . includes_url('js/jquery/jquery.js') . '"></script>';
echo '<script>
jQuery(document).ready(function($) {
    $("#test-ajax").click(function() {
        var nonce = $(this).data("nonce");
        
        $("#ajax-result").show().html("Testing AJAX...");
        
        $.ajax({
            url: "' . admin_url('admin-ajax.php') . '",
            type: "POST",
            data: {
                action: "lumora_test_rename",
                nonce: nonce,
                attachment_id: 1,
                new_filename: "test"
            },
            success: function(response) {
                $("#ajax-result").html("✅ AJAX Success: " + JSON.stringify(response));
            },
            error: function(xhr, status, error) {
                $("#ajax-result").html("❌ AJAX Error: " + status + " - " + error);
            }
        });
    });
});
</script>';

echo '<h2>8. Testing Instructions</h2>';
echo '<ol>';
echo '<li><strong>FIRST:</strong> Click the "Test AJAX Connection" button above</li>';
echo '<li>If AJAX test fails, the problem is basic connectivity</li>';
echo '<li>If AJAX test works, go to <a href="/wp-admin/upload.php" target="_blank">Media Library</a></li>';
echo '<li>Look for the "Lumora Features" column</li>';
echo '<li>Click "Rename File" on any image</li>';
echo '<li>Try renaming with BOTH checkboxes UNCHECKED first</li>';
echo '<li>Check the error log at: wp-content/debug.log (if WP_DEBUG_LOG is enabled)</li>';
echo '<li>Check browser console for JavaScript errors</li>';
echo '</ol>';

echo '<p><strong>Enable WordPress debug logging by adding this to wp-config.php:</strong></p>';
echo '<pre>define("WP_DEBUG", true);
define("WP_DEBUG_LOG", true);
define("WP_DEBUG_DISPLAY", false);</pre>';
?>
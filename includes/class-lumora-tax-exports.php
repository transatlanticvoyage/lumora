<?php
// TEST COMMENT - Verifying dual git tracking for Lumora

/**
 * Lumora Tax Exports Class
 * Handles export functionality for sharkintax, walrustax, and other formats
 */
class Lumora_Tax_Exports {
    
    /**
     * Generate sharkintax export format
     */
    public static function generate_sharkintax($data, $fields = null) {
        $output = "";
        
        if (!$fields) {
            $fields = array_keys((array)$data);
        }
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                // Field name with separators, followed by value
                $output .= "--|{$field}|--\n";
                $output .= $data[$field] . "\n";
            }
        }
        
        return $output;
    }
    
    /**
     * Generate walrustax export format
     */
    public static function generate_walrustax($data, $fields = null) {
        $output = "";
        
        if (!$fields) {
            $fields = array_keys((array)$data);
        }
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                // Walrus format: ~fieldname~ value
                $output .= "~{$field}~" . $data[$field] . "\n";
            }
        }
        
        return $output;
    }
    
    /**
     * Generate CSV export
     */
    public static function generate_csv($data, $fields = null) {
        if (!$fields) {
            $fields = array_keys((array)$data);
        }
        
        // CSV Header
        $output = implode(',', array_map(array(self::class, 'escape_csv'), $fields)) . "\n";
        
        // CSV Data row
        $row_data = array();
        foreach ($fields as $field) {
            $row_data[] = isset($data[$field]) ? $data[$field] : '';
        }
        
        $output .= implode(',', array_map(array(self::class, 'escape_csv'), $row_data)) . "\n";
        
        return $output;
    }
    
    /**
     * Generate XLS export (simplified tab-delimited format)
     */
    public static function generate_xls($data, $fields = null) {
        if (!$fields) {
            $fields = array_keys((array)$data);
        }
        
        // XLS Header (tab-delimited)
        $output = implode("\t", $fields) . "\n";
        
        // XLS Data row
        $row_data = array();
        foreach ($fields as $field) {
            $value = isset($data[$field]) ? $data[$field] : '';
            // Replace tabs and newlines in values
            $value = str_replace(array("\t", "\n", "\r"), array(" ", " ", " "), $value);
            $row_data[] = $value;
        }
        
        $output .= implode("\t", $row_data) . "\n";
        
        return $output;
    }
    
    /**
     * Generate SQL export
     */
    public static function generate_sql($data, $fields = null, $table_name = 'zen_sitespren') {
        if (!$fields) {
            $fields = array_keys((array)$data);
        }
        
        // SQL INSERT statement
        $output = "-- Lumora Export SQL\n";
        $output .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        $columns = implode(', ', array_map(array(self::class, 'escape_sql_identifier'), $fields));
        
        $values = array();
        foreach ($fields as $field) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $values[] = self::escape_sql_value($value);
        }
        
        $values_string = implode(', ', $values);
        
        $output .= "INSERT INTO `{$table_name}` ({$columns}) VALUES ({$values_string});\n";
        
        return $output;
    }
    
    /**
     * Escape CSV values
     */
    private static function escape_csv($value) {
        // Convert to string
        $value = (string)$value;
        
        // If value contains comma, quote, or newline, wrap in quotes and escape quotes
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        
        return $value;
    }
    
    /**
     * Escape SQL identifiers (column names)
     */
    private static function escape_sql_identifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Escape SQL values
     */
    private static function escape_sql_value($value) {
        if ($value === null) {
            return 'NULL';
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        // Escape string value
        $value = str_replace(array('\\', "'"), array('\\\\', "''"), $value);
        return "'" . $value . "'";
    }
    
    /**
     * Generate export filename with timestamp and domain
     */
    public static function generate_export_filename($format, $domain = null) {
        if (!$domain) {
            $domain = parse_url(get_site_url(), PHP_URL_HOST);
        }
        
        // Replace dots with underscores in domain
        $domain_clean = str_replace('.', '_', $domain);
        
        // Generate timestamp (MM-DD-YY-TIME format)
        $timestamp = date('m-d-y-His');
        
        // File extension based on format
        $extensions = array(
            'sharkintax' => 'txt',
            'walrustax' => 'txt', 
            'csv' => 'csv',
            'xls' => 'xls',
            'sql' => 'sql'
        );
        
        $ext = isset($extensions[$format]) ? $extensions[$format] : 'txt';
        
        return "lumora_export_{$timestamp}_{$domain_clean}_1.{$ext}";
    }
    
    /**
     * Handle export download
     */
    public static function download_export($format, $data, $fields = null, $filename = null) {
        // Generate content based on format
        switch ($format) {
            case 'sharkintax':
                $content = self::generate_sharkintax($data, $fields);
                $mime_type = 'text/plain';
                break;
                
            case 'walrustax':
                $content = self::generate_walrustax($data, $fields);
                $mime_type = 'text/plain';
                break;
                
            case 'csv':
                $content = self::generate_csv($data, $fields);
                $mime_type = 'text/csv';
                break;
                
            case 'xls':
                $content = self::generate_xls($data, $fields);
                $mime_type = 'application/vnd.ms-excel';
                break;
                
            case 'sql':
                $content = self::generate_sql($data, $fields);
                $mime_type = 'text/plain';
                break;
                
            default:
                wp_die('Invalid export format');
                return;
        }
        
        // Generate filename if not provided
        if (!$filename) {
            $filename = self::generate_export_filename($format);
        }
        
        // Set headers for download
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output content and exit
        echo $content;
        exit;
    }
    
    /**
     * Get all available export formats
     */
    public static function get_export_formats() {
        return array(
            'sharkintax' => 'Sharkintax Format',
            'walrustax' => 'Walrustax Format', 
            'csv' => 'CSV Format',
            'xls' => 'Excel Format',
            'sql' => 'SQL Format'
        );
    }
    
    /**
     * Validate export format
     */
    public static function is_valid_format($format) {
        $formats = self::get_export_formats();
        return array_key_exists($format, $formats);
    }
}
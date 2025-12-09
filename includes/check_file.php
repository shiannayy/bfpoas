<?php
// check_file.php (in includes folder)
include_once "_init.php";

// Security check
$schedule_id = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : 0;
$filepath = isset($_POST['filepath']) ? $_POST['filepath'] : '';

// Validate schedule_id exists in database
if (!$schedule_id || empty($filepath)) {
    echo json_encode(['accessible' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Get document root properly
$doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Convert absolute path to web-accessible URL
$web_url = '';
$real_path = realpath($filepath);

// If we can't get real path, try to construct it
if (!$real_path && $filepath) {
    // Try to handle absolute paths
    if (strpos($filepath, '/') === 0) {
        $real_path = $filepath;
    }
}

// Check if file exists and is readable
$accessible = false;
$file_exists = false;
$is_readable = false;
$file_size = 0;

if ($real_path) {
    $file_exists = file_exists($real_path);
    $is_readable = is_readable($real_path);
    
    if ($file_exists && $is_readable) {
        $file_size = filesize($real_path);
        
        // Only check PDF header if file has reasonable size
        if ($file_size > 100 && $file_size < 10000000) { // Between 100 bytes and 10MB
            $handle = @fopen($real_path, 'rb'); // 'rb' for binary read
            if ($handle) {
                $header = fread($handle, 5);
                fclose($handle);
                if (strpos($header, '%PDF-') === 0) {
                    $accessible = true;
                    
                    // Create web-accessible URL
                    if ($doc_root && strpos($real_path, $doc_root) === 0) {
                        // Convert absolute path to relative web path
                        $relative_path = str_replace($doc_root, '', $real_path);
                        $web_url = $protocol . '://' . $server_name . $relative_path;
                    } else {
                        // Try alternative approach for XAMPP
                        if (strpos($real_path, '/htdocs/') !== false) {
                            $htdocs_pos = strpos($real_path, '/htdocs/');
                            $relative_path = substr($real_path, $htdocs_pos + 7); // +7 to skip '/htdocs/'
                            $web_url = $protocol . '://' . $server_name . '/' . $relative_path;
                        } else if (strpos($real_path, '/www/') !== false) {
                            $www_pos = strpos($real_path, '/www/');
                            $relative_path = substr($real_path, $www_pos + 5); // +5 to skip '/www/'
                            $web_url = $protocol . '://' . $server_name . '/' . $relative_path;
                        }
                    }
                }
            }
        } else if ($file_size > 100) {
            // File exists and is readable but too large or too small for PDF check
            $accessible = true; // Assume accessible if readable
        }
    }
}

// Log for debugging (remove in production)
error_log("Check file - Path: $filepath, Real: $real_path, Exists: $file_exists, Readable: $is_readable, Size: $file_size");

echo json_encode([
    'accessible' => $accessible,
    'file_exists' => $file_exists,
    'is_readable' => $is_readable,
    'file_size' => $file_size,
    'absolute_path' => $real_path,
    'web_url' => $web_url,
    'document_root' => $doc_root,
    'server_name' => $server_name,
    'protocol' => $protocol,
    'checked_at' => date('Y-m-d H:i:s'),
    'filepath_received' => $filepath
]);
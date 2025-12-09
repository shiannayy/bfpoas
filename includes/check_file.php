<?php
// check_file.php (in tester folder)
include_once "../includes/_init.php";

// Security check
$schedule_id = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : 0;
$filepath = isset($_POST['filepath']) ? $_POST['filepath'] : '';

// Validate schedule_id exists in database
if (!$schedule_id) {
    echo json_encode(['accessible' => false, 'error' => 'Invalid schedule ID']);
    exit;
}

// If filepath is absolute, convert to relative for web access
$web_path = $filepath;
$doc_root = '';

// Remove document root to get web-accessible path
if ($doc_root && strpos($filepath, $doc_root) === 0) {
    $web_path = str_replace($doc_root, '', $filepath);
}

// Check file accessibility
$accessible = false;
$real_path = realpath($filepath);
$file_exists = file_exists($real_path);
$is_readable = is_readable($real_path);
$file_size = 0;

if ($file_exists && $is_readable) {
    $file_size = filesize($real_path);
    if ($file_size > 100) {
        // Try to read first few bytes to verify it's a PDF
        $handle = @fopen($real_path, 'r');
        if ($handle) {
            $header = fread($handle, 5);
            fclose($handle);
            if (strpos($header, '%PDF-') === 0) {
                $accessible = true;
            }
        }
    }
}

echo json_encode([
    'accessible' => $accessible,
    'file_exists' => $file_exists,
    'is_readable' => $is_readable,
    'file_size' => $file_size,
    'absolute_path' => $real_path,
    'web_path' => $web_path,
    'document_root' => $doc_root,
    'checked_at' => date('Y-m-d H:i:s')
]);
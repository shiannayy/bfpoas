<?php
// serve_pdf.php
include_once "_init.php";

// Security: only allow access from same domain
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Credentials: true');

// Get parameters
$filepath = isset($_GET['file']) ? urldecode($_GET['file']) : '';
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

// Security validation
if (empty($filepath) && !$schedule_id) {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid request');
}

// If schedule_id is provided, find the latest PDF
if ($schedule_id > 0) {
    $temp_dir = dirname(__FILE__) . '/../temp_pdfs/';
    $pattern = $temp_dir . "Inspection_Order_" . $schedule_id . "_*.pdf";
    $files = glob($pattern);
    
    if (!empty($files)) {
        // Get the most recent file
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $filepath = $files[0];
    }
}

// Security: Ensure file is within allowed directories
$allowed_dirs = [
    realpath(dirname(__FILE__) . '/../temp_pdfs/'),
    realpath(dirname(__FILE__) . '/../assets/'),
    realpath(dirname(__FILE__) . '/../uploads/')
];

$real_filepath = realpath($filepath);
$allowed = false;

foreach ($allowed_dirs as $allowed_dir) {
    if ($real_filepath && strpos($real_filepath, $allowed_dir) === 0) {
        $allowed = true;
        break;
    }
}

if (!$allowed || !$real_filepath) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Check if file exists and is readable
if (!file_exists($real_filepath) || !is_readable($real_filepath)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

// Check if it's a PDF file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $real_filepath);
finfo_close($finfo);

if ($mime_type !== 'application/pdf') {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid file type');
}

// Serve the file
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($real_filepath) . '"');
header('Content-Length: ' . filesize($real_filepath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($real_filepath);
exit;
?>
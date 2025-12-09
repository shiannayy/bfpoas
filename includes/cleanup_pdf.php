<?php
// cleanup_pdf.php
require_once "../includes/_init.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'message' => 'No filename']);
        exit;
    }
    
    // Sanitize filename to prevent directory traversal
    $filename = basename($filename);
    
    $filepath = dirname(__FILE__) . '/../temp_pdfs/' . $filename;
    
    // Verify file exists in temp_pdfs directory only
    if (file_exists($filepath) && strpos(realpath($filepath), realpath(dirname(__FILE__) . '/../temp_pdfs')) === 0) {
        if (unlink($filepath)) {
            echo json_encode(['success' => true, 'message' => 'File cleaned']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'File not found or invalid path']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
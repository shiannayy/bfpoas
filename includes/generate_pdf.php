<?php
// generate_pdf.php - FOR INSPECTION ORDERS (WITH FILE CACHING)
require_once "../includes/_init.php";
require_once('../vendor/autoload.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$schedule_id = intval($_POST['schedule_id'] ?? 0);
$step = intval($_POST['step'] ?? 0);

if (!$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'No schedule ID provided']);
    exit;
}

// Generate unique filename based on schedule_id and step
$unique_filename = 'Inspection_Order_' . $schedule_id . '_' . $step . '.pdf';
$temp_dir = dirname(__FILE__) . '/../temp_pdfs/';
$filepath = $temp_dir . $unique_filename;

// 1. CHECK IF FILE ALREADY EXISTS AND IS VALID
if (file_exists($filepath)) {
    $file_is_valid = validatePdfFile($filepath);
    
    if ($file_is_valid) {
        // File exists and is valid - return cached version
        echo json_encode([
            'success' => true,
            'filename' => $unique_filename,
            'filepath' => $filepath,
            'filesize' => filesize($filepath),
            'generated_at' => date('Y-m-d H:i:s', filemtime($filepath)),
            'valid_pdf' => true,
            'cached' => true,
            'message' => 'Using cached PDF file'
        ]);
        exit;
    } else {
        // File exists but is invalid - delete it
        @unlink($filepath);
    }
}

// 2. GENERATE NEW PDF FILE
try {
    // Set schedule_id for the included file
    $_GET['id'] = $schedule_id;
    
    // Start output buffering to capture HTML
    ob_start();
    include "../pages/fsed9f_email_attachment_template.php";
    $html_content = ob_get_clean();
    
    // Add CSS styling to the HTML content
    $html_content = addPdfStyling($html_content);
    
    // Convert images to Base64
    $html_content = convertImagesToBase64($html_content, $schedule_id);
    
    // Create PDF using TCPDF
    $pdf = new \TCPDF('P', 'mm', 'Legal', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('BFP Oas System');
    $pdf->SetAuthor('Bureau of Fire Protection');
    $pdf->SetTitle('Inspection Order - ' . $schedule_id);
    $pdf->SetSubject('Fire Safety Inspection Order');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins (left, top, right)
    $pdf->SetMargins(5, 5, 5);
    
    // Add a page
    $pdf->AddPage();
    
    // Write HTML content
    $pdf->writeHTML($html_content, true, false, true, false, '');
    
    // Ensure directory exists
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    // Save PDF file
    $output_result = $pdf->Output($filepath, 'F');
    
    // Verify file was created and is valid
    if ($output_result !== false && file_exists($filepath)) {
        chmod($filepath, 0644); // Make it readable
        
        $is_valid_pdf = validatePdfFile($filepath);
        
        if ($is_valid_pdf) {
            echo json_encode([
                'success' => true,
                'filename' => $unique_filename,
                'filepath' => $filepath,
                'filesize' => filesize($filepath),
                'generated_at' => date('Y-m-d H:i:s'),
                'valid_pdf' => true,
                'cached' => false,
                'message' => 'New PDF generated successfully'
            ]);
        } else {
            // Clean up invalid file
            @unlink($filepath);
            echo json_encode([
                'success' => false,
                'message' => 'Generated file is not a valid PDF'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save PDF file'
        ]);
    }
    
} catch (Exception $e) {
    // Clean up any partially created file
    if (isset($filepath) && file_exists($filepath)) {
        @unlink($filepath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error generating PDF: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Validate if file is a valid PDF
 */
function validatePdfFile($filepath) {
    if (!file_exists($filepath) || filesize($filepath) === 0) {
        return false;
    }
    
    // Quick check: Read first 5 bytes to check for PDF header
    $handle = @fopen($filepath, 'r');
    if (!$handle) {
        return false;
    }
    
    $header = fread($handle, 5);
    fclose($handle);
    
    return strpos($header, '%PDF-') === 0;
}

/**
 * Add CSS styling for PDF generation
 */
function addPdfStyling($html) {
   $css = '
     <style>
        /* Bootstrap 5 Reset and Base */
        * {
            margin: 0;
            padding: 0;
            
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.2;
            color: #212529;
            background-color: #fff;
        }
        
        /* Container */
        .container-fluid {
            width: 100%;
            padding-right: 12px;
            padding-left: 12px;
            margin-right: auto;
            margin-left: auto;
        }
        
        /* Card */
        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
        }
        
        
        .card.border-dark {
            border-color: #212529 !important;
        }
        
        .card.my-1 {
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }
        
        .rounded-0 {
            border-radius: 0 !important;
        }
        
        /* Card Header */
        .card-header {
            padding: 0.5rem 1rem;
            margin-bottom: 0;
        }
        
        /* Card Body */
        .card-body {
            flex: 1 1 auto;
            padding: 1rem;
        }
        
        /* Grid System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -12px;
            margin-left: -12px;
        }
        
        .col-1, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-10, .col-11, .col-12 {
            position: relative;
            width: 100%;
            padding-right: 12px;
            padding-left: 12px;
        }
        
        .col-1 { flex: 0 0 8.333333%; max-width: 8.333333%; }
        .col-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
        .col-3 { flex: 0 0 25%; max-width: 25%; }
        .col-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
        .col-5 { flex: 0 0 41.666667%; max-width: 41.666667%; }
        .col-6 { flex: 0 0 50%; max-width: 50%; }
        .col-7 { flex: 0 0 58.333333%; max-width: 58.333333%; }
        .col-8 { flex: 0 0 66.666667%; max-width: 66.666667%; }
        .col-9 { flex: 0 0 75%; max-width: 75%; }
        .col-10 { flex: 0 0 83.333333%; max-width: 83.333333%; }
        .col-11 { flex: 0 0 91.666667%; max-width: 91.666667%; }
        .col-12 { flex: 0 0 100%; max-width: 100%; }
        
        .offset-1 { margin-left: 8.333333%; }
        
        /* Spacing */
        .m-0 { margin: 0 !important; }
        .mt-0 { margin-top: 0 !important; }
        .mt-1 { margin-top: 0.25rem !important; }
        .mt-2 { margin-top: 0.5rem !important; }
        .mt-3 { margin-top: 1rem !important; }
        .mt-4 { margin-top: 1.5rem !important; }
        .mt-5 { margin-top: 3rem !important; }
        
        .mb-0 { margin-bottom: 0 !important; }
        .mb-1 { margin-bottom: 0.25rem !important; }
        .mb-2 { margin-bottom: 0.5rem !important; }
        .mb-3 { margin-bottom: 1rem !important; }
        .mb-4 { margin-bottom: 1.5rem !important; }
        .mb-5 { margin-bottom: 3rem !important; }
        
        .my-0 { margin-top: 0 !important; margin-bottom: 0 !important; }
        .my-1 { margin-top: 0.25rem !important; margin-bottom: 0.25rem !important; }
        .my-2 { margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
        .my-3 { margin-top: 1rem !important; margin-bottom: 1rem !important; }
        .my-4 { margin-top: 1.5rem !important; margin-bottom: 1.5rem !important; }
        .my-5 { margin-top: 3rem !important; margin-bottom: 3rem !important; }
        
        .p-0 { padding: 0 !important; }
        .p-1 { padding: 0.25rem !important; }
        .p-2 { padding: 0.5rem !important; }
        .p-3 { padding: 1rem !important; }
        .p-4 { padding: 1.5rem !important; }
        .p-5 { padding: 3rem !important; }
        
        /* Text */
        .text-center { text-align: center !important; }
        .text-start { text-align: left !important; }
        .text-end { text-align: right !important; }
        .text-uppercase { text-transform: uppercase !important; }
        .text-lowercase { text-transform: lowercase !important; }
        .text-capitalize { text-transform: capitalize !important; }
        
        .text-primary { color: #0d6efd !important; }
        .text-secondary { color: #6c757d !important; }
        .text-success { color: #198754 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-warning { color: #ffc107 !important; }
        .text-info { color: #0dcaf0 !important; }
        .text-light { color: #f8f9fa !important; }
        .text-dark { color: #212529 !important; }
        .text-white { color: #fff !important; }
        .text-muted { color: #6c757d !important; }
        
        .fw-bold { font-weight: 700 !important; }
        .fw-bolder { font-weight: bolder !important; }
        .fw-normal { font-weight: 400 !important; }
        .fw-light { font-weight: 300 !important; }
        .fw-lighter { font-weight: lighter !important; }
        
        .fst-italic { font-style: italic !important; }
        .fst-normal { font-style: normal !important; }
        
        /* Font sizes */
        .fs-1 { font-size: calc(1.375rem + 1.5vw) !important; }
        .fs-2 { font-size: calc(1.325rem + 0.9vw) !important; }
        .fs-3 { font-size: calc(1.3rem + 0.6vw) !important; }
        .fs-4 { font-size: calc(1.275rem + 0.3vw) !important; }
        .fs-5 { font-size: 1.25rem !important; }
        .fs-6 { font-size: 1rem !important; }
        
        .small { font-size: 0.875em !important; }
        .h6 { font-size: 1rem; margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
        .h5 { font-size: 1.25rem; margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
        .h4 { font-size: 1.5rem; margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
        .h3 { font-size: 1.75rem; margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
        .h2 { font-size: 2rem; margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
        .h1 { font-size: 2.5rem; margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
        
        /* Alignment */
        .align-baseline { vertical-align: baseline !important; }
        .align-top { vertical-align: top !important; }
        .align-middle { vertical-align: middle !important; }
        .align-bottom { vertical-align: bottom !important; }
        .align-text-bottom { vertical-align: text-bottom !important; }
        .align-text-top { vertical-align: text-top !important; }
        
        .align-center { align-items: center !important; }
        
        /* Table */
        .table {
            width: 100%;
            color: #212529;
            border-collapse: collapse;
        }
        
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        
        .table td, .table th {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        
        .border-1 { border-width: 1px !important; }
        .border-2 { border-width: 1px !important; }
        .border-3 { border-width: 1px !important; }
        .border-4 { border-width: 1px !important; }
        .border-5 { border-width: 1px !important; }
        
        .border-dark { border-color: #fafcffff !important; }
        .border-primary { border-color: #0d6efd !important; }
        .border-secondary { border-color: #6c757d !important; }
        .border-success { border-color: #198754 !important; }
        .border-danger { border-color: #dc3545 !important; }
        .border-warning { border-color: #ffc107 !important; }
        .border-info { border-color: #0dcaf0 !important; }
        .border-light { border-color: #f8f9fa !important; }
        .border-white { border-color: #fff !important; }
        
        /* Width & Height */
        .w-25 { width: 25% !important; }
        .w-50 { width: 50% !important; }
        .w-75 { width: 75% !important; }
        .w-100 { width: 100% !important; }
        .w-auto { width: auto !important; }
        
        /* Position */
        .position-static { position: static !important; }
        .position-relative { position: relative !important; }
        .position-absolute { position: absolute !important; }
        .position-fixed { position: fixed !important; }
        .position-sticky { position: sticky !important; }
        
        .top-0 { top: 0 !important; }
        .top-50 { top: 50% !important; }
        .top-100 { top: 100% !important; }
        
        .bottom-0 { bottom: 0 !important; }
        .bottom-50 { bottom: 50% !important; }
        .bottom-100 { bottom: 100% !important; }
        
        .start-0 { left: 0 !important; }
        .start-50 { left: 50% !important; }
        .start-100 { left: 100% !important; }
        
        .end-0 { right: 0 !important; }
        .end-50 { right: 50% !important; }
        .end-100 { right: 100% !important; }
        
        .translate-middle { transform: translate(-50%, -50%) !important; }
        .translate-middle-x { transform: translateX(-50%) !important; }
        .translate-middle-y { transform: translateY(-50%) !important; }
        
        /* Images */
        .img-fluid {
            max-width: 100%;
            height: auto;
        }
        
        .img-responsive {
            display: block;
            max-width: 100%;
            height: auto;
        }
        
        /* Custom classes for your form */
        .line {
            display: inline-block;
            min-width: 250px;
            border-bottom: 1px solid #000;
            text-align: center;
        }
        
        .fine-print-small {
            font-size: 0.5rem;
            line-height: 1;
            margin: 0;
            padding: 0;
        }
        
        .fine-print-small span {
            display: block;
            line-height: 1;
            margin: 0;
            padding: 0;
        }
        
        .small-print {
            font-size: 0.9rem;
        }
        
        .normal-print {
            font-size: 0.9rem;
        }
        
        .small-fine-print {
            font-size: 0.9rem;
            line-height: 1.2;
        }
        
        .img-signature {
            mix-blend-mode: darken;
        }
        
        .signature {
            margin-top: 60px;
            text-align: center;
        }
        
        .header-title {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        /* Horizontal Rule */
        hr {
            margin: 0;
            color: inherit;
            border: 0;
            opacity: 0.25;
        }
        
        hr.my-1 {
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }
        
        /* Underline */
        u {
            text-decoration: underline;
        }
        
        /* Italic */
        i, em {
            font-style: italic;
        }
        
        /* Print styles */
        @media print {
            body {
                font-family: "Times New Roman", serif;
                font-size: 14px;
            }
            
            .container-fluid {
                max-width: 100% !important;
                padding: 0 !important;
            }
            
            .card {
                margin: 0 !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .signature {
                margin-top: 40px;
            }
            
            .img-signature {
                max-height: 70px;
            }
            
            /* Ensure black text for printing */
            .text-danger {
                color: #000 !important;
            }
            
        }
    </style>
    ';
    
    // Insert CSS at the beginning of the HTML
    if (strpos($html, '<head>') !== false) {
        $html = preg_replace('/<head>/', '<head>' . $css, $html, 1);
    } else {
        $html = '<!DOCTYPE html><html><head>' . $css . '</head><body>' . $html . '</body></html>';
    }
    
    return $html;
}

/**
 * Convert all images to Base64
 */
function convertImagesToBase64($html, $schedule_id) {
    // Get schedule data
    $schedule = select_join(
        ['inspection_schedule'],
        ['*'],
        [
            [
                'table' => 'checklists',
                'on' => 'inspection_schedule.checklist_id = checklists.checklist_id',
                'type' => 'LEFT'
            ]
        ],
        ['inspection_schedule.schedule_id' => $schedule_id],
        null,
        1
    );
    
    if (!$schedule) {
        return $html;
    }
    
    $data = $schedule[0];
    
    // Fix for PNG warnings - convert PNG to JPEG or fix PNG profile
    $images_to_convert = [
        '../assets/img/dilg.jpg',
        '../assets/img/bfp-logo.jpg'
    ];
    
    foreach ($images_to_convert as $img_path) {
        if (file_exists($img_path)) {
            try {
                // Read and convert to base64
                $image_content = file_get_contents($img_path);
                $base64 = 'data:image/jpg;base64,' . base64_encode($image_content);
                
                // Replace all occurrences
                $html = str_replace($img_path, $base64, $html);
                
                // Also replace config constants if they exist
                $config_constants = [
                    Config::DILG_LOGO,
                    Config::BFP_LOGO
                ];
                
                foreach ($config_constants as $constant) {
                    if ($constant && strpos($html, $constant) !== false) {
                        $html = str_replace($constant, $base64, $html);
                    }
                }
            } catch (Exception $e) {
                // Continue if one image fails
                error_log("Failed to convert image: " . $img_path . " - " . $e->getMessage());
            }
        }
    }
    
    // Convert signatures
    $signature_types = [
        'RecommendingApprover' => 'Recommending Approver',
        'FinalApprover' => 'Final Approver',
        'AckByClient_id' => 'Client'
    ];
    
    foreach ($signature_types as $field => $type) {
        if (!empty($data[$field]) && isSignedBy($type, $schedule_id)) {
            $sig_file = esignature($data[$field]);
            $sig_path = '../assets/signatures/' . $sig_file;
            
            if (file_exists($sig_path)) {
                try {
                    $sig_content = file_get_contents($sig_path);
                    
                    // Determine MIME type based on file extension
                    $extension = strtolower(pathinfo($sig_path, PATHINFO_EXTENSION));
                    $mime_type = 'image/jpeg'; // default
                    if ($extension === 'png') {
                        $mime_type = 'image/png';
                    } elseif ($extension === 'gif') {
                        $mime_type = 'image/gif';
                    } elseif ($extension === 'svg') {
                        $mime_type = 'image/svg+xml';
                    }
                    
                    $sig_base64 = 'data:' . $mime_type . ';base64,' . base64_encode($sig_content);
                    $html = str_replace($sig_path, $sig_base64, $html);
                } catch (Exception $e) {
                    error_log("Failed to convert signature: " . $sig_path);
                }
            }
        }
    }
    
    return $html;
}
?>
<?php
// generate_pdf.php - FOR INSPECTION ORDERS
require_once "../includes/_init.php";
require_once('../vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = intval($_POST['schedule_id'] ?? 0);
    
    if (!$schedule_id) {
        echo json_encode(['success' => false, 'message' => 'No schedule ID provided']);
        exit;
    }
    
    try {
        // Set schedule_id for the included file
        $_GET['id'] = $schedule_id;
        
        // Start output buffering to capture HTML
        ob_start();
        include "../pages/fsed9f_email_attachment_template.php";
        $html_content = ob_get_clean();
        $html_content = addPDFStyles($html_content);
        // Convert images to Base64
      //  $html_content = convertImagesToBase64($html_content, $schedule_id);
        
        // Add PDF-specific styles
        
        
        // Generate PDF with Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('dpi', 150);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html_content);
        $dompdf->setPaper('Legal', 'portrait');
        $dompdf->render();
        
        // Save PDF file
        $unique_filename = 'Inspection_Order_' . $schedule_id . '_' . date('Ymd_His') . '.pdf';
        $filepath = '../temp_pdfs/' . $unique_filename;    
        
        file_put_contents($filepath, $dompdf->output());
        
        echo json_encode([
            'success' => true,
            'message' => 'PDF generated successfully',
            'html' => $html_content,
            'filename' => $unique_filename,
            'filepath' => $filepath,
            'size' => filesize($filepath)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error generating PDF: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
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
    
    // Convert DILG logo



    if (file_exists("../assets/img/dilg.png")) {
        $dilg_content = file_get_contents("../assets/img/dilg.png");
        $dilg_base64 = 'data:image/png;base64,' . base64_encode($dilg_content);
        $html = str_replace("../assets/img/dilg.png", $dilg_base64, $html);
    }
    
    // Convert BFP logo
    if (file_exists("../assets/img/bfp-logo.png")) {
        $bfp_content = file_get_contents("../assets/img/bfp-logo.png");
        $bfp_base64 = 'data:image/png;base64,' . base64_encode($bfp_content);
        $html = str_replace("../assets/img/bfp-logo.png", $bfp_base64, $html);
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
                $sig_content = file_get_contents($sig_path);
                $sig_base64 = 'data:image/png;base64,' . base64_encode($sig_content);
                $html = str_replace('../assets/signatures/' . $sig_file, $sig_base64, $html);
            }
        }
    }
 
    return $html;
}

/**
 * Add PDF-specific CSS styles
 */
function addPDFStyles($html) {
    $pdf_css = '<style>
    
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
    }
    .container-fluid {
        width: 100%;
        padding-right: 12px;
        padding-left: 12px;
        margin-right: auto;
        margin-left: auto;
    }
    .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
    }
    .card.border-4 {
        border-width: 4px !important;
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
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    .card-body {
        flex: 1 1 auto;
        padding: 1rem;
    }
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -12px;
        margin-left: -12px;
    }
    .col-1 {
        flex: 0 0 8.333333%;
        max-width: 8.333333%;
    }
    .col-2 {
        flex: 0 0 16.666667%;
        max-width: 16.666667%;
    }
    .col-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }
    .col-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
    .col-5 {
        flex: 0 0 41.666667%;
        max-width: 41.666667%;
    }
    .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    .col-8 {
        flex: 0 0 66.666667%;
        max-width: 66.666667%;
    }
    .col-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .col-1,
    .col-2,
    .col-3,
    .col-4,
    .col-5,
    .col-6,
    .col-8,
    .col-12 {
        position: relative;
        width: 100%;
        padding-right: 12px;
        padding-left: 12px;
    }

    .offset-1 {
        margin-left: 8.333333%;
    }

    /* Spacing Utilities */
    .m-0 {
        margin: 0 !important;
    }

    .mt-0 {
        margin-top: 0 !important;
    }

    .mt-3 {
        margin-top: 1rem !important;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .mb-1 {
        margin-bottom: 0.25rem !important;
    }

    .mb-2 {
        margin-bottom: 0.5rem !important;
    }

    .my-1 {
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }

    .p-0 {
        padding: 0 !important;
    }

    /* Text Utilities */
    .text-center {
        text-align: center !important;
    }

    .text-start {
        text-align: left !important;
    }

    .text-uppercase {
        text-transform: uppercase !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-dark {
        color: #212529 !important;
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    .small {
        font-size: 0.875em;
    }

    .h6 {
        font-size: 1rem;
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-weight: 500;
        line-height: 1.2;
    }
    .align-center {
        align-items: center !important;
    }

    /* Position */
    .position-relative {
        position: relative !important;
    }

    .position-absolute {
        position: absolute !important;
    }

    .bottom-0 {
        bottom: 0 !important;
    }

    .start-0 {
        left: 0 !important;
    }

    /* Table */
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
    }
    .table-bordered {
        border: 2px solid #000000ff;
    }

    .table-bordered td {
        border: 1px solid #000000ff;
    }

    .border-1 {
        border-width: 1px !important;
    }

    .border-dark {
        border-color: #212529 !important;
    }

    .table td {
        padding: 0.375rem;
        vertical-align: top;
        border-top: 1px solid #000000ff;
    }

    .w-100 {
        width: 100% !important;
    }

    
    .line {
        display: inline-block;
        min-width: 250px;
        border-bottom: 1px solid #000;
    }

    /* Custom Print Sizes */
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
        font-size: 0.8rem;
    }

    .normal-print {
        font-size: 0.9rem;
    }

    .small-fine-print {
        font-size: 0.7rem;
        line-height: 1.2;
    }

    /* Image */
    .img-fluid {
        max-width: 100%;
        height: auto;
    }

    .img-responsive {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .img-signature {
        mix-blend-mode: darken;
    }

    /* Horizontal Rule */
    hr {
        margin: 0.5rem 0;
        color: inherit;
        background-color: currentColor;
        border: 0;
        opacity: 0.25;
    }

    hr.my-1 {
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }

    /* Signature Section */
    .signature {
        text-align: center;
    }

    .signature .line {
        width: 280px;
        margin: 0 auto;
    }

    /* Header Title */
    .header-title {
        font-weight: bold;
        text-transform: uppercase;
    }

    /* Underline */
    u {
        text-decoration: underline;
    }

    /* Italic */
    i,
    em {
        font-style: italic;
    }

    /* Print Styles */
    @media print {
        button {
            display: none;
        }

        .container-fluid {
            max-width: 100%;
        }

        .card {
            border: 4px solid #000 !important;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
        }
    }
    </style>';
    
    // Insert CSS before closing </head>
    if (strpos($html, '</head>') !== false) {
        $html = str_replace('</head>', $pdf_css . '</head>', $html);
    } else {
        // If no head tag, add at beginning
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' . $pdf_css . '</head><body>' . $html . '</body></html>';
    }
    
    return $html;
}
?>
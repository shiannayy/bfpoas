<?php
// generate_pdf.php - FOR INSPECTION ORDERS
require_once "../includes/_init.php";
require_once('../vendor/autoload.php');

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
        
        // Convert images to Base64
        $html_content = convertImagesToBase64($html_content, $schedule_id);
        
        
        // Create PDF using TCPDF - Use full class name
        $pdf = new \TCPDF('P', 'mm', 'Legal', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('BFP Oas System');
        $pdf->SetAuthor('Bureau of Fire Protection');
        $pdf->SetTitle('Inspection Order - ' . $schedule_id);
        $pdf->SetSubject('Fire Safety Inspection Order');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        //$pdf->SetFont('Helvetica', '', 9);
        
        // Write HTML content
        $pdf->writeHTML($html_content, true, false, true, false, '');
        
        // Save PDF file - Use absolute path
        $unique_filename = 'Inspection_Order_' . $schedule_id . '_' . date('Ymd_His') . '.pdf';
        $temp_dir = dirname(__FILE__) . '/../temp_pdfs/';
        $filepath = $temp_dir . $unique_filename;
        
        // Create directory if it doesn't exist
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }
        
        // ----- ADDED CODE: Output to file with verification -----
        $output_result = $pdf->Output($filepath, 'F');
        
        // Ensure file is closed and permissions are set
        if ($output_result !== false && file_exists($filepath)) {
            chmod($filepath, 0644); // Make it readable
            
            // Verify file is valid PDF by checking header
            $is_valid_pdf = false;
            $handle = @fopen($filepath, 'r');
            if ($handle) {
                $header = fread($handle, 5);
                fclose($handle);
                $is_valid_pdf = (strpos($header, '%PDF-') === 0);
            }
            
            if ($is_valid_pdf) {
                echo json_encode([
                    'success' => true,
                    'filename' => $unique_filename,
                    'filepath' => $filepath,
                    'filesize' => filesize($filepath),
                    'generated_at' => date('Y-m-d H:i:s'),
                    'valid_pdf' => true
                ]);
            } else {
                // Clean up invalid file
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
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
        // ----- END OF ADDED CODE -----
        
    } catch (Exception $e) {
        // Clean up any partially created file
        if (isset($filepath) && file_exists($filepath)) {
            @unlink($filepath);
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Error generating PDF: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
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
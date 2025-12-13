<?php 
// wrapper_send_cert_mail.php
include_once "../includes/_init.php"; 

// Get parameters
if(!isset($_GET['schedule_id']) || !isset($_GET['role']) || !isset($_GET['token'])) {
    die("Missing required parameters.");
}

$schedule_id = intval($_GET['schedule_id']);
$role = $_GET['role']; // 'ChiefFSES' or 'FireMarshal'
$token = $_GET['token'];
$recipient_email = $_GET['recipient_email'] ?? '';
$recipient_name = $_GET['recipient_name'] ?? '';

// Validate token
$token_data = select("email_token", ['email_token' => $token, 'schedule_id' => $schedule_id]);
if(empty($token_data)) {
    showTokenError();
    exit();
}

// Get inspection details
$inspection = getInspectionDetails($schedule_id);
if (!$inspection) {
    die("Inspection not found");
}

// Get order number and establishment
$scheduleInfo = select("inspection_schedule", ['schedule_id' => $schedule_id]);
$order_number = $scheduleInfo[0]['order_number'] ?? '';
$establishment = $scheduleInfo[0]['proceed_instructions'] ?? '';

// Determine email content based on role
switch($role) {
    case 'ChiefFSES':
        $subject = "Inspection Certification #{$order_number} - Recommendation Required";
        $action = "recommend";
        $button_text = "Recommend for Approval";
        $intro = "An inspection has been completed and requires your recommendation for certification.";
        break;
        
    case 'FireMarshal':
        $subject = "Inspection Certification #{$order_number} - Final Approval Required";
        $action = "approve";
        $button_text = "Approve Certification";
        $intro = "An inspection has been recommended and requires your final approval for certification.";
        break;
        
    case 'client':
        $subject = "Fire Safety Inspection Certificate #{$order_number} - APPROVED";
        $action = "view";
        $button_text = "View Certificate";
        $intro = "Your fire safety inspection has been approved. Please find your certificate attached.";
        break;
        
    default:
        $subject = "Inspection Update #{$order_number}";
        $action = "";
        $button_text = "Review";
        $intro = "An update is available for your inspection.";
}

// Build acknowledgement link
$link = Config::WEBSITE_EMAIL_URL . "email_ack/cert.php?token=" . $token . 
        "&schedule_id=" . $schedule_id . 
        "&role=" . $role;

// Generate PDF (if needed)
$pdf_filename = "certification_{$order_number}.pdf";
$pdf_path = "../temp/{$pdf_filename}";

// Build email HTML
$emailContent = buildCertEmailHTML($role, $recipient_name, $order_number, $establishment, 
                                  $inspection['compliance_rate'], $link, $button_text, $intro);

// Store data for JavaScript
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Certification Email</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loading-spinner { display: none; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .compliance-badge { 
            display: inline-block; 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-weight: bold;
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h3>Sending Certification Email</h3>
        <small class="text-danger">Do not close until completed.</small>
        
        <div id="data-container" style="display:none;">
            <span data-name="scheduleId"><?= $schedule_id ?></span>
            <span data-name="orderNumber"><?= $order_number ?></span>
            <span data-name="role"><?= $role ?></span>
            <span data-name="recipientName"><?= $recipient_name ?></span>
            <span data-name="recipientEmail"><?= $recipient_email ?></span>
            <span data-name="establishment"><?= $establishment ?></span>
            <span data-name="complianceRate"><?= $inspection['compliance_rate'] ?></span>
            <span data-name="link"><?= $link ?></span>
        </div>
        
        <div id="email-preview" class="d-none">
            <?= $emailContent ?>
        </div>
        
        <div class="mt-3">
            <button id="sendEmailBtn" class="btn btn-primary">Send Email</button>
            <div id="loading" class="mt-3 loading-spinner">
                <div class="spinner-border spinner-border-sm"></div>
                <span>Processing...</span>
            </div>
        </div>
        
        <div id="result" class="mt-3"></div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function() {
        // Auto-start email sending
        setTimeout(() => {
            $('#sendEmailBtn').click();
        }, 500);
        
        $('#sendEmailBtn').click(async function() {
            $(this).prop('disabled', true);
            $('#loading').show();
            $('#result').html('');
            
            const scheduleId = $('#data-container [data-name="scheduleId"]').text();
            const orderNumber = $('#data-container [data-name="orderNumber"]').text();
            const role = $('#data-container [data-name="role"]').text();
            const recipientName = $('#data-container [data-name="recipientName"]').text();
            const recipientEmail = $('#data-container [data-name="recipientEmail"]').text();
            const establishment = $('#data-container [data-name="establishment"]').text();
            const complianceRate = $('#data-container [data-name="complianceRate"]').text();
            const link = $('#data-container [data-name="link"]').text();
            
            try {
                // 1. Generate certification PDF
                $('#result').html('<div class="alert alert-info">Generating PDF...</div>');
                
                const pdfResponse = await $.ajax({
                    url: '../includes/generate_cert_pdf.php',
                    type: 'POST',
                    data: {
                        schedule_id: scheduleId,
                        action: 'generate'
                    },
                    dataType: 'json'
                });
                
                if (!pdfResponse.success) {
                    throw new Error(pdfResponse.message || 'Failed to generate PDF');
                }
                
                // 2. Send email
                $('#result').html('<div class="alert alert-info">Sending email...</div>');
                
                const emailResponse = await $.ajax({
                    url: '../includes/send_cert_email.php',
                    type: 'POST',
                    data: {
                        schedule_id: scheduleId,
                        recipient_email: recipientEmail,
                        recipient_name: recipientName,
                        role: role,
                        token: '<?= $token ?>',
                        subject: '<?= $subject ?>'
                    },
                    dataType: 'json'
                });
                
                if (emailResponse.success) {
                    $('#result').html(`
                        <div class="alert alert-success">
                            <h5>✓ Email Sent Successfully!</h5>
                            <p>To: ${recipientName} (${recipientEmail})</p>
                            <p>Subject: <?= $subject ?></p>
                            <p>Compliance Rate: <span class="badge bg-success">${complianceRate}%</span></p>
                            <small class="text-muted">${new Date().toLocaleTimeString()}</small>
                        </div>
                    `);
                    
                    // Auto-close after 3 seconds
                    setTimeout(() => {
                        window.close();
                    }, 3000);
                    
                } else {
                    throw new Error(emailResponse.message || 'Failed to send email');
                }
                
            } catch (error) {
                $('#result').html(`
                    <div class="alert alert-danger">
                        <h5>✗ Error</h5>
                        <p>${error.message}</p>
                        <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary">Retry</button>
                    </div>
                `);
                $('#sendEmailBtn').prop('disabled', false);
            } finally {
                $('#loading').hide();
            }
        });
    });
    </script>
</body>
</html>

<?php
function showTokenError() {
    ?>
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>✗ Link Expired or Invalid</h4>
            <p>This acknowledgement link has already been used or is no longer valid.</p>
        </div>
    </div>
    <?php
}

function buildCertEmailHTML($role, $recipientName, $orderNumber, $establishment, $complianceRate, $link, $buttonText, $intro) {
    $complianceColor = $complianceRate >= 75 ? '#28a745' : '#dc3545';
    
    return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto;">
    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px 5px 0 0;">
        <h2 style="margin: 0;">Fire Safety Inspection System</h2>
        <h3 style="color: #333; margin-top: 10px;">Certification Process</h3>
    </div>
    
    <div style="background: white; padding: 20px; border: 1px solid #ddd;">
        <p>Dear {$recipientName},</p>
        
        <p>{$intro}</p>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <table style="width: 100%;">
                <tr>
                    <td><strong>Order Number:</strong></td>
                    <td>#{$orderNumber}</td>
                </tr>
                <tr>
                    <td><strong>Establishment:</strong></td>
                    <td>{$establishment}</td>
                </tr>
                <tr>
                    <td><strong>Compliance Rate:</strong></td>
                    <td>
                        <span style="display: inline-block; padding: 3px 10px; border-radius: 15px; 
                              background: {$complianceColor}; color: white; font-weight: bold;">
                            {$complianceRate}%
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="background: #e7f3ff; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0;">
            <h4 style="margin-top: 0;">
                { $role == 'client' ? 'Your certification is ready' : 'Action Required' }
            </h4>
            <a href="{$link}" style="display: inline-block; background: #0d6efd; color: white; 
               padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                {$buttonText}
            </a>
            <p style="margin-top: 15px; font-size: 14px;">
                If the button doesn't work, copy this link:<br>
                <code style="background: white; padding: 5px; border-radius: 3px;">{$link}</code>
            </p>
        </div>
        <p>Thank you for your prompt attention to this matter.</p>
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
            <p>This is an automated message from Fire Safety Evaluation System.</p>
        </div>
    </div>
</div>
HTML;
}
?>
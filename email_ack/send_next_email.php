<?php
// send_next_email.php
include_once "../includes/_init.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../email_ack/cert.php?error=invalid_request");
    exit;
}

$schedule_id = $_POST['schedule_id'] ?? 0;
$order_number = $_POST['order_number'] ?? '';
$role = $_POST['role'] ?? '';
$recipient_email = $_POST['recipient_email'] ?? '';
$recipient_name = $_POST['recipient_name'] ?? '';
$token = $_POST['token'] ?? '';
$establishment = $_POST['establishment'] ?? '';
$compliance_rate = $_POST['compliance_rate'] ?? 0;

// Email settings
$your_email = "SystemNotification@bfp-oas-fsic.site";
$your_password = getConfigValue('EMAIL_PASS');
$your_name = "BFP OAS";

// Build link
$base_url = "https://" . $_SERVER['HTTP_HOST'];
if($role != 'client'){
    $link = Config::WEBSITE_BASE_URL  . "/email_ack/cert.php?token=" . $token . "&schedule_id=" . $schedule_id . "&role=" . $role;
}
else{
    $inspection_id = select('inspections',['schedule_id' => $schedule_id])[0]['inspection_id'];
    $link = Config::WEBSITE_BASE_URL  . "/email_ack/print_certificate.php?token=" . $token . "&inspection_id=" . $inspection_id ;
}

$inspection_link = Config::WEBSITE_BASE_URL . "/email_ack/inspection_results.php?token=" . $token . "&schedule_id=" . $schedule_id . "&role=" . $role; 
// Build email content
$emailContent = buildCertEmail($role, $recipient_name, $order_number, $establishment, $compliance_rate, $link, $inspection_link);
$subject = getCertSubject($role, $order_number);

try {
    $mail = new PHPMailer(true);
    
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = $your_email;
    $mail->Password = $your_password;
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    
    // Email content
    $mail->setFrom($your_email, $your_name);
    $mail->addAddress($recipient_email, $recipient_name);
    $mail->Subject = $subject;
    $mail->Body = $emailContent;
    $mail->isHTML(true);
    
    if ($mail->send()) {
        // Redirect back with success
        header("Location: ../email_ack/cert.php?email_sent=1&recipient=${role}");
    } else {
        header("Location: ../email_ack/cert.php?email_sent=0&email_error=1&recipient=${role}");
    }
} catch (Exception $e) {
    header("Location: ../email_ack/cert.php?email_sent=0&email_error=1&recipient=${role}");
}

function buildCertEmail($role, $recipientName, $orderNumber, $establishment, $complianceRate, $link, $inspection_link) {
    $config = [
        'client' => [
            'title' => 'Certificate Available',
            'button' => 'View Certificate',
            'description' => 'Your inspection certificate is now available.'
        ],
        'ChiefFSES' => [
            'title' => 'Recommendation Required',
            'button' => 'Recommend for Approval',
            'description' => 'An inspection requires your recommendation for certification.'
        ],
        'FireMarshall' => [
            'title' => 'Approval Required',
            'button' => 'Approve Certification',
            'description' => 'An inspection requires your final approval.'
        ]
    ][$role] ?? ['title' => 'Action Required', 'button' => 'Take Action', 'description' => 'Action required.'];
    
    return "

    <div style='font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto;'>
        <div style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>
            <h2 style='color: #333;'>Fire Safety Inspection System</h2>
            <h3 style='color: #0d6efd;'>{$config['title']}</h3>
        </div>
        
        <div style='background: white; padding: 30px; border: 1px solid #ddd;'>
            <p>Dear {$recipientName},</p>
            <p>{$config['description']}</p>
            
            <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                <table style='width: 100%;'>
                    <tr><td><strong>Order Number:</strong></td><td>#{$orderNumber}</td></tr>
                    <tr><td><strong>Establishment:</strong></td><td>{$establishment}</td></tr>
                    <tr><td><strong>Compliance Rate:</strong></td><td>{$complianceRate}%</td></tr>
                </table>
            </div>
            
            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$inspection_link}' style='display: inline-block; background: #0d6efd; color: white; 
                   padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    Inspection Results
                </a>
                 <p>If the button doesn't work, copy this link:<br>
                 <code style='background: #f8f9fa; padding: 5px; border-radius: 3px;'>{$inspection_link}</code></p>
            </div>

            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$link}' style='display: inline-block; background: #0d6efd; color: white; 
                   padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    {$config['button']}
                </a>
            </div>
            
            <p>If the button doesn't work, copy this link:<br>
            <code style='background: #f8f9fa; padding: 5px; border-radius: 3px;'>{$link}</code></p>
        </div>
    </div>";
}

function getCertSubject($role, $orderNumber) {
    $subjects = [
        'client' => "[BFP OAS - FSIC] Certificate Available #{$orderNumber}",
        'ChiefFSES' => "[BFP OAS - FSIC] Recommendation Required #{$orderNumber}",
        'FireMarshal' => "[BFP OAS - FSIC] Approval Required #{$orderNumber}"
    ];
    return $subjects[$role] ?? "[BFP OAS - FSIC] Action Required #{$orderNumber}";
}
?>
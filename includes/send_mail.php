<?php
// send_mail.php
require_once "../includes/_init.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// YOUR HOSTINGER SETTINGS
$your_email = "SystemNotification@bfp-oas-fsic.site";
$your_password = getConfigValue('EMAIL_PASS');
$your_name = "BFP OAS";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // GET FORM DATA WITH PROPER VALIDATION
    $to_emails = [];
    
    // Handle both array and string formats
    if (isset($_POST['to_email'])) {
        if (is_array($_POST['to_email'])) {
            // Handle array format: to_email[]
            $to_emails = $_POST['to_email'];
        } else {
            // Handle string format: "email1@test.com; email2@test.com"
            $to_emails = preg_split('/[;,]/', $_POST['to_email']);
        }
    }
    
    // Clean and validate emails
    $valid_emails = [];
    foreach ($to_emails as $email) {
        $email = trim($email);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid_emails[] = $email;
        }
    }
    
    $subject = "[BFP OAS - FSIC] " . ($_POST['subject'] ?? 'No Subject');
    $message = $_POST['message'] ?? 'No message content';
    
    // Check if we have valid emails
    if (empty($valid_emails)) {
        echo json_encode([
            'success' => false,
            'message' => 'No valid email addresses provided'
        ]);
        exit;
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // SMTP Settings (Hostinger)
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = $your_email;
        $mail->Password = $your_password;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        // Email content
        $mail->setFrom($your_email, $your_name);
        
        // Add all valid recipients
        foreach ($valid_emails as $email) {
            $mail->addAddress($email);
        }
        
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(true);
        
        // Handle attachment
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $attachment = $_FILES['attachment'];
            $mail->addAttachment(
                $attachment['tmp_name'],
                $attachment['name']
            );
        }
        
        // Send email
        if ($mail->send()) {
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully to: ' . implode(', ', $valid_emails)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send email'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
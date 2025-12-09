<?php 
include_once "../includes/_init.php"; 
// Get parameters
if(!isset($_GET['schedule_id']) || !isset($_GET['step']) || !isset($_GET['email_token']) ){
    die("Seems something was not sent right.");
}

$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$email_token = $_GET['email_token'] ?? '';

if (!$schedule_id) die("undefined schedule_id");

// Get schedule information
$scheduleInfo = select("inspection_schedule", ['schedule_id' => $schedule_id]);
if (empty($scheduleInfo)) die("Schedule not found");
    $order_number = $scheduleInfo[0]['order_number'];
    $gen_info_id = $scheduleInfo[0]['gen_info_id'];
    $owner_id = getOwnerInfo($gen_info_id)[0]['user_id'];
    $establishment = $scheduleInfo[0]['proceed_instructions'] ?? '';
    $inspector_id = $scheduleInfo[0]['inspector_id'];

// Default user IDs from config
$chief = select('users',['sub_role'=>'Chief FSES'],null,1);
    $chiefFses_id = $chief[0]['user_id'];
    $chiefFses_email = $chief[0]['email'];


$fm = select('users',['sub_role'=>'Fire Marshall'],null,1);
    $fm_id = $fm[0]['user_id'];
    $fm_email = $fm[0]['email'];

$email_token_sched = select("email_token", ['email_token' => $email_token]);


// Determine recipient based on step
$recipient_info = [];
switch($step) {
    case 1: // Send to Client
        $user_info = select('users', ['user_id' => $owner_id])[0] ?? [];
        $recipient_info = [
            'email' => $user_info['email'],
            'name' => $user_info['full_name'] ?? 'Client',
            'role' => 'client',
            'next_step' => 2,
            'next_recipient' => getUserInfo($inspector_id, 'email'),
            'next_recipient_name' => getUserInfo($inspector_id, 'full_name')
        ];
        break;
        
    case 2: // Send to Inspector
        $email = getUserInfo($inspector_id,'email');
        $fullname = getUserInfo($inspector_id,'full_name');
        $recipient_info = [
            'email' => $email,
            'name' => $fullname,
            'role' => 'inspector',
            'next_step' => 3,
            'next_recipient' => $chiefFses_email,
            'next_recipient_name' => 'Chief FSES'
        ];
        break;
        
    case 3: // Send to Chief FSES
        $recipient_info = [
            'email' => $chiefFses_email,
            'name' => 'Chief FSES',
            'role' => 'chief_fses',
            'next_step' => 4,
            'next_recipient' => $fm_email,
            'next_recipient_name' => 'Fire Marshal'
        ];
        break;
        
    case 4: // Send to Fire Marshal
        $recipient_info = [
            'email' => $fm_email,
            'name' => 'Fire Marshal',
            'role' => 'fire_marshal',
            'next_step' => 0, // Final step
            'next_recipient' => '',
            'next_recipient_name' => ''
        ];
        break;
        
    default:
        $recipient_info = [
            'email' => Config::ADMIN_EMAIL,
            'name' => 'Administrator',
            'role' => 'admin',
            'next_step' => 1,
            'next_recipient' => '',
            'next_recipient_name' => ''
        ];
}

// Build query parameters for next step
// $query_params = [
//     'email_token' => $email_token,
//     'schedule_id' => $schedule_id,
//     'step' => $recipient_info['next_step'],
//     'order_number' => $order_number,
//     // 'owner_id' => $owner_id,
//     //'owner_name' => $recipient_info['name'],
//     'recepient' => $recipient_info['next_recipient'],
//     'est_name' => $establishment
// ];
$link = "http://localhost/bfpoas-online/email_ack/?email_token=" . $email_token . 
        "&schedule_id=" . $schedule_id . 
        "&step=" . $step;
//$link = "http://localhost/bfpoas-online/email_ack/?" . http_build_query($query_params);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Inspection Order - Step <?= $step ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loading-spinner { display: none; }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h3>Sending Inspection Order via Email - Step <?= $step ?></h3>
        <small class="small text-danger">Do Not Close until Completed.</small> <br>
        
        <div id="data-container" style="display:none;">
            <span data-name="scheduleId"><?= $schedule_id ?></span>
            <span data-name="orderNumber"><?= $order_number ?></span>
            <span data-name="recipientName"><?= $recipient_info['name'] ?></span>
            <span data-name="recipientEmail"><?= $recipient_info['email'] ?></span>
            <span data-name="establishment"><?= $establishment ?></span>
            <span data-name="step"><?= $step ?></span>
            <span data-name="nextStep"><?= $recipient_info['next_step'] ?></span>
            <span data-name="link"><?= $link ?></span>
        </div>
        
        <emailContent>
            <?php if($step === 1): ?>
                <h4>Inspection Order Acknowledgement</h4>
                <p>Hi <?= $recipient_info['name'] ?>,</p>
                <p>This is to inform you that an inspection order has been filed for your establishment:</p>
                <h5><?= $establishment ?></h5>
                <p>Please check the attached Inspection Order document and click the link below to acknowledge:</p>
                <a class="btn btn-primary" href="<?= $link ?>">Acknowledge Inspection Order</a>
                <p><small>Inspection Order Number: <?= $order_number ?></small></p>
                
            <?php elseif($step === 2): ?>
                <h4>Inspection Assignment</h4>
                <p>Hi <?= $recipient_info['name'] ?>,</p>
                <p>You have been assigned to inspect the following establishment:</p>
                <h5><?= $establishment ?></h5>
                <p>Please review the attached Inspection Order and click the link below to acknowledge:</p>
                <a class="btn btn-primary" href="<?= $link ?>">Acknowledge Assignment</a>
                <p><small>Inspection Order Number: <?= $order_number ?></small></p>
                
            <?php elseif($step === 3): ?>
                <h4>Recommendation Required</h4>
                <p>Hi <?= $recipient_info['name'] ?>,</p>
                <p>The inspection order for <strong><?= $establishment ?></strong> requires your recommendation.</p>
                <p>Please review the attached document and click the link below to recommend:</p>
                <a class="btn btn-primary" href="<?= $link ?>">Recommend Inspection</a>
                <p><small>Inspection Order Number: <?= $order_number ?></small></p>
                
            <?php elseif($step === 4): ?>
                <h4>Approval Required</h4>
                <p>Hi <?= $recipient_info['name'] ?>,</p>
                <p>The inspection order for <strong><?= $establishment ?></strong> requires your final approval.</p>
                <p>Please review the attached document and click the link below to approve:</p>
                <a class="btn btn-primary" href="<?= $link ?>">Approve Inspection</a>
                <p><small>Inspection Order Number: <?= $order_number ?></small></p>
            <?php endif; ?>
        </emailContent>

        <button id="sendTestBtn" class="btn btn-primary mt-3">
            Send Email
        </button>
        
        <div id="loading" class="mt-3 loading-spinner">
            <div class="spinner-border"></div> 
            <span id="status-text">Processing...</span>
        </div>
        
        <div id="result" class="mt-3"></div>
        
        <div id="progress" class="mt-3" style="display:none;">
            <div class="progress">
                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="mt-2">
                <small id="step-text">Step 1: Generating PDF...</small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/send_mail.js"></script>
    
    <script>
    $(document).ready(function(){
        // Get data from hidden container
        const dataContainer = $('#data-container');
        const scheduleId = dataContainer.find('[data-name="scheduleId"]').text();
        const orderNumber = dataContainer.find('[data-name="orderNumber"]').text();
        const recipientName = dataContainer.find('[data-name="recipientName"]').text();
        const recipientEmail = dataContainer.find('[data-name="recipientEmail"]').text();
        const establishment = dataContainer.find('[data-name="establishment"]').text();
        const step = parseInt(dataContainer.find('[data-name="step"]').text());
        const nextStep = parseInt(dataContainer.find('[data-name="nextStep"]').text());
        const emailContentHTML = $("emailContent").html();
        
        console.log('Loaded data:', {
            scheduleId,
            orderNumber,
            recipientName,
            recipientEmail,
            step,
            nextStep
        });

        // Auto-trigger email sending after 1 second
        setTimeout(() => {
            $('#sendTestBtn').trigger('click');
        }, 1000);
        
        $('#sendTestBtn').click(async function(){
            $('#loading').show();
            $('#progress').show();
            $('#result').html('').removeClass('alert-success alert-danger alert-warning');
            
            updateProgress(0, 'Starting email process...');
            
            try {
                // STEP 1: Generate PDF
                updateProgress(10, 'Generating PDF document...');
                
                const pdfResponse = await $.ajax({
                    url: '../includes/generate_pdf.php',
                    type: 'POST',
                    data: {
                        schedule_id: scheduleId,
                        generate_pdf: 1
                    },
                    dataType: 'json',
                    timeout: 30000
                });
                
                console.log('PDF Generation Response:', pdfResponse);
                
                if (!pdfResponse.success) {
                    throw new Error('PDF Generation Failed: ' + pdfResponse.message);
                }
                
                updateProgress(30, 'PDF generated successfully');
                
                // STEP 2: Check file accessibility
                updateProgress(40, 'Verifying PDF file accessibility...');
                
                const isFileAccessible = await checkFileAccessibility(pdfResponse.filepath, 5000, 10);
                
                console.log('File accessibility check result:', isFileAccessible);
                
                if (!isFileAccessible) {
                    throw new Error('PDF file is not accessible.');
                }
                
                updateProgress(60, 'PDF file verified and accessible');
                
                // STEP 3: Get PDF as File
                updateProgress(70, 'Preparing PDF for email attachment...');
                
                let pdfUrl = pdfResponse.filepath;
                console.log('Original filepath:', pdfUrl);

                // Convert absolute path to URL if needed
                if (pdfUrl.startsWith('/')) {
                    const currentUrl = window.location.href;
                    const urlObj = new URL(currentUrl);
                    const basePath = urlObj.pathname;
                    
                    const pathParts = basePath.split('/').filter(p => p);
                    if (pathParts.length > 0) {
                        const projectFolder = pathParts[0];
                        const regex = new RegExp(`^.*?/${projectFolder}/`);
                        if (regex.test(pdfUrl)) {
                            pdfUrl = pdfUrl.replace(regex, `/${projectFolder}/`);
                        } else {
                            const htdocsIndex = pdfUrl.indexOf('/htdocs/');
                            if (htdocsIndex !== -1) {
                                pdfUrl = pdfUrl.substring(htdocsIndex + 7);
                            }
                        }
                    }
                    
                    if (!pdfUrl.startsWith('/')) {
                        pdfUrl = '/' + pdfUrl;
                    }
                    
                    pdfUrl = window.location.origin + pdfUrl;
                }

                console.log('Fetching PDF from:', pdfUrl);
                
                const pdfBlob = await fetch(pdfUrl, {
                    cache: 'no-cache',
                    headers: {
                        'Pragma': 'no-cache',
                        'Cache-Control': 'no-cache'
                    }
                }).then(response => {
                    console.log('Fetch response status:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error(`Failed to fetch PDF: ${response.status} ${response.statusText}`);
                    }
                    return response.blob();
                });
                
                console.log('PDF blob size:', pdfBlob.size);
                
                if (pdfBlob.size === 0) {
                    throw new Error('PDF file is empty or corrupted');
                }
                
                const pdfFile = new File([pdfBlob], 'Inspection_Order_' + orderNumber + '.pdf', { 
                    type: 'application/pdf',
                    lastModified: Date.now()
                });
                
                updateProgress(80, 'PDF attached successfully');
                
                // STEP 4: Send email with attachment
                updateProgress(90, 'Sending email with attachment...');
                
                // Determine subject based on step
                let subject = '';
                switch(step) {
                    case 1:
                        subject = "Inspection Order #" + orderNumber + " - Acknowledgement Required";
                        break;
                    case 2:
                        subject = "Inspection Assignment #" + orderNumber + " - Acknowledgement Required";
                        break;
                    case 3:
                        subject = "Inspection Order #" + orderNumber + " - Recommendation Required";
                        break;
                    case 4:
                        subject = "Inspection Order #" + orderNumber + " - Final Approval Required";
                        break;
                    default:
                        subject = "Inspection Order #" + orderNumber;
                }
                
                const emailResponse = await sendEmail(
                    subject,
                    recipientEmail,
                    emailContentHTML,
                    pdfFile
                );
                
                // STEP 5: Process result
                updateProgress(100, 'Email process completed');
                
                if (emailResponse.success) {
                    let successMessage = '';
                    switch(step) {
                        case 1:
                            successMessage = 'Email sent to Client for acknowledgement.';
                            break;
                        case 2:
                            successMessage = 'Email sent to Inspector for acknowledgement.';
                            break;
                        case 3:
                            successMessage = 'Email sent to Chief FSES for recommendation.';
                            break;
                        case 4:
                            successMessage = 'Email sent to Fire Marshal for approval.';
                            break;
                    }
                    
                    $('#result').addClass('alert alert-success')
                               .html(`
                                <h5>✓ ${successMessage}</h5>
                                <p>Inspection Order Number: ${orderNumber}</p>
                                <p>Recipient: ${recipientName} (${recipientEmail})</p>
                                <p>Establishment: ${establishment}</p>
                                <p>PDF: ${pdfResponse.filename} (${formatBytes(pdfBlob.size)})</p>
                                ${nextStep > 0 ? `<p><small>Next: Step ${nextStep}</small></p>` : ''}
                                <small class="text-muted">Email ID: ${emailResponse.message || 'Sent'}</small>
                               `);
                    
                    // Cleanup PDF after 3 seconds
                    setTimeout(() => {
                        cleanupPDF(pdfResponse.filename, scheduleId);
                    }, 3000);
                    
                } else {
                    throw new Error('Email sending failed: ' + emailResponse.message);
                }
                
            } catch (error) {
                console.error('Full error:', error);
                updateProgress(0, 'Process failed');
                $('#result').addClass('alert alert-danger')
                           .html(`
                            <h5>✗ Error in Step ${step}</h5>
                            <p>${error.message}</p>
                            <p><small>Please contact BFP-OAS support</small></p>
                            <small class="text-muted">Inspection Order Number: ${orderNumber}</small>
                           `);
            } finally {
                $('#loading').hide();
                setTimeout(() => {
                    $('#progress').hide();
                }, 3000);
            }
        });
        
        // File accessibility check function
        async function checkFileAccessibility(filepath, timeout = 5000, maxRetries = 10) {
            return new Promise((resolve) => {
                let retries = 0;
                const startTime = Date.now();
                
                function checkFile() {
                    retries++;
                    
                    $.ajax({
                        url: '../includes/check_file.php',
                        type: 'POST',
                        data: { 
                            filepath: filepath,
                            schedule_id: scheduleId
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Check file response:', response);
                            if (response.accessible) {
                                resolve(true);
                            } else if (retries < maxRetries && (Date.now() - startTime) < timeout) {
                                $('#status-text').text(`Waiting for file... (attempt ${retries}/${maxRetries})`);
                                setTimeout(checkFile, 500);
                            } else {
                                resolve(false);
                            }
                        },
                        error: function() {
                            if (retries < maxRetries && (Date.now() - startTime) < timeout) {
                                $('#status-text').text(`Retrying file check... (attempt ${retries}/${maxRetries})`);
                                setTimeout(checkFile, 500);
                            } else {
                                resolve(false);
                            }
                        }
                    });
                }
                
                checkFile();
            });
        }
        
        // Cleanup PDF function
        async function cleanupPDF(filename, scheduleId) {
            try {
                await $.post('../includes/cleanup_pdf.php', {
                    filename: filename,
                    schedule_id: scheduleId
                });
                console.log('PDF cleanup successful');
            } catch (error) {
                console.log('PDF cleanup failed:', error);
            }
        }
        
        // Progress update function
        function updateProgress(percent, message) {
            $('#progress-bar').css('width', percent + '%');
            $('#step-text').text(message);
            $('#status-text').text(message);
        }
        
        // Format file size
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    });
    </script>
</body>
</html>
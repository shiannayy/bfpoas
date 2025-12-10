<?php 
include_once "../includes/_init.php"; 
// Get parameters
if(!isset($_GET['schedule_id']) || !isset($_GET['step']) || !isset($_GET['email_token']) ){
    die("Seems something was not sent right.");
}

$schedule_id = htmlentities($_GET['schedule_id']);//isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;
$step = isset($_GET['step']) ? $_GET['step'] : 1;
$email_token = $_GET['email_token'] ?? '';
$isEmailtoken = select("email_token",['email_token' => $email_token , 'schedule_id' => $schedule_id],null,1);
if(empty($isEmailtoken)){ ?>
                <h1 class="error">✗ Link Expired or Invalid</h1>
                <p class="lead">This acknowledgement link has already been used or is no longer valid.</p>
                <div class="alert alert-warning mt-4">
                    <h5>What to do next:</h5>
                    <ul class="text-start">
                        <li>Check if you've already acknowledged this inspection order</li>
                        <li>Contact BFP-OAS if you need to re-send the acknowledgement link</li>
                        <li>Ensure you're using the most recent email link</li>
                    </ul>
                </div>
<?php  } 

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

// if($step > 0 && $step < 4){
//     //prevent from resending the email
//     $old_email_token = $email_token;
//     $email_token = bin2hex(random_bytes(8));
//     update_data("email_token",['email_token' => $email_token], ['email_token' => $old_email_token]);
// }

$link = "http://localhost/bfpoas/email_ack/?email_token=" . $email_token . 
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
    .loading-spinner {
        display: none;
    }

    .success {
        color: #198754;
    }

    .error {
        color: #dc3545;
    }

    .warning {
        color: #ffc107;
    }
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

        <div class="email-container d-none" data-name="emailContent">
            <a class="btn btn-primary" style="text-decoration: none;padding: 3px; background-color: blue; border-radius: 10px; color: white;" href="<?= $link ?>">Acknowledge Link</a>
            <hr>
            <?php if($step == 1) { ?>
            <h4>Inspection Order Acknowledgement</h4>
            <p>Hi <?= $recipient_info['name'] ?>,</p>
            <p>This is to inform you that an inspection order has been filed for your establishment:</p>
            <h5><?= $establishment ?></h5>
            <p>Please check the attached Inspection Order document and click the link below to acknowledge:</p>
            <a class="btn btn-primary" style="" href="<?= $link ?>">Acknowledge Inspection Order</a>
            <p><small>Inspection Order Number: <?= $order_number ?></small></p>

            <?php }  if($step == 2){ ?>
            <h4>Inspection Assignment</h4>
            <p>Hi <?= $recipient_info['name'] ?>,</p>
            <p>You have been assigned to inspect the following establishment:</p>
            <h5><?= $establishment ?></h5>
            <p>Please review the attached Inspection Order and click the link below to acknowledge:</p>
            <a class="btn btn-primary" href="<?= $link ?>">Acknowledge Assignment</a>
            <p><small>Inspection Order Number: <?= $order_number ?></small></p>

            <?php }  if($step == 3) {?>
            <h4>Recommendation Required</h4>
            <p>Hi <?= $recipient_info['name'] ?>,</p>
            <p>The inspection order for <strong><?= $establishment ?></strong> requires your recommendation.</p>
            <p>Please review the attached document and click the link below to recommend:</p>
            <a class="btn btn-primary" href="<?= $link ?>">Recommend Inspection</a>
            <p><small>Inspection Order Number: <?= $order_number ?></small></p>

            <?php }  if($step == 4){ ?>
            <h4>Approval Required</h4>
            <p>Hi <?= $recipient_info['name'] ?>,</p>
            <p>The inspection order for <strong><?= $establishment ?></strong> requires your final approval.</p>
            <p>Please review the attached document and click the link below to approve:</p>
            <a class="btn btn-primary" href="<?= $link ?>">Approve Inspection</a>
            <p><small>Inspection Order Number: <?= $order_number ?></small></p>
            <?php } ?>
            </div>

        <button id="sendTestBtn" class="btn btn-primary mt-3">
            Send Email
        </button>

        <div id="loading" class="mt-3 loading-spinner">
            <div class="spinner-border spinner-border-sm"></div>
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

    $(document).ready(function() {
        // Get data from hidden container
        const dataContainer = $('#data-container');
        const scheduleId = dataContainer.find('[data-name="scheduleId"]').text();
        const orderNumber = dataContainer.find('[data-name="orderNumber"]').text();
        const recipientName = dataContainer.find('[data-name="recipientName"]').text();
        const recipientEmail = dataContainer.find('[data-name="recipientEmail"]').text();
        const establishment = dataContainer.find('[data-name="establishment"]').text();
        const step = parseInt(dataContainer.find('[data-name="step"]').text());
        const nextStep = parseInt(dataContainer.find('[data-name="nextStep"]').text());
        const emailContentHTML = $('div.email-container[data-name="emailContent"]').html();
        
        console.log('Loaded data:', {
            scheduleId,
            orderNumber,
            recipientName,
            recipientEmail,
            step,
            nextStep
        });

        // Auto-trigger email sending immediately
        setTimeout(() => {
            $('#sendTestBtn').trigger('click');
            $('#sendTestBtn').prop("disabled",true);
        }, 500); // Reduced from 1000ms to 500ms

        $('#sendTestBtn').click(async function() {
            $('#loading').show();
            $('#progress').show();
            $('#result').html('').removeClass('alert-success alert-danger alert-warning');

            updateProgress(0, 'Starting email process...');

            try {
                // STEP 1: Generate PDF with progress monitoring
                updateProgress(10, 'Generating PDF document...');

                const pdfResponse = await generatePDFWithProgress(scheduleId,step);
                console.log('PDF Generation Response:', pdfResponse);

                if (!pdfResponse.success) {
                    throw new Error('PDF Generation Failed: ' + pdfResponse.message);
                }

                updateProgress(30, 'PDF generated successfully');

                // STEP 2: Check file accessibility with real polling
                updateProgress(40, 'Verifying PDF file accessibility...');

                const isFileAccessible = await waitForFileAccess(pdfResponse.filepath, scheduleId);
                console.log('File accessibility check result:', isFileAccessible);

                if (!isFileAccessible) {
                    throw new Error('PDF file is not accessible after multiple attempts.');
                }

                updateProgress(60, 'PDF file verified and accessible');

                // STEP 3: Get PDF as File
                updateProgress(70, 'Preparing PDF for email attachment...');

                const {
                    pdfBlob,
                    pdfUrl
                } = await fetchPDFFile(scheduleId, pdfResponse.filename);

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

                const subject = getEmailSubject(step, orderNumber);
                const emailResponse = await sendEmail(
                    subject,
                    recipientEmail,
                    emailContentHTML,
                    pdfFile
                );

                // STEP 5: Process result
                updateProgress(100, 'Email process completed');

                if (emailResponse.success) {
                    showSuccessMessage(step, orderNumber, recipientName, recipientEmail,
                        establishment, pdfResponse.filename, pdfBlob.size, nextStep);

                    // Cleanup PDF after success
                    setTimeout(() => {
                        cleanupPDF(pdfResponse.filename, scheduleId);
                    }, 1000); // Reduced from 3000ms

                } else {
                    throw new Error('Email sending failed: ' + emailResponse.message);
                }

            } catch (error) {
                console.error('Full error:', error);
                updateProgress(0, 'Process failed');
                showErrorMessage(step, error.message, orderNumber);
            } finally {
                $('#sendTestBtn').prop("disabled",false).text("Re-Send Email");
                $('#loading').hide();
                // Hide progress bar immediately
                setTimeout(() => {
                    $('#progress').hide();
                }, 1000); // Reduced from 3000ms
            }
        });

        // ========== OPTIMIZED HELPER FUNCTIONS ==========

        // Generate PDF with better error handling
        async function generatePDFWithProgress(scheduleId, step) {
            // Just call generate_pdf.php - it will handle caching internally
            return await $.ajax({
                url: '../includes/generate_pdf.php',
                type: 'POST',
                data: {
                    schedule_id: scheduleId,
                    step: step,
                    generate_pdf: 1
                },
                dataType: 'json',
                timeout: 15000
            });
        }



        // Wait for file to be accessible with exponential backoff
        async function waitForFileAccess(filepath, scheduleId, maxAttempts = 10) {
            for (let attempt = 1; attempt <= maxAttempts; attempt++) {
                updateProgress(40 + (attempt * 2),
                    `Checking PDF file (attempt ${attempt}/${maxAttempts})...`);

                try {
                    const response = await checkFileSingleAttempt(filepath, scheduleId);
                    if (response.accessible) { // Check response.accessible, not just isAccessible
                        return true;
                    }
                } catch (error) {
                    console.log(`File check attempt ${attempt} failed:`, error.message);
                }
                // Exponential backoff: 250ms, 500ms, 750ms, etc.
                await sleep(50 * attempt);
            }
            return false;
        }

        // Single file check attempt
        async function checkFileSingleAttempt(filepath, scheduleId) {
            return new Promise((resolve) => {
                $.ajax({
                    url: '../includes/check_file.php',
                    type: 'POST',
                    data: {
                        filepath: filepath,
                        schedule_id: scheduleId
                    },
                    dataType: 'json',
                    timeout: 3000,
                    success: function(response) {
                        // Return the full response, not just accessible boolean
                        resolve(response);
                    },
                    error: function() {
                        resolve({
                            accessible: false
                        });
                    }
                });
            });
        }

        // Updated fetchPDFFile function using schedule_id instead of filepath
        async function fetchPDFFile(scheduleId, filename) {
            // Use server endpoint with schedule_id
            const pdfUrl = `../includes/serve_pdf.php?schedule_id=${scheduleId}`;
            console.log('Fetching PDF for schedule:', scheduleId, 'from:', pdfUrl);

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

            return {
                pdfBlob,
                pdfUrl
            };
        }

        // Get email subject based on step
        function getEmailSubject(step, orderNumber) {
            const subjects = {
                1: "Inspection Order #" + orderNumber + " - Acknowledgement Required",
                2: "Inspection Assignment #" + orderNumber + " - Acknowledgement Required",
                3: "Inspection Order #" + orderNumber + " - Recommendation Required",
                4: "Inspection Order #" + orderNumber + " - Final Approval Required"
            };
            return subjects[step] || "Inspection Order #" + orderNumber;
        }

        // Show success message
        function showSuccessMessage(step, orderNumber, recipientName, recipientEmail,
            establishment, filename, fileSize, nextStep) {
            const messages = {
                1: 'Email sent to Client for acknowledgement.',
                2: 'Email sent to Inspector for acknowledgement.',
                3: 'Email sent to Chief FSES for recommendation.',
                4: 'Email sent to Fire Marshal for approval.'
            };

            $('#result').addClass('card shadow')
                .html(`
                <div class="card-body">
                    <h5>✓ ${messages[step] || 'Email sent successfully'}</h5>
                    <p>Inspection Order Number: ${orderNumber}</p>
                    <p>Recipient: ${recipientName} (${recipientEmail})</p>
                    <p>Establishment: ${establishment}</p>
                    <p>PDF: ${filename} (${formatBytes(fileSize)})</p>
                    ${nextStep > 0 ? `<p><small>Next: Step ${nextStep}</small></p>` : ''}
                    <small class="text-muted">${new Date().toLocaleTimeString()}</small>
                </div>
                   `);
        }

        // Show error message
        function showErrorMessage(step, errorMessage, orderNumber) {
            $('#result').addClass('alert alert-danger')
                .html(`
                    <h5>✗ Error in Step ${step}</h5>
                    <p>${errorMessage}</p>
                    <p><small>Please contact BFP-OAS support</small></p>
                    <small class="text-muted">Inspection Order Number: ${orderNumber}</small>
                    <button class="btn btn-sm btn-outline-secondary mt-2" onclick="$('#sendTestBtn').click()">
                        Retry
                    </button>
                   `);
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

        // Sleep utility function
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
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
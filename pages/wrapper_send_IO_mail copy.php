<?php 
include_once "../includes/_init.php"; 
// Get schedule_id from URL or default
//$schedule_id = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : 0;
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

if (!$schedule_id) die("undefined schedule_id");

$recepientEmail = isset($_GET['recepient']) ? $_GET['recepient'] : Config::ADMIN_EMAIL ;
$establishment = isset($_GET['est_name']) ? $_GET['est_name'] : '';

$step = isset($_GET['sched_step']) ? intval($_GET['sched_step']) : 1;
$scheduleInfo = select("inspection_schedule",['schedule_id' => $schedule_id]);
$inspector_assigned = $scheduleInfo[0]['assigned_to_officer_id'];

$order_number = $_GET['order_number'] ?? $scheduleInfo[0]['order_number'];
$recepientName = isset($_GET['owner_name']) ? $_GET['owner_name'] : select('users',['email' => $_GET['recepient'] ])[0]['full_name'];
$owner_id = isset($_GET['owner_id']) ? intval($_GET['owner_id']) : select('users',['email' => $_GET['recepient'] ])[0]['user_id'];

$config_RecoApprover_email = "ashianna395@gmail.com";
$config_Approver_email = "ashianna395@gmail.com";
$config_Inspector_email = getUserInfo($inspector_assigned,"email");
$config_Inspector_name = getUserInfo($inspector_assigned);

$query_params = [];
switch($step){
    case 1:
        $recepientRole = "client";
        $recepientSysId = $owner_id;
        $email_token = generateToken(8,"sched");
            update_data("users",['email_token'=>$email_token],['user_id' => $recepientSysId]);
       
        $query_params = [
        'email_token' => $email_token,
        'schedule_id' => $schedule_id,
        'step' => 2,
        'order_number' => $order_number,
        'owner_id' => $owner_id,
        'owner_name' => $recepientName,
        'recepient' => $config_Inspector_email,
        'est_name' => $establishment
        ];

    break;
}


$link = "localhost/bfpoas-online/email_ack/?" . http_build_query($query_params);



?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send I.0.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loading-spinner {
            display: none;
        }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h3>Sending Inspection Order via Email</h3>
        <small class="small text-danger">Do Not Close until Completed.</small> <br>
        <scheduleid><?= $schedule_id ?></scheduleid>
        <ordernumber><?= $order_number ?></ordernumber>

        <span>Recepient:</span> 
        <recepientName><?= $recepientName ?></recepientName>
        <recepientEmail><?= $recepientEmail ?></recepientEmail>

        <emailContent>
                <?php
                    if($step === 1 ){ 
                        //email Client for inspection order
                        ?>
                        Hi <?= $recepientName ?>, <br>
                        <p class="text-start">
                            This is to inform you that an inspection order has been filed for your establishment
                            <b><?= $establishment ?></b>
                        </p>
                        <p>Check the inspection order attached. click the link below to acknowledge the Inspection Order.</p>
                        <hr>
                        <a class="btn btn-primary text-center" href="<?= $link ?>">Acknowledge Here</a>
                        <hr>
                    <?php }
                    if($step === 2){ ?>
                        Hi <?= $config_Inspector_name ?>, <br>
                        <p class="text-start">
                            This is to inform you that an inspection order has been filed and you were assigned to Inspect
                            <b><?= $establishment ?></b>
                        </p>
                        <p>Check the inspection order attached. click the link below to acknowledge the Inspection Order.</p>
                        <hr>
                        <a class="btn btn-primary text-center" href="<?= $link ?>">Acknowledge Here</a>
                        <hr>
                    <?php }
                ?>    
        </emailContent>

        <button id="sendTestBtn" class=" btn btn-primary">
            Resend
        </button>
        <div id="loading" class="mt-3 loading-spinner">
            <div class="spinner-border"></div> 
            <span id="status-text">Processing...</span>
        </div>
        
        <div id="result" class="mt-3"></div>
        
        <!-- Progress tracking -->
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

    // Since you're using custom HTML tags, use them directly:
let orderNumber = $("ordernumber").text();
let scheduleId = $("scheduleid").text();
let ownerFullname = $("recepientName").text();
let recepient = $("recepientEmail").text(); // Changed from recepient to recepientEmail
let emailContentHTML = $("emailContent").html();
    
        

        $('#sendTestBtn').click(async function(){
            $('#loading').show();
            $('#progress').show();
            $('#result').html('').removeClass('alert-success alert-danger alert-warning');
            
            updateProgress(0, 'Starting process...');
            
            try {
                // STEP 1: Generate PDF on server
                updateProgress(10, 'Generating PDF document...');
                
                const pdfResponse = await $.ajax({
                    url: '../includes/generate_pdf.php',
                    type: 'POST',
                    data: {
                        schedule_id: scheduleId,
                        generate_pdf: 1
                    },
                    dataType: 'json',
                    timeout: 30000 // 30 second timeout
                });
                
                console.log('PDF Generation Response:', pdfResponse);
                
                if (!pdfResponse.success) {
                    throw new Error('PDF Generation Failed: ' + pdfResponse.message);
                }
                
                updateProgress(30, 'PDF generated successfully');
                
                // STEP 2: Check if file is accessible
                updateProgress(40, 'Verifying PDF file accessibility...');
                
                // Wait for file to be fully written and accessible
                const isFileAccessible = await checkFileAccessibility(pdfResponse.filepath, 5000, 10);
                
                console.log('File accessibility check result:', isFileAccessible);
                
                if (!isFileAccessible) {
                    throw new Error('PDF file is not accessible. It may not exist or is not readable.');
                }
                
                updateProgress(60, 'PDF file verified and accessible');
                
                // STEP 3: Get PDF as File
                updateProgress(70, 'Preparing PDF for email attachment...');
                
                // Convert filepath to URL if it's an absolute path
                let pdfUrl = pdfResponse.filepath;
                    console.log('Original filepath:', pdfUrl);


               // If it's an absolute path starting with /, convert to URL
                if (pdfUrl.startsWith('/')) {
                    // Get the current URL path
                    const currentUrl = window.location.href;
                    const urlObj = new URL(currentUrl);
                    const basePath = urlObj.pathname;
                    
                    // Extract project folder name (bfpoas-online)
                    const pathParts = basePath.split('/').filter(p => p);
                    if (pathParts.length > 0) {
                        const projectFolder = pathParts[0]; // Should be "bfpoas-online"
                        
                        // Remove everything before the project folder in the filepath
                        const regex = new RegExp(`^.*?/${projectFolder}/`);
                        if (regex.test(pdfUrl)) {
                            pdfUrl = pdfUrl.replace(regex, `/${projectFolder}/`);
                        } else {
                            // If not found, use a simpler approach
                            const htdocsIndex = pdfUrl.indexOf('/htdocs/');
                            if (htdocsIndex !== -1) {
                                pdfUrl = pdfUrl.substring(htdocsIndex + 7); // +7 to include '/htdocs'
                            }
                        }
                    }
                    
                    // Ensure it starts with /
                    if (!pdfUrl.startsWith('/')) {
                        pdfUrl = '/' + pdfUrl;
                    }
                    
                    // Make it a full URL
                    pdfUrl = window.location.origin + pdfUrl;
                }

                console.log('Converted PDF URL:', pdfUrl);
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
                
                // Verify blob size
                if (pdfBlob.size === 0) {
                    throw new Error('PDF file is empty or corrupted');
                }
                
                const pdfFile = new File([pdfBlob], 'Inspection_Order.pdf', { 
                    type: 'application/pdf',
                    lastModified: Date.now()
                });
                
                updateProgress(80, 'PDF attached successfully');
                // STEP 4: Send email with attachment
                updateProgress(90, 'Sending email with attachment...');
                
                const emailContent = emailContentHTML;
                
                const emailResponse = await sendEmail(
                    "Inspection Order #" + ${orderNumber},
                    recepient,
                    emailContent,
                    pdfFile
                );
                
                // STEP 5: Process result
                updateProgress(100, 'Email process completed');
                
                if (emailResponse.success) {
                    $('#result').addClass('alert alert-success')
                               .html(`
                                <h5>✓ Email sent successfully!</h5>
                                <p>Inspection Order Number: ${orderNumber} </p>
                                <p>Recipient: ${recepient} </p>
                                <p>PDF: ${pdfResponse.filename} (${formatBytes(pdfBlob.size)})</p>
                                <small class="text-muted">Email ID: ${emailResponse.message || 'Sent'}</small>
                               `);
                    
                    // Optional: Cleanup the temporary PDF file
                    setTimeout(() => {
                        cleanupPDF(pdfResponse.filename);
                    }, 3000);
                    
                } else {
                    throw new Error('Email sending failed: ' + emailResponse.message);
                }
                
            } catch (error) {
                console.error('Full error:', error);
                updateProgress(0, 'Process failed');
                $('#result').addClass('alert alert-danger')
                           .html(`
                            <h5>✗ Error</h5>
                            <p>${error.message}</p>
                            <p><small>Contact BFP-OAS, send this</small></p>
                            <small class="text-muted">Inspection Order Number: ${orderNumber}</small>
                           `);
            } finally {
                $('#loading').hide();
                setTimeout(() => {
                    $('#progress').hide();
                }, 3000);
            }
        });
        
        // Test function to debug PDF generation
        // async function testPDFGeneration() {
        //     try {
        //         console.log('Testing PDF generation...');
                
        //         const response = await $.ajax({
        //             url: '../includes/generate_pdf.php',
        //             type: 'POST',
        //             data: { 
        //                 schedule_id: <?= $schedule_id ?>,
        //             },
        //             dataType: 'json'
        //         });
                
        //         console.log('Test Response:', response);
        //         return response;
                
        //     } catch (error) {
        //         console.error('Test failed:', error);
        //         throw error;
        //     }
        // }
        
        // Function to check file accessibility with retries
        async function checkFileAccessibility(filepath, timeout = 5000, maxRetries = 10) {
            return new Promise((resolve, reject) => {
                let retries = 0;
                const startTime = Date.now();
                
                function checkFile() {
                    retries++;
                    
                    $.ajax({
                        url: '../includes/check_file.php',
                        type: 'POST',
                        data: { 
                            filepath: filepath,
                            schedule_id: <?= $schedule_id ?>
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
                        error: function(xhr, status, error) {
                            console.error('Check file AJAX error:', status, error);
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
        
        // Alternative: Direct fetch test
        async function testFileFetch(filepath) {
            try {
                let testUrl = filepath;
                if (testUrl.startsWith('/')) {
                    const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                    const projectRoot = basePath.substring(0, basePath.lastIndexOf('/'));
                    testUrl = projectRoot + testUrl.replace(/^.*\/htdocs/, '');
                }
                
                console.log('Testing fetch from:', testUrl);
                const response = await fetch(testUrl, { method: 'HEAD' });
                console.log('HEAD response:', response.status, response.statusText);
                return response.ok;
            } catch (error) {
                console.error('Fetch test error:', error);
                return false;
            }
        }
        
        // Function to cleanup PDF file
        async function cleanupPDF(filename) {
            try {
                await $.post('../includes/cleanup_pdf.php', {
                    filename: filename,
                    schedule_id: <?= $schedule_id ?>
                });
                console.log('PDF cleanup successful');
            } catch (error) {
                console.log('PDF cleanup failed (non-critical):', error);
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

    // $(document).ready(function(){
    //     setTimeout(() => {
    //     $('#sendTestBtn').trigger('click');
    // }, 1000);
    // });
        
    </script>
</body>
</html>
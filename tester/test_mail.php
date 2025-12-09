<?php 
include_once "../includes/_init.php"; 
// Get schedule_id from URL or default
$schedule_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$schedule_id) die("Add ?id=SCHEDULE_ID to URL");
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email with PDF</title>
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
        <h3>Sending Email with PDF Attachment</h3>
        
        <button id="sendTestBtn" class="btn btn-primary">
            Send Test Email with PDF
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
                        schedule_id: <?= $schedule_id ?>,
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
                
                const emailContent = `
                    Hi Test Recipient,<br><br>
                    <p>Good Day, you have a pending Acknowledgement for an Inspection Order</p>
                    <b>Inspection Order Details:</b><br>
                    - Schedule ID: <?= $schedule_id ?><br>
                    - This is a test email with PDF attachment.<br><br>
                    Please review the attached document.
                `;
                
                const emailResponse = await sendEmail(
                    "TEST: Inspection Order #<?= $schedule_id ?>",
                    "reymar.a.llagas@gmail.com; ashianna395@gmail.com",
                    emailContent,
                    pdfFile
                );
                
                // STEP 5: Process result
                updateProgress(100, 'Email process completed');
                
                if (emailResponse.success) {
                    $('#result').addClass('alert alert-success')
                               .html(`
                                <h5>✓ Email sent successfully!</h5>
                                <p>Schedule ID: <?= $schedule_id ?></p>
                                <p>Recipient: reymar.a.llagas@gmail.com; ashianna395@gmail.com</p>
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
                            <p><small>Check browser console for more details</small></p>
                            <small class="text-muted">Schedule ID: <?= $schedule_id ?></small>
                           `);
            } finally {
                $('#loading').hide();
                setTimeout(() => {
                    $('#progress').hide();
                }, 3000);
            }
        });
        
        // Test function to debug PDF generation
        async function testPDFGeneration() {
            try {
                console.log('Testing PDF generation...');
                
                const response = await $.ajax({
                    url: '../includes/generate_pdf.php',
                    type: 'POST',
                    data: { 
                        schedule_id: <?= $schedule_id ?>,
                    },
                    dataType: 'json'
                });
                
                console.log('Test Response:', response);
                return response;
                
            } catch (error) {
                console.error('Test failed:', error);
                throw error;
            }
        }
        
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
        
        // Debug: Test on page load
         testPDFGeneration().then(response => {
             console.log('Auto-test completed:', response);
         });
    });
    </script>
</body>
</html>
<?php 
// send_email_with_pdf.php
include_once "../includes/_init.php";
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email with PDF</title>
    <!-- Bootstrap 5.3.2 CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
        rel="stylesheet" 
        crossorigin="anonymous">
    <style>
        #pdfContainer { display: none; }
        .loading { display: none; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php
        // Get schedule_id from URL
        $schedule_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$schedule_id) {
            echo '<div class="alert alert-danger">No schedule ID provided</div>';
            exit;
        }
        
        // Fetch schedule data
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
            echo '<div class="alert alert-danger">Invalid Schedule ID</div>';
            exit;
        }
        
        $data = $schedule[0];
        ?>
        
        <h2>Send Inspection Order Email</h2>
        
        <!-- Hidden container for PDF generation -->
        <div id="pdfContainer">
            <?php include_once "../pages/print_inspection_order.php"; ?>
        </div>
        
        <!-- Form for email details -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Recipient Name</label>
                    <input type="text" id="receiverName" class="form-control" 
                           value="<?= htmlspecialchars($data['to_officer'] ?? '') ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Recipient Email</label>
                    <input type="email" id="receiverEmail" class="form-control" 
                           placeholder="recipient@example.com" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Subject</label>
                    <input type="text" id="emailSubject" class="form-control" 
                           value="Inspection Order #<?= htmlspecialchars($data['order_number']) ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Additional Message</label>
                    <textarea id="additionalMessage" class="form-control" rows="3">
Good Day, you have a pending Acknowledgement for an Inspection Order.
Please review the attached document and complete the acknowledgement section.
                    </textarea>
                </div>
                
                <!-- Loading -->
                <div id="loading" class="loading alert alert-info">
                    <div class="spinner-border spinner-border-sm me-2"></div>
                    Generating PDF and sending email...
                </div>
                
                <!-- Result Message -->
                <div id="resultMessage" class="alert" style="display: none;"></div>
                
                <!-- Send Button -->
                <button id="sendEmailBtn" class="btn btn-primary">
                    <i class="bi bi-envelope"></i> Send Email with PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script>
    $(document).ready(function(){
        const scheduleId = <?= $schedule_id ?>;
        
        function showMessage(message, type = 'success') {
            const resultDiv = $('#resultMessage');
            resultDiv.removeClass('alert-success alert-danger alert-warning')
                     .addClass('alert-' + (type === 'error' ? 'danger' : type))
                     .html(message)
                     .show();
            
            if (type === 'success') {
                setTimeout(() => resultDiv.fadeOut(), 5000);
            }
        }
        
        function setLoading(show) {
            $('#loading').toggle(show);
            $('#sendEmailBtn').prop('disabled', show);
        }
        
        // Generate PDF on server
        async function generatePDF(htmlContent) {
            try {
                const response = await $.ajax({
                    url: '../includes/generate_pdf.php',
                    type: 'POST',
                    data: {
                        html_content: htmlContent,
                        schedule_id: scheduleId,
                        filename: 'Inspection_Order_<?= $data['order_number'] ?>.pdf'
                    },
                    dataType: 'json'
                });
                return response;
            } catch (error) {
                throw new Error('PDF generation failed: ' + error.responseText);
            }
        }
        
        // Get PDF as File
        async function getPDFBlob(filepath) {
            const response = await fetch(filepath);
            if (!response.ok) throw new Error('Failed to fetch PDF');
            const blob = await response.blob();
            return new File([blob], 'Inspection_Order.pdf', { type: 'application/pdf' });
        }
        
        // Main function
        async function sendEmailWithPDF() {
            setLoading(true);
            
            try {
                // 1. Get HTML for PDF
                const pdfHtml = $('#pdfContainer').html();
                
                // 2. Generate PDF
                const pdfResult = await generatePDF(pdfHtml);
                if (!pdfResult.success) throw new Error(pdfResult.message);
                
                // 3. Get PDF as File
                const pdfFile = await getPDFBlob(pdfResult.filepath);
                
                // 4. Prepare email content
                const receiverName = $('#receiverName').val();
                const emailContent = `
                    Hi ${receiverName},<br><br>
                    ${$('#additionalMessage').val()}<br><br>
                    <b>Inspection Order Details:</b><br>
                    - Order Number: <?= $data['order_number'] ?><br>
                    - Scheduled Date: <?= $data['scheduled_date'] ?><br>
                    - Purpose: <?= $data['purpose'] ?><br><br>
                    Please find the attached inspection order.<br><br>
                    Best regards,<br>
                    BFP OAS
                `;
                
                // 5. Send email (using your send_mail.js function)
                const emailResponse = await sendEmail(
                    $('#emailSubject').val(),
                    $('#receiverEmail').val(),
                    emailContent,
                    pdfFile
                );
                
                // 6. Show result
                if (emailResponse.success) {
                    showMessage('✓ Email with PDF sent successfully!', 'success');
                    
                    // Clean up temp file
                    $.post('../includes/cleanup_pdf.php', {
                        filename: pdfResult.filename
                    });
                    
                } else {
                    showMessage('✗ ' + emailResponse.message, 'error');
                }
                
            } catch (error) {
                showMessage('✗ Error: ' + error.message, 'error');
                console.error(error);
            } finally {
                setLoading(false);
            }
        }
        
        $('#sendEmailBtn').click(sendEmailWithPDF);
    });
    </script>
    
    <!-- Include your send_mail.js -->
    <script src="../assets/js/send_mail.js"></script>
</body>
</html>
<?php
include_once "../includes/_init.php";

$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

// Optional: You can also get token and role for acknowledgement links
if(isset($_GET['token'])){
    $token = $_GET['token'] ?? '';
    if($token){
        $res = select('email_token',['email_token' => $token], null, 1);
        if(empty($res)){
            $schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;
        }
        else{
            $schedule_id = $res[0]['schedule_id'];
        }
    }
}


$role = $_GET['role'];

// Build acknowledgement link if token exists
$acknowledgement_link = '';
if ($token && $schedule_id) {
    $base_url = "https://" . $_SERVER['HTTP_HOST'];
    $acknowledgement_link = Config::WEBSITE_BASE_URL . "/email_ack/cert.php?token=" . $token . "&schedule_id=" . $schedule_id . "&role=" . $role;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inspection Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 12px !important;
            }
            .table {
                font-size: 10px !important;
            }
        }
        .inspection-report img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
  </head>
  <body>
    <div class="container-fluid">
        <!-- Optional: Add navigation/action buttons -->
        <div class="row mt-3 no-print">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Inspection Report</h2>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary me-2">
                            <i class="bi bi-printer"></i> Print / Save as PDF
                        </button>
                        <button onclick="window.close()" class="btn btn-secondary">
                            Close
                        </button>
                    </div>
                </div>
                <div class="alert alert-info">
                    <strong>Schedule ID:</strong> #<?= $schedule_id ?> | 
                    <strong>Generated:</strong> <?= date('Y-m-d H:i:s') ?>
                </div>
            </div>
        </div>
        
        <!-- Report will be loaded here -->
        <div class="row">
            <div class="col-12">
                <div id="inspectionResult"></div>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" class="text-center mt-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading inspection report...</p>
        </div>
        
        <!-- Error message container (hidden by default) -->
        <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;">
            Failed to load inspection report. Please try again.
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="../assets/js/main.js"></script>
    
    <script>
        $(document).ready(function(){
            const scheduleId = <?= $schedule_id ?>;
            const token = "<?= $token ?>";
            const role = "<?= $role ?>";
            const acknowledgementLink = "<?= $acknowledgement_link ?>";
            
            if (!scheduleId) {
                showError("No schedule ID provided");
                return;
            }
            
            // Check if we should include acknowledgement
            const includeAcknowledgement = token && acknowledgementLink;
            
            // Build the inspection report
            buildInspectionReport(scheduleId, 'inspectionResult', {
                isForPrint: false,
                isForEmail: false,
                includeAcknowledgement: includeAcknowledgement,
                acknowledgementLink: acknowledgementLink
            })
            .then(result => {
                // Success - hide loading indicator
                $('#loadingIndicator').hide();
                
                // If there's an acknowledgement section, add some JavaScript for it
                if (includeAcknowledgement) {
                    // You could add additional JavaScript here for acknowledgement handling
                    console.log('Report loaded with acknowledgement link for role:', role);
                }
            })
            .catch(error => {
                // Error - show error message
                $('#loadingIndicator').hide();
                $('#errorMessage').show().html(`
                    <h4>Error Loading Report</h4>
                    <p>${error.message || 'Failed to load inspection report'}</p>
                    <button onclick="location.reload()" class="btn btn-warning btn-sm">Retry</button>
                `);
                console.error('Error building inspection report:', error);
            });
        });
        
        function showError(message) {
            $('#loadingIndicator').hide();
            $('#inspectionResult').html(`
                <div class="alert alert-danger">
                    <h4>Error</h4>
                    <p>${message}</p>
                    <a href="../dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            `);
        }
    </script>
  </body>
</html>
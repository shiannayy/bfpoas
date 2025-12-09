<!DOCTYPE html>
<?php
include_once "../includes/_init.php";
//define($_SESSION['user_id'],$_SESSION['user_id']);
$hasSignature = false;
    /*-----------*/

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inspections</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <!--    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <style>
        #signatureCanvas {
            width: 100%;
            height: 100%;
            display: block;
            border: 1px solid #ccc;
            touch-action: none;
            /* prevent scrolling while drawing */
        }

        .page-button {
            margin-left: 5px;
            margin-right: 5px;
        }
        input#SearchIns:focus {
            outline: none !important;
            box-shadow: none !important;
        }
    </style>
</head>

<body>

   <!-- Inspection Report Modal -->
<div class="modal fade" id="inspectionReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content shadow-lg" style="max-height: 95vh;">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Inspection Report</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal body now scrolls -->
      <div class="modal-body overflow-auto" style="max-height: 70vh;">
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle" id="inspectionReportTable">
            <thead class="table-light">
              <tr>
                <th style="width: 10%">Section</th>
                <th style="width: 35%">Item</th>
                <th style="width: 15%">Response</th>
                <th style="width: 20%">Remarks</th>
                <th style="width: 20%">Proof</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="5" class="text-center text-muted">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="exportReportBtn" data-sched-id="">
          <i class="bi bi-file-earmark-excel"></i> Export
        </button>
      </div>
    </div>
  </div>
</div>
   
    <!-- Fullscreen Offcanvas -->
    <div class="offcanvas offcanvas-bottom h-100" tabindex="-1" id="signatureOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Draw Signature
                <?php if(esignature($_SESSION['user_id']) !== NULL ){
                        $hasSignature = true;
                    ?>
                <!-- Thumbnail -->
                <img src="../assets/signatures/<?php echo esignature($_SESSION['user_id']);?>" alt="Signature" height="50px" data-bs-toggle="modal" data-bs-target="#signaturePreviewModal">
                <?php } ?>

            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <!-- Signature Canvas -->
            <canvas id="signatureCanvas"></canvas>

            <!-- Buttons -->
            <div class="d-flex justify-content-between p-3 bg-light border-top">
                <button class="btn btn-secondary" id="clearSignature">Clear</button>
                <button class="btn btn-primary" id="saveSignature">Save</button>
            </div>
        </div>
    </div>

    <div class="container-fluid" style="margin-top: 100px">

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <div class="mb-0">
            <span class="fs-4 mb-0">Inspections and Certificates</span>
            <br>
            <span class="small">Completed and Inprogress Inspections</span>
          </div>

            
        <?php if(isChiefFSES() || isClient() || isFireMarshall() ){ ?>
            <a href="#" class="btn btn-gold btn-sm add-signature" data-user="<?php echo $_SESSION['user_id']; ?>" data-role="<?php echo $_SESSION['role']; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                    <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z" />
                </svg>
                <span class="text-navy d-none d-lg-inline">
                    <?php echo $hasSignature ? 'Update' : 'Set New'; ?> E-Signature
                </span>

            </a>
        <?php } ?>

            <input type="text" id="SearchIns" class="rounded-0 border-2 border-dark border-top-0 border-start-0 border-end-0 form-control form-control-sm w-50" placeholder="Search...">
        </div>


        <div class="table-responsive overflow-y-scroll mt-2">
            <table class="table table-striped table-responsive table-hover table-bordered align-middle" id="scheduleTable">
                <thead class="table-navy">
                    <tr class="text-center align-middle">
                        <th>Order No.</th>
                        <th>Scheduled Date</th>
                        <th>Start Date</th>
                        <th>Date Completed</th>
                        <th>Building Info</th>
                        <th>Remarks and Score</th>
                        <th>Inspection Statistic</th>
                        <th>FSIC Recommended for Approval</th>
                        <th>Approved</th>
                        <th>Client Received</th>
                        <th>Assigned Inspector</th>
                        <th>Checklist Type</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody id="inspectionTableBody" class="overflow-y-scroll">
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Loading inspections...
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <!-- Bootstrap JS -->
   
    

</body>

</html>
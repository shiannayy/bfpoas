<?php
include_once "../includes/_init.php";
$hasSignature = false;

if(isset($_GET['action']) && isset($_GET['sched_id'])){
    $action = htmlspecialchars($_GET['action'] ?? ' ');
    $sid = htmlspecialchars($_GET['sched_id']);
    $exist = select("inspection_schedule",["schedule_id" => $sid]);
    
    if($action == 'reschedule'){
            if(isset($_GET['remarks'])){
                $remarks = htmlspecialchars($_GET['remarks']);
            }
            update_data("inspection_schedule",
                            ["scheduled_date" => $exist[0]['preferredSchedule'],
                              "inspection_sched_status" => "Scheduled",
                              "remarks" => "[Approved] " . $remarks
                            ],
                            ["schedule_id" => $sid]);
    }
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <!--    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <style>
        
        
.offcanvas-bottom {
    border-top-left-radius: 30px !important;
    border-top-right-radius: 30px !important;
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

.offcanvas-bottom {
    transform: translateY(100%);
    transition: transform 0.35s ease-in-out;
}

.offcanvas-bottom.show {
    transform: translateY(0);
}
     

        .dropdown-toggle::after {
            display: none !important;
        }

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
    </style>
</head>

<body>
    <div class="modal fade" id="cancelScheduleModal" tabindex="-1" aria-labelledby="cancelScheduleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelScheduleLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Cancellation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to <b>cancel this schedule</b>? This action cannot be undone.</p>

                    <form id="cancelScheduleForm">
                        <input type="hidden" id="cancelScheduleId" name="schedule_id">
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label fw-semibold small">Reason / Remarks:</label>
                            <textarea id="cancelReason" name="reason" class="form-control" rows="3" placeholder="Enter reason for cancellation..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Confirm Cancel</button>
                </div>
            </div>
        </div>
    </div>
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
    <!-- Signature Preview Modal -->
    <div class="modal fade" id="signaturePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Signature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="signaturePreviewImg" src="" class="img-fluid border p-2" alt="Signature Preview">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSaveSignature">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!--    reschedule request-->
    <div class="offcanvas offcanvas-start bg-navy-dark text-gold" tabindex="-1" id="reschedCanvas" aria-labelledby="reschedCanvasLabel">
        <div class="offcanvas-header">
            <h5 id="reschedCanvasLabel">Request Reschedule</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="reschedForm">
                <input type="hidden" name="schedule_id" id="schedule_id">

                <div class="mb-3">
                    <label for="preferred_date" class="form-label">Preferred Date</label>
                    <input type="date" class="form-control" id="preferred_date" name="preferred_date" required>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Rescheduling</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Submit Request</button>
            </form>
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
    <!-- Other Details of A schedule   -->
    <div class="offcanvas offcanvas-bottom h-75" tabindex="-1" id="moreDetails">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Scheduled Inspection</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
           <div class="container-fluid" id="moreDetailsBody"></div>
        </div>
    </div>

    <div class="container-fluid mt-4 px-0">
        <div class="d-flex align-items-center gap-2 flex-wrap px-2">
            <span class="fs-4 mb-0">Inspection Schedules</span>
            <a href="?page=sched_ins" class="btn btn-gold btn-sm d-none btn-new-schedule">
                + New Schedule
            </a>

            <a href="?page=ins_sched&calendar_view" class="btn btn-gold btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3 text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
                    <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                </svg>
                <span class="text-navy d-none d-lg-inline">Calendar View</span>
            </a>

           <?php if( !isDataEntry() && !isInspector() ){ ?>
            <a href="#" class="btn btn-gold btn-sm add-signature position-relative" data-user="<?php echo $_SESSION['user_id']; ?>" data-role="<?php echo $_SESSION['role']; ?>">
                <?php if(!$hasSignature){?>
                <span class="px-1 bg-danger border-rounded rounded-2 position-absolute top-0 start-100 translate-middle" style="font-size:8pt;">
                    <span class="text-uppercase text-light">Sign</span>
                </span>
                <?php  } ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                    <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z" />
                </svg>
                <span class="text-navy d-none d-lg-inline">
                    <?php echo $hasSignature ? 'Update' : 'Set New'; ?> E-Signature
                </span>
            </a>
            <?php } ?>
            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleArchived" data-status-toggle="Archived">
                <label class="form-check-label" for="toggleArchived">Show Archived</label>
            </div>

            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleCompleted" data-status-toggle="Completed">
                <label class="form-check-label" for="toggleCompleted">Show Completed</label>
            </div>

            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleCancelled" data-status-toggle="Cancelled">
                <label class="form-check-label" for="toggleCancelled">Show Cancelled</label>
            </div>


            <input type="text" id="SearchInsSched" class="form-control form-control-sm w-auto" placeholder="Search...">
        </div>


        <div class="table-responsive overflow-y-scroll mt-2 px-0" style="min-height: 50vh">
            <div class="container pagination"></div>
            <table class="mx-0 w-100 table table-striped table-responsive table-hover table-bordered align-middle" id="scheduleTable">
                <thead class="table-navy" id="inspectionTableHeader">
                    <tr class="text-center align-middle">

                        <th>Created</th>
                        <th>Scheduled Inspection Status</th>
                        <th>Order No.</th>
                        <th>Scheduled Date</th>
                        <th class="d-none d-md-table-cell">Preferred Date</th>
                        <th>Owner</th>
                        <th class="d-none d-md-table-cell">Client Acknowledgement</th>
                        <th class="d-none d-md-table-cell">Inspector Acknowledgement</th>
                        <th class="d-none d-md-table-cell">Chief FSES Acknowledgement</th>
                        <th class="d-none d-md-table-cell">Fire Marshall Acknowledgement</th>
                        
                        <th class="d-none d-md-table-cell">Assigned Inspector</th>
                        <th class="d-none d-md-table-cell">Checklist Type</th>
                        <th class="d-none d-md-table-cell">FSIC Purpose</th>
                        <th class="d-none d-md-table-cell">Nature of Inspection</th>
                        <th class="d-none d-md-table-cell">Remarks</th>
                        <th class="d-none d-md-table-cell">Has defects</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody id="inspectionTableBody" class="overflow-y-scroll">
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Loading inspection schedules...
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>


</body>

</html>
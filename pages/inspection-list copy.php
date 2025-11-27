<!DOCTYPE html>
<?php
include_once "../includes/_init.php";
//define("$GLOBALS['USER_LOGGED']",$_SESSION['user_id']);

if(!isLoggedin()){
    header("location: ../?not_allowed_there_buddy");
    die();
};
// Fetch schedules with checklist join
$filter = [];

if(isClient()){
    $filter = array_merge($filter,['g.owner_id' => $GLOBALS['USER_LOGGED'] ]);
    
}
else if (isInspector()){
    $filter = array_merge($filter,['ins.to_officer' => getUserInfo($GLOBALS['USER_LOGGED'], "full_name") ]);   
}
else{
    $filter = array_merge($filter,[1 => 1]);
}

$hasSignature = false;

$schedules = select_join(
    ['inspections ins'],             // table alias
         ['ins.started_at'
         ,'ins.completed_at'
         ,'ins.status'
         ,'CASE WHEN ins.hasdefects = "1" THEN "YES" WHEN ins.hasdefects = "0" THEN "NO" END as hasdefects'
         ,'ins.defects_details'
         ,'i.fullname as inspector_name'
         ,'u.fullname as client_name' 
         ,'i.role as inspector_role'
         ,'i.contact_no as inspector_contact_no'
         ,'u.contact_no as owner_contact_no'
         ,'c.fsed_code'
         ,'c.title as fsed_code_title'
         ,'c.description as fsed_code_description'
         ],           
    [        
        // joins
        [
            'table' => 'inspection_schedule is',
            'on' => 'ins.schedule_id = is.schedule_id',
            'type' => 'INNER'
        ],
        [
            'table' => 'checklists c',
            'on' => 'ins.checklist_id = c.checklist_id',
            'type' => 'LEFT'
        ],
        [
            'table' => 'general_info g',
            'on' => 'ins.gen_info_id = g.gen_info_id',
            'type' => 'LEFT'
        ],
        [
            'table' => 'users u',
            'on' => 'g.owner_id = u.user_id',
            'type' => 'LEFT'
        ],
        [
            'table' => 'users i',
            'on' => 'ins.inspector_id = i.user_id',
            'type' => 'LEFT'
        ]
    ],
    $filter,
    ['ins.scheduled_date' => 'DESC']             // order by
);
 $remarks = null;

/*-----------*/
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
                
                if($action == 'cancel'){
                        update_data("inspection_schedule",
                                        [ "inspection_sched_status" => "Cancelled",
                                          "remarks" => "[Cancelled]"
                                        ],
                                        ["schedule_id" => $sid]);
                    
                }
            }
    /*-----------*/

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>List of Establishments</title>
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
    </style>
</head>

<body>
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
                <input type="text" name="schedule_id" id="schedule_id">

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
    <!---->

    <!-- Fullscreen Offcanvas -->
    <div class="offcanvas offcanvas-bottom h-100" tabindex="-1" id="signatureOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Draw Signature
                <?php if(esignature($GLOBALS['USER_LOGGED']) !== NULL ){
                        $hasSignature = true;
                    ?>
                <!-- Thumbnail -->
                <img src="../assets/signatures/<?php echo esignature($GLOBALS['USER_LOGGED']);?>" alt="Signature" height="50px" data-bs-toggle="modal" data-bs-target="#signaturePreviewModal">
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

    <div class="container-fluid mt-4" style="margin-top: 100px">

        <h4 class="mb-3">Inspection Schedules
            <a href="?page=ins_sched&calendar_view" class="btn btn-gold me-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3 text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
                    <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                </svg>
                <span class="text-navy d-none d-lg-inline">Calendar View</span>
            </a>

            <a href="#" class="btn btn-gold me-1 add-signature" data-user="<?php echo $_SESSION['user_id']; ?>" data-role="<?php echo $_SESSION['role']; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                    <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z" />
                </svg>
                <span class="text-navy d-none d-lg-inline"><?php echo $hasSignature ? 'Update' : 'Set New'; ?> E-Signature</span>

            </a>



        </h4>
        <div class="table-responsive overflow-y-scroll">
            <table class="table table-striped table-responsive table-hover table-bordered align-middle" id="scheduleTable" style="width:150%">
                <thead class="table-navy">
                    <tr class="text-center align-middle ">

                        <th>Action</th>
                        <th>Order No.</th>
                        <th>Scheduled Date</th>
                        <th>Owner</th>
                        <th>Client Acknowledgement</th>
                        <?php if(isAdmin() && isClient()){?>
                        <th>Officer</th>
                        <?php } ?>
                        <th>Establishment</th>
                        <th>Checklist Type</th>
                        <th>Status</th>
                        
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody class="overflow-y-scroll">
                    <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $item): ?>
                    <tr>
                        <td class="text-center">
                            <div class="d-flex m-auto">
                                <?php
                                // 🔹 Determine current user's role label
                                $roleLabel = null;
                                if (isClient()) {
                                    $roleLabel = 'Client';

                                    // Client: Show Reschedule button if not yet acknowledged
                                    if ($item['HasClientAck'] != 'Y') { ?>
                                <a href="#" class="ack page-button btn btn-navy" data-btnId="<?= $item['schedule_id'] ?>" data-sched-id="<?= $item['schedule_id'] ?>" data-bs-toggle="offcanvas" data-bs-target="#reschedCanvas" aria-controls="reschedCanvas">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-repeat-1 m-2" viewBox="0 0 16 16">
                                        <path d="M11 4v1.466a.25.25 0 0 0 .41.192l2.36-1.966a.25.25 0 0 0 0-.384l-2.36-1.966a.25.25 0 0 0-.41.192V3H5a5 5 0 0 0-4.48 7.223.5.5 0 0 0 .896-.446A4 4 0 0 1 5 4zm4.48 1.777a.5.5 0 0 0-.896.446A4 4 0 0 1 11 12H5.001v-1.466a.25.25 0 0 0-.41-.192l-2.36 1.966a.25.25 0 0 0 0 .384l2.36 1.966a.25.25 0 0 0 .41-.192V13h6a5 5 0 0 0 4.48-7.223Z" />
                                        <path d="M9 5.5a.5.5 0 0 0-.854-.354l-1.75 1.75a.5.5 0 1 0 .708.708L8 6.707V10.5a.5.5 0 0 0 1 0z" />
                                    </svg>
                                    <span class="d-none d-lg-block">Reschedule</span>
                                </a>
                                <?php }
                                } elseif (isRecoApprover() || isFireMarshall()) {
                                    $roleLabel = 'Recommending Approver';
                                } elseif (isApprover()) {
                                    $roleLabel = 'Final Approver';
                                } elseif (isInspector()) {
                                    $roleLabel = 'Inspector';
                                }

                                // 🔹 Cancel Button (Admin only, if not yet cancelled)
                                if ($item['inspection_sched_status'] != 'Cancelled') {
                                    if (isAdmin()) { ?>
                                <a href="?page=ins_sched&action=cancel&sched_id=<?= $item['schedule_id'] ?>" class="page-button btn btn-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban m-auto align-middle" viewBox="0 0 16 16">
                                        <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0" />
                                    </svg>
                                    <span class="d-none d-lg-block">Cancel Schedule</span>
                                </a>
                                <?php }
                                } else { ?>
                                <div class="badge bg-danger p-2 d-flex m-auto shadow bg-opacity-75">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle m-auto d-inline" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                        <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8" />
                                    </svg>
                                    <span class="d-none ms-1 my-2 d-lg-inline">
                                        Schedule was
                                    </span>
                                    <span class="ms-1 my-2 d-inline">
                                        Cancelled
                                    </span>
                                </div>
                                <?php }

                                // 🔹 Acknowledge Button (if applicable and not yet signed)
                                if ($roleLabel && !isSignedBy($roleLabel, $item['schedule_id']) && $item['inspection_sched_status'] != "Cancelled") { ?>
                                <button type="button" data-btnId="<?= $item['schedule_id'] ?>" class="ack page-button btn btn-success ack-btn" data-sched-id="<?= $item['schedule_id'] ?>" data-role="<?= $roleLabel ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                    <span class="d-none ms-1 my-2 d-lg-block">
                                        Acknowledge as <?= htmlspecialchars($roleLabel); ?>
                                    </span>
                                </button>
                                <?php }

                                // 🔹 Inspector-only Actions (Client already signed)
                                if (isInspector() && isSignedBy('Client', $item['schedule_id']) && $item['inspection_sched_status'] != "Cancelled") {
                                    $geninfo = getGenInfo($item['gen_info_id']);
                                    $address = $geninfo['location_address'];
                                    $lat = $geninfo['location_lat'];
                                    $lng = $geninfo['location_lng'];
                                ?>
                                <a href="../pages/map.php?address=<?= urlencode($address) ?>&lat=<?= $lat ?>&lng=<?= $lng ?>" class="btn btn-gold page-button">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                                        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10" />
                                        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                                    </svg>
                                    <span class="d-none ms-1 my-2 d-lg-block">
                                        View Location
                                    </span>
                                </a>

                                <a href="#" id="startInspectionBtn" data-schedule-id="<?= $item['schedule_id'] ?>" class="btn btn-gold page-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0" />
                                    </svg>
                                    <span class="d-none ms-1 my-2 d-lg-block">
                                        Start Inspection
                                    </span>
                                </a>
                                <?php }

                                // 🔹 Print Button (if not cancelled)
                                if ($item['inspection_sched_status'] != 'Cancelled') { ?>
                                <a href="../pages/print_inspection_order.php?id=<?= $item['schedule_id'] ?>" target="_blank" class="page-button btn btn-gold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1" />
                                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1" />
                                    </svg>
                                    <span class="d-none d-lg-block">
                                        PRINT FSED-9F
                                    </span>
                                </a>
                                <?php } ?>
                            </div>
                        </td>

                        <td><?php echo htmlspecialchars($item['order_number'] ?? ' ') ; ?></td>
                        <td><?php echo htmlspecialchars($item['scheduled_date' ?? ' ']) ; ?></td>
                        <td><?php echo htmlspecialchars($item['owner_name'] ?? ' ') ; ?></td>
                        
                        <td><?php 
                                $msg = null; $n = null;
                                $n = getUserInfo($item['AckByClient_id']);
                                $msg = htmlspecialchars($item['HasClientAck'] ?? '') == 'Y' ? 'Acknowledged by' : 'Pending Acknowledgement from' ; 
                                echo $msg . ' ' . $n;
                            ?>
                        </td>
                        <?php if(isAdmin() && isClient()){?>
                        <td><?php echo htmlspecialchars($item['to_officer'] ?? ' ') ; ?></td>
                        <?php } ?>
                        <td><?php echo htmlspecialchars($item['proceed_instructions'] ?? ' ') ; ?></td>
                        <td><?php echo htmlspecialchars($item['title'] ?? ' ') ?></td>
                        
                        <?php
                         $remarks=null;
                            $remarks = $item['remarks'] ?? '';
                            $hasdefects = null;
                            $hasdefects = stripos($remarks, 'Has defects') !== false; // case-insensitive search
                            
                        ?>
                        <td class="<?php 
                           
                            $sched_stat = htmlspecialchars($item['inspection_sched_status']); 
                            $class = ''; 

                            if ($sched_stat === 'Completed') {
                                if($hasdefects){
                                    $class = 'bg-success bg-opacity-50 text-gold';    
                                }
                                else{
                                    $class = 'bg-success bg-opacity-75 text-light';    
                                }
                                
                            }
                            elseif ($sched_stat === 'Rescheduled') {
                                $class = 'bg-gold text-dark';
                            } elseif ($sched_stat === 'Cancelled') {
                                $class = 'bg-danger text-light';
                            }

                            echo $class;
                        ?>">
                                                <?php 
                        $remarks_text = null;
                        if (isAdmin()) {
                            if ($sched_stat === "Rescheduled") {
                                $remarks_text = "Establishment Owner Requested a reschedule to " . 
                                                htmlspecialchars($item['preferredSchedule']) . 
                                                " due to: " . htmlspecialchars($item['rescheduleReason']); 
                                ?>
                                                <a href="?page=ins_sched&action=reschedule&sched_id=<?= $item['schedule_id'] ?>&remarks=<?= urlencode($remarks_text) ?>" class="ack btn btn-gold page-button">
                                                    <small class="text-small small">APPROVE?</small>
                                                </a>
                                                <?php
                            }
                        }

                        echo htmlspecialchars($sched_stat);
                        ?>
                        </td>

                        <?php
                    
                            $schedReason = $remarks; 
                            $class = $hasdefects ? 'bg-gold text-dark' : '';
                            ?>

                        <td class="<?= $class ?>">
                            <?= !empty($schedReason) ? nl2br(htmlspecialchars($schedReason)) : '' ?>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No inspection schedules found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="../assets/js/main.js"></script>


    <script>
        $(document).ready(function() {
            // When clicking "Reschedule"
            $(document).on("click", ".resched-btn", function() {
                const schedId = $(this).data("sched-id");
                $("#schedule_id").val(schedId); // populate hidden input
            });

            // Handle form submit
            $("#reschedForm").on("submit", function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: "../includes/request_reschedule.php", // <-- endpoint for backend handling
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(res) {
                        if (res.success) {
                            showAlert("Reschedule request submitted successfully!");
                            $("#reschedForm")[0].reset();
                            const offcanvasEl = bootstrap.Offcanvas.getInstance($("#reschedCanvas")[0]);
                            offcanvasEl.hide();

                        } else {
                            alert("Error: " + res.message);
                        }
                    },
                    error: function() {
                        alert("An error occurred while submitting the request.");
                    }
                });
            });

            $("#startInspectionBtn").on("click", function() {

                let scheduleId = $(this).data("schedule-id");
                console.log(scheduleId);
                $.post("../includes/save_inspection.php", {
                    schedule_id: scheduleId
                }, function(res) {
                    if (res.success) {

                        // redirect with GET params
                        window.location.href = "?page=strt_ins&sched_id=" + scheduleId + "&insp_id=" + res.inspection_id;
                    } else {
                        alert(res.message);
                    }
                }, "json");
            });



        });
    </script>

</body>

</html>
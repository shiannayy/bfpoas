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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
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
  

    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-2 flex-wrap px-2 mb-2">
            <a href="?page=ins_sched&calendar_view" class="btn btn-gold btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-calendar3 text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path
                        d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
                    <path
                        d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                </svg>
                <span class="text-navy d-none d-lg-inline">Calendar View</span>
            </a>

            <?php if( !isDataEntry() && !isInspector() ){ ?>
            <a href="#" class="btn btn-gold btn-sm add-signature position-relative"
                data-user="<?php echo $_SESSION['user_id']; ?>" data-role="<?php echo $_SESSION['role']; ?>">
                <?php if(!$hasSignature){?>
                <span class="px-1 bg-danger border-rounded rounded-2 position-absolute top-0 start-100 translate-middle"
                    style="font-size:8pt;">
                    <span class="text-uppercase text-light">Sign</span>
                </span>
                <?php  } ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen"
                    viewBox="0 0 16 16">
                    <path
                        d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z" />
                </svg>
                <span class="text-navy d-none d-lg-inline">
                    <?php echo $hasSignature ? 'Update' : 'Set New'; ?> E-Signature
                </span>
            </a>
            <?php } ?>
            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleArchived"
                    data-status-toggle="Archived">
                <label class="form-check-label" for="toggleArchived">Show Archived</label>
            </div>

            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleCompleted"
                    data-status-toggle="Completed">
                <label class="form-check-label" for="toggleCompleted">Show Completed</label>
            </div>

            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleCancelled"
                    data-status-toggle="Cancelled">
                <label class="form-check-label" for="toggleCancelled">Show Cancelled</label>
            </div>


            <input type="text" id="SearchInsSched" class="form-control form-control-sm w-auto" placeholder="Search...">

            <a href="?page=sched_ins" class="ms-auto btn btn-gold btn-sm d-none btn-new-schedule">
                + New Schedule
            </a>
        </div>

<div class="row">
    <?php 
    $ackCheck = [
        'Client' => [ ['ins.HasClientAck' => 'N'] ],
        'Inspector' => [ ['ins.HasInspectorAck' => 0] ],
        'Chief FSES' => [ ['ins.hasRecommendingApproval' => 0] ],
        'Fire Marshall' => [ ['ins.hasFinalApproval' => 0] ],
    ];
    
    foreach($ackCheck as $role => $conditions){ 
        $where = $conditions[0];
        
        $joins = [
                    [
                    'table' => 'general_info gi',
                    'on' => 'ins.gen_info_id = gi.gen_info_id',
                    'type' => 'LEFT'
                    ]
        ];
        $insSched = select_join(['inspection_schedule ins']
                            , 
                            ['ins.order_number',
                              'ins.scheduled_date',
                              'ins.schedule_time',
                              'gi.owner_name',
                              'gi.location_of_construction',
                              'ins.to_officer',
                              'ins.inspection_sched_status',
                              'ins.hasInspectorAck',
                              'ins.hasClientAck',
                              'ins.hasRecommendingApproval',
                              'ins.hasFinalApproval',
                            ],
                             $joins, 
                             $where, 
                            ['ins.created_at'=>'DESC', 'ins.scheduled_date'=>'ASC'],
                            1000);
    ?>
    <div class="col-3 pending-inspections-container list-group vh-100 overflow-y-auto" style="max-height: 80vh;">
        <a class="list-group-item bg-navy">
            <strong class="text-gold"><?= $role ?></strong>
        </a>
        <?php 
        if(empty($insSched)){ 
        ?>
            <div class="m-3 text-muted">
                No Scheduled inspections found.
            </div>
        <?php 
        } 
        else {
            
            foreach($insSched as $sched):  
                $borderClass = null;
                switch($role){
                    case 'Client':
                        $borderClass = ($sched['hasClientAck'] == 'Y') ? 'border-success' : 'border-danger';
                        break;
                    case 'Inspector':
                        $borderClass = ($sched['hasInspectorAck'] == 1) ? 'border-success' : 'border-danger';
                        break;
                    case 'Chief FSES':
                        $borderClass = ($sched['hasRecommendingApproval'] == 1) ? 'border-success' : 'border-danger';
                        break;
                    case 'Fire Marshall':
                        $borderClass = ($sched['hasFinalApproval'] == 1) ? 'border-success' : 'border-danger';
                        break;
                }
            ?>
            <div href="#" class="list-group-item">
                <div class="d-flex w-100 align-items-center justify-content-between">
                    <strong class="mb-1 text-uppercase"><?= $sched['owner_name'] === null ? "NONE INDICATED" : $sched['owner_name']  ?></strong>
                    <div class="dropdown">
                        <button class="btn btn-navy dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"> ... </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </div>
                </div>
                <small><?= date('M d, Y', strtotime($sched['scheduled_date'])) . " at " . date('h:i A', strtotime($sched['schedule_time'])) ?></small>
                <div class="col-10 mb-1 small">
                    <?= $sched['to_officer'] ?>
                </div>
            </div>
        <?php 
            endforeach;
        } //end else
        ?>
    </div>
    <?php } ?>
</div>


        <div class="d-none table-responsive overflow-y-scroll px-0" style="min-height: 50vh">
            <div class="container pagination"></div>
            <table class="mx-0 w-100 table table-striped table-responsive table-hover table-bordered align-middle"
                id="scheduleTable">
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
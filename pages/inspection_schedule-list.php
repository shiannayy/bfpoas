<?php
include_once "../includes/_init.php";


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
        <div class="table-responsive overflow-y-scroll px-0" style="min-height: 50vh">
            <div class="container pagination"></div>
            <table class="mx-0 w-100 table table-striped table-responsive table-hover align-middle"
                id="scheduleTable">
                <thead class="table-navy" id="inspectionTableHeader">
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
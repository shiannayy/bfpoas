<?php
include_once "../includes/_init.php";
enforceRoleAccess(['client']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Safety Inspection System</title>

    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <style>
        .page-btn {
            min-height: 110px;
        }

        .w-100 {
            width: 100%;
        }

        .fine-print {
            font-size: 0.7rem;
            line-height: 1;
            margin: 0;
            padding: 0;

        }

        .fine-print span {
            display: block;
            /* each on its own line */
            line-height: 1;
            /* tight line spacing */
            margin: 0;
            /* no gaps */
            padding: 0;
        }
    </style>
</head>

<body id="main_container">
    <div class="container-fluid">

        <?php 
        include_once "../pages/modals.php";
        include_once "../includes/_nav_client.php"; ?>
        <div class="row g-1">
            <div class="col-0 bg-navy col-lg-2 col-md-2 d-none d-sm-none d-md-inline d-lg-inline p-0 position-fixed vh-100 start-0 top-0 overflow-y-auto">
                <!-- Navigation -->
                 <?php include_once "app_nav.php"; ?>
                 <!-- Navigation -->
            </div>
            <div class="col-12 col-lg-10 col-md-10 offset-lg-2 offset-md-2 ps-3 pe-3 overflow-y-auto h-100" style="padding-top: 65px;">
                <!-- Main Content -->
                 <?php if(!isset($_GET['page'])){ ?>
                <div class="container-fluid mt-2 mb-3">
                    <h3> Hi, Welcome <?= getUserInfo($_SESSION['user_id']) ?> </h3>
                    <hr>
                    <div class="row g-1">
                       <!-- Start ROW -->


                        <?php $estList = select_join(
                                        ["general_info gi"],
                                        [
                                            "gi.gen_info_id",
                                            "gi.building_name",
                                            "gi.location_of_construction",
                                            "gi.owner_name",
                                            "gi.owner_contact_no",
                                            "msl.address",
                                            "msl.lat",
                                            "msl.lng",
                                            "gi.created_at"
                                        ],
                                        [
                                            [
                                                "type"  => "INNER",
                                                "table" => "map_saved_location msl",
                                                "on"    => "gi.loc_id = msl.loc_id"
                                            ]
                                        ],
                                        ["owner_id" => $_SESSION['user_id'] ],
                                        ["msl.date_added" => "DESC"]
                                    );
                            $estIdList = [];
                        if(!empty($estList)){ 
                            
                            ?>
                        <div class="col-12">

                        <span class="bg-navy text-light d-flex align-items-center flex-shrink-0 p-3 text-decoration-none border-bottom"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building" viewBox="0 0 16 16">
                                <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                                <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/>
                                </svg>
                            <span class="ms-1 fs-5 fw-semibold">Establishments</span> 
                        </span>
                                    <div class="list-group m-0">
                                    <?php 
                                    foreach($estList as $e){ 
                                        //populate my current establishment id
                                        $estIdList[] += $e['gen_info_id'];
                                        ?>
                                        <a target="_blank" href="../pages/map.php?address=<?= $e['address'] ?>&lat=<?= $e['lat']?>&lng=<?= $e['lng'] ?>" class="list-group-item list-group-item-action  py-3 lh-sm" aria-current="true"> 
                                            <div class="d-flex w-100 align-items-center justify-content-between">
                                                <strong class="mb-1"><?= $e['building_name'] ?></strong> <small><?= toWordDate($e['created_at']) ?></small> </div> 
                                                <div class="col-10 mb-1 small"><?= $e['location_of_construction'] ?></div> 
                                            </a>
                                    <?php } ?>
                                    </div>
                        </div>
                        <?php } ?>   

                        <?php 
                        //for inspection schedule
                        if(!empty($estIdList)){ ?>
                            <div class="col-12">

                        <span class="bg-navy text-light d-flex align-items-center flex-shrink-0 p-3 text-decoration-none border-bottom"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building" viewBox="0 0 16 16">
                                <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                                <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/>
                                </svg>
                            <span class="ms-1 fs-5 fw-semibold">Scheduled Inspections</span> 
                        </span>
                           
                        <?php foreach($estIdList as $g ) {
                                $mySched = select("view_inspection_status",['gen_info_id' => $g ]);
                                if(!empty($mySched)){
                                    foreach($mySched as $i){ ?>
                                         <span class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true"> 
                                            <div class="d-flex w-100 align-items-center justify-content-between">
                                                <strong class="mb-1"><?= $i['order_number'] ?></strong> 
                                                <small class="me-3"><?= toWordDate($i['sched_updated_at']) ?></small>
                                             </div> 

                                                <div class="col-10 mb-1 small">
                                                    <small class="text-small">
                                                        Scheduled on <?= formatDateTime($i['scheduled_date']) ?>
                                                        for <?= $i['proceed_instructions'] ?>
                                                    </small> <br>
                                                    <i>Progress:</i>: <br>
                                                    <br>
                                                    <?= stepStatus([ $i['sched_client_ack'], $i[ 'sched_inspector_ack'], $i[ 'sched_recommending_approval'], $i[ 'sched_final_approval'], $i[ 'ins_recommending_approval'], $i[ 'ins_final_approval'], $i['ins_client_received'] ] ) ?>
                                                </div> 
                                        </span>
                                    <?php 
                                    }
                                }
                            } ?>
                            </div>
                        <?php 
                        $estIdList = [];
                        }
                        ?>
                        
                         <?php 
                        $logs = select("logs", ['user_id' => $_SESSION['user_id']],['log_ts'=>'DESC'], 5);
                        if(!empty($logs)) {
                        ?>
                        <div class="col-12">

                        <span class="bg-navy mb-0 text-light d-flex align-items-center flex-shrink-0 p-3 text-decoration-none border-bottom"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                                <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                                <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                            <span class="ms-1 fs-5 fw-semibold">Log Information</span> 
                        </span>
                                    <div class="list-group m-0">
                                    <?php 
                                    foreach($logs as $l){ ?>
                                         <span class="list-group-item list-group-item-action py-3 lh-sm" aria-current="true"> 
                                            <div class="d-flex w-100 align-items-center justify-content-between">
                                                <strong class="mb-1">Logged In</strong> <small><?= toWordDate($l['log_ts']) ?></small> </div> 
                                                <div class="col-10 mb-1 small">You Logged in <?= formatDateTime($l['log_ts']) ?></div> 
                                        </span>
                                    <?php } ?>
                                    </div>
                        </div>
                        <?php } ?>


                        <!-- end ROW -->
                    </div>

                </div>
                    <?php }
                    else{
                        switch($_GET['page']){
                            case 'new_est':
                                require_once "../pages/gen_info.php";
                                break;
                            case 'est_list':
                                require_once "../pages/establishment-list.php";
                                break;
                            case 'edit_gen_info':
                                require_once "../pages/edit_gen_info.php";
                                break;
                            case 'ins_sched':
                                require_once "../pages/inspection_schedule.php";
                                break;
                            case 'ins_list':
                                require_once "../pages/inspection-list.php";
                                break;
                            case 'defects':
                                require_once "../pages/defects.php";
                                break;
                            default: include_once "app_nav.php";
                        }
                    }
                    ?>
                 <!-- Main Content -->                
            </div>
        </div>
    </div>

    <main class="d-none d-flex flex-column flex-grow-1">
        <!-- Static Top Navbar -->
        <?php include_once "../includes/_nav_client.php"; ?>
        <?php include_once "../includes/_footer.php"; ?>
    </main>
    

    <!-- Footer -->
    

    <!-- Bootstrap Bundle JS -->
    <script src="../assets/js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <?php
    if(!isset($_GET['page'])){ ?>
        <script src="../assets/js/_dashboard.js"></script>
        <script src="../assets/js/admin_charts.js"></script>
    <?php  }
    ?>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/send_mail.js"></script>
    <?php if(isset($_GET['page'])){
    $page = $_GET['page'] ?? '';
       $scripts = [
            'ins_sched' => 'inspection_sched.js',
            'strt_ins'  => 'start_inspection.js',
            'new_est' => 'gen_info.js',
            'edit_gen_info' => 'gen_info.js',
            'view_checklists' => 'checklist.js',
            'sched_ins' => 'fsed9f.js',
            'ins_list' => 'inspection.js',
        ];

        if(isset($_GET['view'])){
        $page = $_GET['view'];
            $scripts = [
                    'stat' => 'inspection_sched_stat.js',
                    'list' => 'inspection_sched.js'
            ];
        }

        if (array_key_exists($page, $scripts)) {
            echo '<script src="../assets/js/' . htmlspecialchars($scripts[$page]) . '"></script>';
        }
        
    }
    ?>


</body>

</html>
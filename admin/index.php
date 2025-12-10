<?php
include_once "../includes/_init.php";
enforceRoleAccess(['administrator']);
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
        include_once "../includes/_nav_admin.php"; ?>
        <div class="row">
            <div class="col-0 bg-navy col-lg-2 col-md-2 d-sm-none d-md-inline d-lg-inline p-0 position-fixed vh-100 start-0 top-0 overflow-y-auto">
                <!-- Navigation -->
                 <?php include_once "app_nav.php"; ?>
                 <!-- Navigation -->
            </div>
            <div class="col-12 col-lg-10 col-md-10 offset-lg-2 offset-md-2 ps-3 pe-3 overflow-y-auto h-100" style="padding-top: 65px;">
                <!-- Main Content -->
                 <?php if(!isset($_GET['page'])){ ?>
                <div class="container-fluid mt-2 mb-3">
                    <div class="row" id="counts"></div>
                </div>

                <div class="container-fluid p-0 mb-3">
                    <div class="row" id="chartsContainer">
                        
                    </div>
                </div>
                    <?php }
                    else{
                        switch($_GET['page']){
                            case 'new_user':
                                require_once "../pages/add-user.php";
                                break;
                            case 'new_est':
                                if(isset($_SESSION['gen_info_id'])){
                                    unset($_SESSION['gen_info_id']);
                                }
                                require_once "../pages/gen_info.php";
                                break;
                            case 'est_list':
                                require_once "../pages/establishment-list.php";
                                break;
                            case 'edit_gen_info':
                                require_once "../pages/gen_info.php"; break;
                            case 'sched_ins':
                                require_once "../pages/fsed_9F.php";
                                break;
                            case 'ins_sched':
                                require_once "../pages/inspection_schedule.php";
                                break;
                            case 'ins_list':
                                require_once "../pages/inspection-list.php";
                                break;
                            case 'user_list':
                                require_once "../pages/users.php";
                                break;
                                
                            case 'view_checklists':
                                require_once "../pages/checklists.php";
                                break;
                            case 'map_loc':
                                include_once "../pages/map.php";
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
        <?php include_once "../includes/_nav_admin.php"; ?>
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
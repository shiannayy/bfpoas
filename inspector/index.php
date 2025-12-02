<?php include_once "../includes/_init.php";?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Inspection System</title>

    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <style>
         #userInfoBadge.collapse-horizontal {
            transition: width 0.3s ease-in-out;
        }
        .page-btn {
            min-height: 110px;
        }

        .w-100 {
            width: 100%;
        }

        .fine-print {
            font-size: 0.6rem;
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

        .done-inspection {
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1050;
            /* stay on top */
        }

        .done-inspection.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        
        <?php include_once "../includes/_nav_ins.php";
        if(!isset($_GET['page'])){
            include_once "app_nav.php";
            ?>
            <!-- Static Top Navbar -->
         <div class="container">
            <div class="row" id="counts">

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
                case 'strt_ins':
                    require_once "../pages/start_inspection.php";
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
        
    </main>
    <!-- Footer -->
    <?php include_once "../includes/_footer.php"; ?>

    <!-- Core JS -->
    <script src="../assets/js/jquery.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script> -->
<!--    <script src="../assets/js/bootstrap.bundle.min.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin_charts.js"></script>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/_dashboard.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script> -->
 
    <?php if(isset($_GET['page'])){
    $page = $_GET['page'] ?? '';
        $scripts = [
            'ins_sched' => 'inspection_sched.js',
            'strt_ins'  => 'start_inspection.js',
            'ins_list'  => 'inspection.js'
        ];

        if (array_key_exists($page, $scripts)) {
            
            echo '<script src="../assets/js/' . htmlspecialchars($scripts[$page]) . '"></script>';
        }
    }
    ?>



</body>

</html>
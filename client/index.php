<?php
include_once "../includes/_init.php";


    enforceRoleAccess(['client']);

?>
<!DOCTYPE html>
<html lang="en">

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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <!-- Static Top Navbar -->
        <?php 
        include_once "../pages/modals.php";
        include_once "../includes/_nav_client.php";
        
        if(!isset($_GET['page'])){
            include_once "app_nav.php";
        }
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
        
    </main>
    <!-- Footer -->
    <?php include_once "../includes/_footer.php"; ?>

    <!-- Bootstrap Bundle JS -->
    <script src="../assets/js/jquery.js"></script>
<!--    <script src="../assets/js/bootstrap.bundle.min.js"></script>-->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <!--  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>-->
    <!--   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>-->
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/navbar.js"></script>
    
   
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

        if (array_key_exists($page, $scripts)) {
            echo '<script src="../assets/js/' . htmlspecialchars($scripts[$page]) . '"></script>';
        }
    }
    ?>

</body>

</html>
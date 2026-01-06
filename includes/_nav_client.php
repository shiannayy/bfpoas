<?php include_once "../includes/_init.php";
if (isset($_SESSION['user_id'])) {
    $logged_user_info = [
        "logged_in" => isLoggedIn(),
        "user" => [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['name'] ?? "Unknown User",
            "role" => $_SESSION['role'] ?? null,
            "subrole" => $_SESSION['subrole'] ?? null
        ]
    ];
}
?>
<link rel="stylesheet" href="../assets/css/color_pallette.css">
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="../admin/">
            <img src="../assets/img/bfp-logo.png" width="40" class="img-fluid" height="32" alt="">
            <img src="../assets/img/bagong-pilipinas.png" width="40" class="img-fluid" height="32" alt="">
            <img src="../assets/img/tagasalbar.png" width="40" class="img-fluid" height="32" alt="">
            <span class="fw-bold d-inline ms-2">BFP Oas</span>
        </a>

        <div class="d-flex align-items-center d-lg-none ms-auto">

            <!-- <button id="notifications" class="nav-link d-lg-none me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bell"
                    viewBox="0 0 16 16">
                    <path
                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                </svg>
            </button> -->

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation -->
            <ul class="navbar-nav nav-pills ms-auto me-3">
                <li class="nav-item">
                    <a class="nav-link" href="../admin/" data-link="home"><svg xmlns="http://www.w3.org/2000/svg"
                            width="16" height="16" fill="currentColor" class="bi bi-house-door-fill"
                            viewBox="0 0 16 16">
                            <path
                                d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5" />
                        </svg></a>
                </li>

                       <?php 
                         $role = $_SESSION['rolelabel'];
                            foreach($roleButtons[$role] as $btns){
                                echo navBarBtn($btns['link'],$btns['icon'],$btns['label']);
                            }
                         ?>
                 <li class="nav-item">
                    <a class="nav-link" href="?page=map_loc" data-link="locator"><svg xmlns="http://www.w3.org/2000/svg"
                            width="20" height="20" fill="currentColor" class="bi bi-map" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M15.817.113A.5.5 0 0 1 16 .5v14a.5.5 0 0 1-.402.49l-5 1a.5.5 0 0 1-.196 0L5.5 15.01l-4.902.98A.5.5 0 0 1 0 15.5v-14a.5.5 0 0 1 .402-.49l5-1a.5.5 0 0 1 .196 0L10.5.99l4.902-.98a.5.5 0 0 1 .415.103M10 1.91l-4-.8v12.98l4 .8zm1 12.98 4-.8V1.11l-4 .8zm-6-.8V1.11l-4 .8v12.98z" />
                        </svg>
                        <small class="d-inline d-lg-none d-md-none">BUILDING LOCATOR</small>
                    </a>
                </li>
                <li class="nav-item">
                    <a id="inspectorLogout" class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                    <?= getIcon("logout") ?>   
                    <small class="d-inline d-lg-none d-md-none">LOGOUT</small>
                    </a>
                </li>
                <li class="nav-item">
                    <a id="DarkMode" href="#" class="nav-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-moon-stars-fill" viewBox="0 0 16 16">
                            <path
                                d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
                            <path
                                d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z" />
                        </svg>
                        <small class="d-inline d-lg-none d-md-none">DARK THEME</small>
                    </a> 
                </li>

                <li class="nav-item d-none d-lg-inline text-center">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" data-bs-toggle="collapse" data-bs-target="#userInfoBadge"
                            aria-expanded="false" aria-controls="userInfoBadge"
                            class="badge rounded-circle bg-navy-dark p-2 border border-4 border-gold shadow">
                            <?php echo strtoupper($logged_user_info['user']['name'][0]); ?>
                        </button>

                        <div class="collapse collapse-horizontal" id="userInfoBadge">
                            <div class="d-flex align-items-center gap-2 ps-1">
                                <span class="badge badge-sm bg-gold text-navy-dark">
                                    <?= strtoupper($logged_user_info['user']['name']); ?>
                                </span>

                                <b class="text-light">|</b>

                                <small class="badge badge-sm bg-navy text-secondary fw-light">
                                    <?= strtoupper($logged_user_info['user']['role']); ?>
                                </small>

                                <b class="text-secondary">|</b>

                                <small class="badge badge-sm bg-navy text-secondary fw-light">
                                    <?= strtoupper($logged_user_info['user']['subrole']); ?>
                                </small>
                            </div>
                        </div>

                    </div>

                </li>
                <li class="nav-item d-inline  d-lg-none">
                    <span class="badge badge-sm bg-gold text-navy-dark">
                        <?php  echo strtoupper( $logged_user_info['user']['name']); ?>
                    </span>
                    <b class="text-light">|</b>
                    <span class="badge badge-sm bg-navy text-secondary">
                        <?php  echo strtoupper( $logged_user_info['user']['role']); ?>
                    </span>

                    <span class="badge badge-sm bg-navy text-secondary">
                        <?php  echo strtoupper( $logged_user_info['user']['subrole']); ?>
                    </span>
                </li>

            </ul>

            <!-- Philippine Standard Date & Time -->
            <span class="navbar-text text-light">

                <img src="../assets/img/philippines.png" width="20" alt="" class="img-fluid">
                <span class="py-3"><span class="ph-time"></span></span>
            </span>
        </div>
    </div>
</nav>

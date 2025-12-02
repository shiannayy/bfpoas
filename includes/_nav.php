<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand no-focus" href="#">
            <img src="../assets/img/bfp-logo.png" width="40" class="img-fluid" height="32" alt="">
            <img src="../assets/img/bagong-pilipinas.png" width="40" class="img-fluid" height="32" alt="">
            <img src="../assets/img/tagasalbar.png" width="40" class="img-fluid" height="32" alt="">
            <span class="fw-bold d-none d-sm-block">BFP Inspection System</span>
        </a>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation -->
            <ul class="navbar-nav nav-pills ms-auto me-3">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-link="home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/map.php" data-link="locator">Building Locator</a>
                </li>
            </ul>

            <!-- Philippine Standard Date & Time -->
            <span class="navbar-text text-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock"
                    viewBox="0 0 16 16">
                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z" />
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0" />
                </svg>
                <img src="../assets/img/philippines.png" width="20" alt="" class="img-fluid">
                <span class="ph-time"></span>
            </span>
        </div>

        <!-- Login Button (Always Visible) -->
        <a id="inspectorLogin" class="btn btn-outline-gold ms-auto me-2" href="#" data-bs-toggle="modal"
            data-bs-target="#loginModal">
            Login
        </a>

        <button class="navbar-toggler btn btn-outline-gold text-gold" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
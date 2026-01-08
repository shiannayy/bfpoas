<!-- Sidebar Navigation (Bootstrap 5.3 Collapse) -->
<nav id="sidebarNav" class="postition-sticky start-0 bg-navy navbar-collapse collapse d-md-block border-end" style="height: 100vh; overflow-x: auto;">
    <div class="position-sticky mt-5 pt-5 ps-3 pe-3">
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <?php $active = (strpos($_SERVER['REQUEST_URI'], '?page=') === false) ? 'active' : '';  ?>
                <a class="nav-link <?= $active?> d-flex align-items-center" href="../admin/" data-link="home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door-fill me-2" viewBox="0 0 16 16">
                        <path d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5" />
                    </svg>
                    <span>DASHBOARD</span>
                </a>
            </li>
            <?php 
            $role = $_SESSION['rolelabel'];
            if (isset($roleButtons[$role])) {
                foreach($roleButtons[$role] as $btns){
                     $active = (strpos($_SERVER['REQUEST_URI'], $btns['link']) !== false) ? 'active' : ''; 
                    if($btns['icon'] === 'divider'){
                        echo '<li class="divider"><hr class="my-0 text-light"></li>';
                    }
                    else{
                   ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?= $active ?>" href="<?= htmlspecialchars($btns['link']) ?>">
                            <?= getIcon(htmlspecialchars($btns['icon'])) ?>
                            <span class="ms-2"><?= htmlspecialchars($btns['label']) ?></span>
                        </a>
                    </li>
                    <?php } ?>
               <?php 
                }
            }
            ?>
            <li class="nav-item">
                    <a id="inspectorLogout" class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                    <?= getIcon("logout") ?>   
                    <small class="ms-2">Logout</small>
                    </a>
                </li>
        </ul>
    </div>
</nav>

<!-- Toggle button for mobile sidebar -->
<button class="btn btn-link d-md-none position-fixed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarNav" aria-controls="sidebarNav" aria-expanded="false" aria-label="Toggle navigation" style="top: 75px; left: 10px; z-index: 101;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
    </svg>
</button>
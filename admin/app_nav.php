 <div class="container" style="margin-top:100px;">
     <div class="row">
         <div class="col-12 col-sm-12 col-lg-12 col-md-12 mb-3">
             <div class="card border-0 shadow">
                 <div class="card-header bg-navy text-light">
                     <h4 class="card-title fw-bold">Welcome <?= getUserInfo($_SESSION['user_id']);?></h4>
                 </div>
                 <div class="card-body container-fluid">
                     <div class="row">
                         <?php 
                         $role = $_SESSION['rolelabel'];
                            foreach($roleButtons[$role] as $btns){
                                echo appNavBtn($btns['link'],$btns['icon'],$btns['label']);
                            }
                         ?>
                     </div>

                 </div>
             </div>
         </div>
         
     </div>
 </div>
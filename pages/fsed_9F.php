<?php
include_once "../includes/_init.php";
//$_SESSION['user_id'] = $_SESSION['user_id'];
//if(!isset($_SESSION['OR_NUMBER'])){
//    $_SESSION['OR_NUMBER'] = config::REGION . randomNDigits(5);
//    $or_number = $_SESSION['OR_NUMBER'];
//}
//else {
//    $or_number = $_SESSION['OR_NUMBER'];
//}
//Set Inspection Order Number
$g=query("select max(schedule_id) + 1 as max_id FROM `inspection_schedule`");
$new_id = $g[0]['max_id'] ?? 1;
$formatted = str_pad($new_id, 4, "0", STR_PAD_LEFT);
?>


<body>
    <section class="d-none" id="infosection" data-info="FSED 9F"></section>
    <div class="container-fluid g-0" style="margin: 60px 0px 30px 0px;">

        <div class="row">
            <div class="col-12 card mx-0 px-0">

                <div class="card-header bg-navy-dark text-gold mx-0">
                    <h4 class="text-center fw-bold my-1">INSPECTION ORDER (FSED 9F)</h4>
                </div>
                <div class="card-body">
                    <form class="border p-4 rounded shadow-sm" id="fsed9F">

                        <!-- Header -->



                        <!-- Inspection Payment -->
                        <div class="row card g-1 mb-3 border-0">
                           <div class="col-md-12">
                               <h6 class="fw-bold">FIRE CODE FEES</h6>
                           </div>
                            <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control border border-1 border-danger" id="OR_Number" name="OR_Number" placeholder=" " value="" required>
                                        <label for="OR_Number">OR Number <span class="text-danger">*</span> </label>
                                    </div>
                            </div>

                            <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="number" min="0" max="9999" class="form-control border border-1 border-danger" id="amt_paid" name="amt_paid" placeholder=" " value="300" required>
                                        <label for="amt_paid">Amount Paid (<?= $CURRENCY; ?>) <span class="text-danger">*</span> </label>
                                    </div>
                            </div>


                        </div>
                        <!-- Inspection Order Details -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" readonly class="form-control text-bg-secondary bg-opacity-25 text-muted" id="order_number" name="order_number" placeholder=" " value="<?php echo config::REGION;?>-ADV-<?php echo $formatted; ?>" required>
                                    <label for="order_number">Inspection Order Number</label>
                                </div>

                            </div>


                        </div>
                        <div class="row g-3 mb-3">

                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="date" name="date" placeholder=" " required>
                                    <label for="date">1. Schedule Date <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
<!--                                    <input type="time" class="form-control" id="time" name="time" placeholder=" " min="06:00" max="18:00" step="300" required>-->
                                    <input type="text" class="form-control" id="time" name="time" placeholder=" " list="time-options" required>
                                    <label for="date">2. Schedule Time <span class="text-danger">*</span></label>
                                    <datalist id="time-options"></datalist>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-1 position-relative">
                                    <input type="hidden" id="inspector_id" name="inspector_id"> <!-- to store ID -->
                                    <input type="text" class="form-control" id="to" name="to" placeholder=" " required autocomplete="off">
                                    <label for="to">3. Assign to (<i class="fw-italic">Inspector Name</i>) <span class="text-danger">*</span></label>
                                    <!-- Suggestions container -->
                                    <ul id="inspector-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display:none; max-height:200px; overflow-y:auto;">
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3 position-relative">
                                    <!--                                    Auto populate when clicked establishment -->
                                    <input type="hidden" name="establishment_id" id="establishment_id" required>
                                    <input type="text" class="form-control" id="proceed" name="proceed" placeholder=" " autocomplete="off" required>
                                    <label for="proceed">4. Proceed to (<i class="fw-italic">Establishment Location</i>) <span class="text-danger">*</span></label>
                                    <!-- Dropdown container -->
                                    <ul id="proceed-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display:none; max-height:200px; overflow-y:auto;">
                                    </ul>
                                </div>
                                
<!--                                    Auto populate when -->
                                    <input type="hidden" name="checklist_id" id="checklist_id" /> 
                            </div>

                        </div>



                      
                        

                        <div class="mb-3">
                            <span class="fw-bold">NATURE OF INSPECTION <b class="text-danger">*</b> </span>
                            
                            <div class="container-fluid">
                                <div class="row">
                                   
                                   <?php $noi = select("nature_of_inspection",['noi_status'=>'A']);
                                         if(!empty($noi)){
                                             foreach($noi as $n){
                                    ?>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input shadow border rounded-5 border-3 border-navy" type="radio" name="nature_of_inspection" id="noi<?php echo $n['noi_id'];?>" value="<?php echo $n['noi_id'];?>" autocomplete="off" /> 
                                            <label for="noi<?php echo $n['noi_id'];?>" class="form-check-label border-0"><?php echo $n['noi_text'];?></label>
                                        </div>
                                    </div>
                                    <?php    } 
                                         }
                                    ?>
                                    
                                    <div class="col-lg-6 col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input shadow border rounded-5 border-3 border-navy" type="radio" name="nature_of_inspection" id="noi<?php echo $n['noi_id'];?>" value="0" autocomplete="off" /> 
                                            <label for="noi<?php echo $n['noi_id'];?>" class="form-check-label border-0">Others: </label>
                                            <input type="text" name="nature_of_inspection_others" class="form-control rounded-0 border border-dark border-top-0 border-start-0 border-end-0 border-bottom-1" placeholder="Please Specify">
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <span class="fw-bold">FSIC PURPOSE <b class="text-danger">*</b> </span>
                            
                            <div class="container-fluid">
                                <div class="row">
                                   
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input shadow border rounded-5 border-3 border-navy" type="radio" name="fsic_purpose" id="fsic_purpose1" value="FOR CERTIFICATE OF OCCUPANCY" autocomplete="off" /> 
                                            <label for="fsic_purpose1" class="form-check-label border-0">FOR CERTIFICATE OF OCCUPANCY</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input shadow border rounded-5 border-3 border-navy" type="radio" name="fsic_purpose" id="fsic_purpose2" value="FOR CERTIFICATE OF OCCUPANCY" autocomplete="off" /> 
                                            <label for="fsic_purpose2" class="form-check-label border-0">FOR BUSINESS PERMIT (NEW/RENEWAL)</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input shadow border rounded-5 border-3 border-navy" type="radio" name="fsic_purpose" id="fsic_purpose_others" value="OTHERS" autocomplete="off" /> 
                                            <label for="fsic_purpose_others" class="form-check-label border-0">Others: </label>
                                            <input type="text" name="fsic_purpose_others" class="form-control rounded-0 border border-dark border-top-0 border-start-0 border-end-0 border-bottom-1" placeholder="Please Specify">
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        


                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="purpose" name="purpose" placeholder=" " style="height: 120px">Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR</textarea>
                            <label for="purpose">PURPOSE OF INSPECTION</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="duration" name="duration" placeholder=" " value="Until the end of Inspection">
                            <label for="duration">DURATION</label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="remarks" name="remarks" placeholder=" " style="height: 140px"></textarea>
                            <label for="remarks">Remarks / Additional Instructions</label>
                        </div>



                        <!-- Submit Button -->
                        <div class="d-flex ms-auto mt-4">
                            <button type="submit" class="ms-auto btn btn-navy fw-bold">Submit Inspection Order</button>
                            <button type="reset" class="ms-2 btn btn-gold fw-bold form-reset">Reset</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

</body>
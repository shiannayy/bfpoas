<!DOCTYPE html>
<?php include_once "../includes/_init.php"; 
enforceRoleAccess(['administrator','client','inspector']);

if(isset($_REQUEST['inspection_id']) && isset($_REQUEST['roleLabel'])){
    $inspection_id = mysqli_real_escape_string($CONN, $_REQUEST['inspection_id']);

        date_default_timezone_set('Asia/Manila'); // Set timezone (optional but recommended)
        $today = date("F j, Y h:iA");
        $uncheckBox = "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-square mb-1' viewBox='0 0 16 16'>
                                        <path d='M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z' />
                                    </svg>";
        $checkBox = "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-check-square' viewBox='0 0 16 16'>
        <path d='M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z'/>
        <path d='M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z'/>
        </svg>";
            
        $table = ["inspections AS ins"];
        $joins = [
            [
                'table' => 'inspection_schedule AS sched',
                'on' => 'sched.schedule_id = ins.schedule_id',
                'type' => 'INNER'
            ],
            [
                'table' => 'general_info AS gi',
                'on' => 'sched.gen_info_id = gi.gen_info_id',
                'type' => 'INNER'
            ],
            [
                'table' => 'checklists AS c',
                'on' => 'sched.checklist_id = c.checklist_id',
                'type' => 'INNER'
            ],
            [
                'table' => 'nature_of_inspection AS noi',
                'on' => 'sched.noi_id = noi.noi_id',
                'type' => 'LEFT'
            ]
        ];

        $columns = [
            "sched.schedule_id AS schedule_id",
            "sched.order_number AS inspection_order_number",
            "ins.*",
            "gi.establishment_name AS establishment_name",
            "gi.owner_name AS owner_name",
            "sched.fsic_purpose AS fsic_purpose",
            "noi.noi_text",
            "sched.order_number AS schedule_order_number",
            "gi.postal_address",
            "gi.location_of_construction AS address",
            "gi.owner_name",
            "gi.height_of_building",
            "gi.no_of_storeys",
            "gi.area_per_floor",
            "gi.total_floor_area",
            "gi.portion_occupied",
            "gi.classification_of_occupancy",
            "DATE_ADD(ins.dateApproved, INTERVAL 365 DAY) AS fsic_validity"  
        ];
        $where = ['ins.inspection_id' => $inspection_id];
        $certificate = select_join($table, $columns, $joins, $where, null, 1);
        $cert = $certificate[0];            
        $purpose = $cert['fsic_purpose'] == 'OTHERS' ? strtoupper($cert['noi_text'] ?? '') : $cert['fsic_purpose'];
        ?>
        <html lang="en">

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>FSIC</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
            <link rel="stylesheet" href="../assets/css/color_pallette.css">
        <style>
        body {
        -webkit-user-select: none; /* Safari */
        -moz-user-select: none;    /* Firefox */
        -ms-user-select: none;     /* IE/Edge */
        user-select: none;         /* Standard */
        }    
        </style>
        </head>

        <body class="p-3" oncontextmenu="return false;" draggable="false" >
            <div class="container-fluid card p-0 border-4 border-dark rounded-0" id="print-area">
                <div class="card-body m-0">
                    <div class="container-fluid m-0">
                        <div class="row text-center align-center">
                            <div class="col-2">
                                <img oncontextmenu="return false;" draggable="false"  src="../assets/img/dilg.png" height="160px" width="160px" alt="" class="img-fluid">
                            </div>
                            <div class="col-8">
                                <small class="small-fine-print">
                                    <span class="">Republic of the Philippines</span> <br>
                                    <span class="mb-1 fs-6"><b>Department of Interior and Local Government</b></span> <br>
                                    <span class="fs-5 text-upper  text-navy-dark"><b>BUREAU OF FIRE PROTECTION</b></span>
                                    <div class="row text-center mb-2 normal-print">
                                        <div class="col-12 mb-0"><?= Config::P_REGION ?></div>
                                        <div class="col-12 mb-0"><?= Config::P_DIST_OFFICE ?></div>
                                        <div class="col-12 mb-0"><?= Config::P_STATION ?></div>
                                        <div class="col-12 mb-0"><?= Config::P_STATION_ADDRESS ?></div>
                                        <div class="col-12 mb-0"><?= Config::P_STATION_CONTACT ?></div>
                                    </div>
                                </small>
                            </div>
                            <div class="col-2">
                                <img oncontextmenu="return false;" draggable="false"  src="../assets/img/bfp-logo.png" height="160px" width="160px" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                
                    <div class="container px-0">
                        <div class="row">
                            <div class="col-7">
                                <p class="mb-0 d-flex align-items-center w-100 text-danger fs-5 fw-bold">
                                    <span>FSIC NO. </span>
                                    <span class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-3 border-danger ps-1 pb-0">
                                        <?= $cert['fsic_no'] ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-4 offset-1 p-0 text-center">
                                <p class="border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark w-100 text-center mb-0"><?= $today ?></p>
                                <span class="mt-0">DATE</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <h2 class="fs-2 mb-0 fw-bold text-uppercase text-navy-dark">
                                    FIRE SAFETY INSPECTION CERTIFICATE
                                </h2>
                            </div>
                            <div class="col-8 offset-2 fs-6 text-navy-dark fw-bold d-block">

                                <div class="d-inline align-items-middle mb-1">
                                    <?php 
                                    echo ($purpose == 'FOR CERTIFICATE OF OCCUPANCY') ? $checkBox : $uncheckBox; 
                                    ?>
                                    FOR CERTIFICATE OF OCCUPANCY
                                </div>
                                <br>
                                <div class="d-inline align-items-middle mb-1">
                                    <?php 
                                    echo ($purpose == 'FOR BUSINESS PERMIT (NEW/RENEWAL)') ? $checkBox : $uncheckBox; 
                                    ?>
                                    FOR BUSINESS PERMIT (NEW/RENEWAL)
                                </div>
                                <br>
                                <div class="d-inline align-items-middle d-flex align-items-center w-100">
                                    <?php 
                                    echo ($cert['fsic_purpose'] == 'OTHERS') ? $checkBox : $uncheckBox; 
                                    ?>
                                    OTHERS 
                                    
                                    <b class="flex-fill small border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark text-center mx-2">
                                        <?php if($cert['fsic_purpose'] == 'OTHERS'){ echo $purpose; }?>
                                    </b>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 pt-2">
                                <b class="fs-6 text-uppercase">TO WHOM IT MAY CONCERN:</b>
                            </div>
                        </div>
                        <div class="row"  style="font-size:10pt">
                            <div class="col-12" >
                                <p class="mb-0 w-100" style="text-indent:4em; text-align:justify; text-justify:inter-word;">
                                    By virtue of the provisions of RA 9514 otherwise known as the Fire Code of the Philippines of 2008, the application for <b>FIRE SAFETY INSPECTION CERTIFICATE</b> of
                                </p>
                                <p class="fw-bold fs-6 text-uppercase border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark w-100 text-center mb-0">
                                    <?= $cert['establishment_name'] ?>
                                </p>
                                <p class="small text-center w-100 fst-italic mb-0">(Name of Establishment)</p>
                                <p class="mb-0 d-flex align-items-center w-100">
                                    <span>owned and managed by</span>
                                    <b class="fw-bold fs-6 text-uppercase flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark text-center mx-2">
                                        <?=$cert['owner_name'] ?>
                                    </b>
                                    <span>with postal address at</span>
                                </p>
            
                                <p class="small text-center w-100 fst-italic mb-0">(Name of Owner/Representative)</p>
                                <p class="fw-bold fs-6 text-uppercase border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark w-100 text-center mb-0"> 
                                    <?= $cert['postal_address'] ?? $cert['address'] ?>
                                </p>
                                <p class="small text-center w-100 fst-italic mb-0">(Address)</p>
                                <p class="text-align-justify">
                                    is hereby <b>GRANTED</b> after said building structure or facility has been duly inspected with the finding that it
        has fully complied with the fire safety and protection requirements of the Fire Code of the Philippines of
        2008 and its Revised Implementing Rules and Regulations.
                                </p>
                            </div>
                        </div>
                        <div class="row g-0"  style="font-size:10pt">
                            <div class="col-12">
                            <p class="mb-0 d-flex align-items-center w-100" style="text-indent:4em">
                                    <span>This Certification is valid for</span>
                                    <b class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark text-center mx-2">
                                        <b class="text-uppercase"><?= $cert['owner_name'] ?></b>   
                                    </b>
                                </p>
                            </div>
                            <div class="col-12 d-flex align-items-end">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-4 offset-7">(Description)</div>
                                        </div>
                                    </div>
                            </div>
                            <div class="col-12">
                            <p class="mb-2 d-flex align-items-center w-100">
                                    <b class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark text-center">
                                        <?= " with ". $cert['total_floor_area'] . "sq.m. located at " . (!$cert['portion_occupied'] ? " Ground Floor" : $cert['portion_occupied']) . " of a " . ($cert['no_of_storeys'] < 1 ? 1 : $cert['no_of_storeys']) . " storey " . $cert['classification_of_occupancy'] ?>
                                    </b>
                                    <span>valid until</span>
                                    <b class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark text-center mx-2">
                                        <?=  isset($cert['fsic_validity']) ? date("F d, Y", strtotime($cert['fsic_validity'])) : 'Certificate Not Yet Approved' ?>
                                    </b>
                                </p>
                                <br>
                                <p class="mb-2 d-flex align-items-center w-100" style="text-indent: 4em;">Violation of Fire Code provisions shall cause this certificate null and void after appropriate
        proceeding and shall hold the owner liable to the penalties provided for by the said Fire Code.</p>
                            </div>
                        </div>    
                        <div class="row g-1" style="font-size:10pt">
                            <div class="col-4">
                            <br>
                                <b style="font-size:11pt">Fire Code Fees:</b><br>
                                <p class="mb-0 d-flex align-items-center w-100" style="font-size:9pt">
                                    <span>Amount Paid:</span>
                                    <b class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark text-end">
                                        Php 300.00
                                    </b>
                                </p>
                                <p class="mb-0 d-flex align-items-center w-100" style="font-size:9pt">
                                    <span>OR Number:</span>
                                    <b class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark  text-end">
                                        OR123456
                                    </b>
                                </p>
                                <p class="mb-0 d-flex align-items-center w-100" style="font-size:9pt">
                                    <span>Date Paid:</span>
                                    <b class="flex-fill border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark  text-end">
                                        Nov 10, 2025
                                    </b>
                                </p>
                            </div>
                            <div class="col-2"></div>
                            <div class="col-6 position-relative ">
                            <br>
                                <b  style="font-size:12pt">RECOMMEND APPROVAL:</b><br>
                                
                                <?php if (isSignedBy('Recommending Approver',$inspection_id,2)){ ?>
                                <img src="../assets/signatures/<?php echo esignature($cert['recommended_by']);?>" oncontextmenu="return false;" draggable="false"  style="width: 2in; mix-blend-mode: darken; left: 30%; bottom: 20%" class="position-absolute"  alt="">
                                <?php } ?>
                                <br>
                                <p class="text-uppercase border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark w-100 text-center mb-0"> 
                                <?php echo getUserInfo($cert['recommended_by']);?></p>
                                <div class="container-fluid text-center">
                                    <span style="font-size:9pt">CHIEF, Fire Safety Enforcement Section</span>
                                </div>
                            </div>
                            <div class="col-6 position-relative">
                            
                            <?php if (isSignedBy('Client',$inspection_id,2)){ ?>
                                <img src="../assets/signatures/<?php echo esignature($cert['received_by']);?>" oncontextmenu="return false;" draggable="false"  style="width: 2in; mix-blend-mode: darken; left: 20%; top: 15%" class="position-absolute"  alt="">
                                
                            <div class="card position-absolute mb-0 border-0" style="top: 10%; left: 20%;color:#660099">
                                    <h3  class="fs-2 text-center fw-bold mb-0">RECEIVED</h3>
                                    <span class="small text-center fw-bold"><?= $cert['dateReceived']?></span>
                                    <img src="../assets/signatures/<?php echo esignature($cert['received_by']);?>" oncontextmenu="return false;" draggable="false"  style="width: 2in; mix-blend-mode: darken; left: 20%; top: 15%" class="position-absolute"  alt="">
                                    <br>
                                <p class="text-uppercase border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark w-100 text-center mb-0">
                                <?php echo getUserInfo($cert['received_by']);?>
                                </p>
                            </div>
                            <?php } ?>
                                
                            </div>
                            <div class="col-6 position-relative">
                                <br>
                                <b  style="font-size:12pt">APPROVED:</b><br>
                                <?php if (isSignedBy('Approver',$inspection_id,2)){ ?>
                                <img src="../assets/signatures/<?php echo esignature($cert['approved_by']);?>" oncontextmenu="return false;" draggable="false"  style="height:1.2in; mix-blend-mode: darken; position: absolute; left: 40%; bottom: 10%" alt="">
                                <?php } ?>
                                <br>
                                <p class="text-uppercase border border-top-0 border-start-0 border-end-0 border-bottom-2 border-dark w-100 text-center mb-0">
                                <?php echo getUserInfo($cert['approved_by']);?></p>
                                <div class="container-fluid text-center">
                                    <span style="font-size:9pt">CITY/MUNICIPAL FIRE MARSHAL</span>
                                </div>
                                <br>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 text-align-justify">
                                <p class="fst-italic fw-bold w-100 text-align-justify mb-0" style="text-indent:4em; text-align:justify; text-justify:inter-word; font-size: 7pt">NOTE: “This Certificate does not take the place of any license required by law and is not transferable. Any change in the use of occupancy of the premises shall require a new certificate.”</p>
                            </div>
                            <div class="col-12 text-center">
                                <p class="fs-6 text-align-center fw-bold text-uppercase mb-0">THIS CERTIFICATE SHALL BE POSTED CONSPICUOUSLY</p>
                                <p class="mb-0 text-align-center fw-bold text-danger text-uppercase" style="font-size: 7pt">PAALALA: “MAHIGPIT NA IPINAGBABAWAL NG PAMUNUAN NG BUREAU OF FIRE PROTECTION SA MGA KAWANI NITO
        ANG MAGBENTA O MAGREKOMENDA NG ANUMANG BRAND NG FIRE EXTINGUISHER”</p>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row g-0">
                            <div class="col-2">
                                <b class="mb-0 ms-0 px-2 fst-italic fw-bold border border-start-0 border-bottom-0 border-top-1 border-end-2 border-dark position-absolute bottom-0 start-0">BFP COPY</b>
                            </div>
                            <div class="col-8 text-center">
                                <p class="fs-6 text-align-center fw-bold text-uppercase mb-0">“FIRE SAFETY IS OUR MAIN CONCERN”</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        <small class="fw-bold bottom-0 start-0"><?= Config::BFP_REV ?></small>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
            
        </body>

        </html>


        <?php 
}
else{
    echo "Why are you here? 404...";
}?>
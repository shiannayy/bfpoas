<?php
/*FSED 9F*/
include_once "../includes/_init.php";
$schedule_id = intval($_GET['id']);
$schedule = select_join(
    ['inspection_schedule'],
    ['*'],
    [
        [
            'table' => 'checklists',
            'on' => 'inspection_schedule.checklist_id = checklists.checklist_id',
            'type' => 'LEFT'
        ]
    ],
    ['inspection_schedule.schedule_id' => $schedule_id],
    null,
    1
);

if (!$schedule) die("Invalid Schedule ID");
$data = $schedule[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inspection Order Print</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        .line {
            display: inline-block;
            min-width: 250px;
            border-bottom: 1px solid #000;
        }

        .fine-print-small {
            font-size: 0.5rem;
            /* smaller font size */
            line-height: 1;
            /* minimal line height */
            margin: 0;
            /* remove default margin */
            padding: 0;
        }

        .fine-print-small span {
            display: block;
            /* each on its own line */
            line-height: 1;
            /* tight line spacing */
            margin: 0;
            /* no gaps */
            padding: 0;
        }

        .small-print {
            font-size: 0.8rem;
        }

        .normal-print {
            font-size: 0.9rem;
        }

        img.img-signature {
            mix-blend-mode: darken;
        }
        /*
    body { font-family: "Times New Roman", serif; font-size: 14px; }
    
    .header-title { font-weight: bold; text-transform: uppercase; }
    .signature { margin-top: 60px; text-align: center; }
    .signature .line { width: 280px; margin: 0 auto; }
    .table td { vertical-align: top; padding: 6px; }
    hr { border-top: 1px solid #000; }
    @media print {
      button { display: none; }
      .container { max-width: 100%; }
    }
*/
    </style>
</head>

<body>
    <div class="container-fluid card border-4 border-dark my-1 rounded-0" id="print-area">
        <div class="card-header">
            <div class="container-fluid m-0">
                <div class="row text-center align-center">
                    <div class="col-2">
                        <!-- <img src="../assets/img/dilg.png" height="60px" alt="" class="img-fluid"> -->
                        <img src="<?= Config::DILG_LOGO ?>" height="60px" alt="" class="img-fluid">
                    </div>
                    <div class="col-8">
                        <small class="small-fine-print">
                            <span class="">Republic of the Philippines</span> <br>
                            <span class=""><b>Department of Interior and Local Government</b></span> <br>
                            <span class=""><b>Bureau of Fire Protection</b></span>

                        </small>
                    </div>
                    <div class="col-2">
                        <img src="../assets/img/bfp-logo.png" height="60px" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">

            <!-- Top Info -->
            <div class="row text-center mb-2 normal-print">
                <div class="col-12 mb-0"><?= config::P_REGION ?></div>
                <div class="col-12 mb-0"><?= config::P_DIST_OFFICE ?></div>
                <div class="col-12 mb-0"><?= config::P_STATION ?></div>
                <div class="col-12 mb-0"><?= config::P_STATION_ADDRESS ?></div>
                <div class="col-12 mb-0"><?= config::P_STATION_CONTACT ?></div>
            </div>

            <!-- Header: Inspection Order / Date -->
            <div class="row mb-0  normal-print">
                <div class="col-6">
                    <b class="header-title mb-1">INSPECTION ORDER</b>
                    <p class="mt-0">NUMBER:
                        <span class="line text-center"><?= htmlspecialchars($data['order_number']) ?></span>
                        <br>
                        <i class="text-center">(Pre-Numbered)</i>
                    </p>
                </div>
                <div class="col-4 offset-1 text-center">
                    <span class="line"><?= htmlspecialchars($data['scheduled_date']) ?></span>
                    <br>
                    <span>DATE</span>
                </div>
            </div>

            <!-- Main Details Table -->
            <table class="table table-bordered border-1 border-dark w-100 mb-0 normal-print">
                <tbody>
                    <tr>
                        <td class="fw-bold col-3">TO</td>
                        <td class="col">:</td>
                        <td class="col-8"><?= htmlspecialchars($data['to_officer']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">PROCEED</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['proceed_instructions']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">PURPOSE</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['purpose']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">DURATION</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['duration']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">REMARKS OR ADDITIONAL INSTRUCTION/S</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($data['remarks']) ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Signatures -->
            <div class="row mt-3 normal-print">
                <div class="col-6 signature">
                    <p class="fw-bold small">RECOMMEND APPROVAL:</p>
                       <div class="text-center container-fluid">
                        <?php if (isSignedBy('Recommending Approver',$schedule_id)){ ?>
                        <img src="../assets/signatures/<?php echo esignature($data['RecommendingApprover']);?>" height="70px" alt="" class="img-signature img-responsive">
                        <?php } ?>
                        <br>
                        <u class="text-uppercase text-center"><?php echo getUserInfo($data['RecommendingApprover']);?></u>
                        
                        <p class="text-center small">Chief, Fire Safety Enforcement Section</p>
                      </div>
                </div>
                <div class="col-6 signature">
                    <p class="fw-bold small">APPROVED:</p>
                    <div class="text-center container-fluid">
                        <?php if (isSignedBy('Final Approver',$schedule_id)){ ?>
                        <img src="../assets/signatures/<?php echo esignature($data['FinalApprover']);?>" height="70px" alt="" class="img-signature img-responsive">
                        <?php } ?>
                        <br>
                        <u class="text-uppercase text-center"><?php echo getUserInfo($data['FinalApprover']);?></u>
                        
                        <p class="text-center small">CITY / MUNICIPAL FIRE MARSHAL</p>
                    </div>
                </div>
            </div>

            <!-- Acknowledgement -->
            <div class="row mt-0 small-print">
                <div class="col-12">
                   <hr class="my-1">
                    <p class="fw-bold text-center mb-0">ACKNOWLEDGEMENT</p>
                    <p class="small">
                        This is to acknowledge that permission was granted to the above-named
                        Fire Safety Inspector/s accompanied by authorized representative to conduct
                        Fire Safety Inspection within the premises in accordance with law.
                    </p>
                </div>
                <div class="col-6 text-center">
                    <div class="signature small text-center">
                        <?php if (isSignedBy('Client',$schedule_id)){ ?>
                        <img src="../assets/signatures/<?php echo esignature($data['AckByClient_id']);?>" height="60px" alt="" class="img-signature img-responsive">
                        <?php } ?>
                        <br>
                        <u class="text-uppercase text-center"><?php echo getUserInfo($data['AckByClient_id']);?></u>
                        <br>
                        <p class="small">Signature over Printed Name/Authorized Representative</p>
                    </div>
                </div>

                <div class="col-5 offset-1 text-center position-relative">
                    <p class="small position-absolute bottom-0 start-0">
                       <u> <?= $data['DateAckbyClient'] ?> </u> <br>
                       Date/Time
                    </p>
                </div>
            </div>

            <!-- Footer Notes -->
            <div class="row mt-0 small-print">
               <div class="col-12">
                <p class="small text-danger text-center">
                    <b>PAALALA: “MAHIGPIT NA IPINAGBABAWAL NG PAMUNUAN NG BUREAU OF FIRE PROTECTION SA MGA KAWANI NITO ANG MAGBENTA O MAGREKOMENDA NG ANUMANG BRAND NG FIRE EXTINGUISHER”</b>
                </p>
               </div>
               <div class="col-12">
                <h3 class="h6 text-center fw-bold text-dark">“FIRE SAFETY IS OUR MAIN CONCERN”</h3>
                </div>
                <div class="col-3">
                <small class="fine-print-small mb-0">
                    <span>DISTRIBUTION:</span>
                    <span><em>Original</em> (Applicant/Owner’s Copy)</span>
                    <span><em>Duplicate</em> (BFP Copy)</span>
                </small>
                </div>

            </div>
        </div>
    </div>
    <small class="text-start small fw-bold">BFP-QSF-FSED-009 Rev. 01 (07.05.19)</small>
    <?php
    if(isset($_GET['print'])){?>
    <script>
        window.print();
    </script>
    <?php } ?>
  
</body>

</html>
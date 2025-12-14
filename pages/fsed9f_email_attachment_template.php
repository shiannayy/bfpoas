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

// Get image paths
$dilg_img = "../assets/img/dilg.jpg";
$bfp_img = "../assets/img/bfp-logo.jpg";


$dilg_base64 = 'data:image/jpg;base64,' . base64_encode(file_get_contents("../assets/img/dilg.jpg"));
$bfp_base64 = 'data:image/jpg;base64,' . base64_encode(file_get_contents("../assets/img/bfp-logo.jpg"));
// Get signature paths and convert to base64
$rec_sig_base64 = $final_sig_base64 = $client_sig_base64 = '';

function getSignatureBase64($path) {
    if (empty($path) || !file_exists($path)) {
        return '';
    }
    
    $imageData = file_get_contents($path);
    if ($imageData === false) {
        return '';
    }
    
    $mimeType = mime_content_type($path);
    $base64 = base64_encode($imageData);
    
    return "data:$mimeType;base64,$base64";
}

if (isSignedBy('Recommending Approver', $schedule_id)) {
    $rec_sig_path = '../assets/signatures/' . esignature($data['RecommendingApprover']);
    if (file_exists($rec_sig_path)) {
        $rec_sig_base64 = getSignatureBase64($rec_sig_path);
    }
}

if (isSignedBy('Final Approver', $schedule_id)) {
    $final_sig_path = '../assets/signatures/' . esignature($data['FinalApprover']);
    if (file_exists($final_sig_path)) {
        $final_sig_base64 = getSignatureBase64($final_sig_path);
    }
}

if (isSignedBy('Client', $schedule_id)) {
    $client_sig_path = '../assets/signatures/' . esignature($data['AckByClient_id']);
    if (file_exists($client_sig_path)) {
        $client_sig_base64 = getSignatureBase64($client_sig_path);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>Document</title>
</head>
<body>
    <div style="border: 2px 2px 2px 2px; margin: 1cm 1cm 1cm 1cm; padding: 10cm 10cm 10cm 10cm;">
            <!-- Header banner -->
            <table style="width: 100%; border-collapse: collapse; margin: 0px 0px 0px 0px; font-size: 11pt">
                <tr style="text-align: center; vertical-align: middle;">
                    <td style="width: 16.6666%; padding: 0; text-align: center; vertical-align:top;">
                        <img src="<?= $dilg_base64 ?>" alt="IMG NOT FOUND"
                            style="max-width: 100%; display: inline; margin: 0;" />
                    </td>
                    <td style="width: 66.6666%; padding: 20px 0px 0px 0px; text-align: center; vertical-align:bottom;">
                            <span>Republic of the Philippines</span><br>
                            <span><b>Department of the Interior and Local Government</b></span><br>
                            <span><b>Bureau of Fire Protection</b></span>

                        <?= htmlspecialchars(Config::P_REGION) ?> <br>
                        <?= htmlspecialchars(Config::P_DIST_OFFICE) ?> <br> 
                        <?= htmlspecialchars(Config::P_STATION) ?> <br>
                        <?= htmlspecialchars(Config::P_STATION_ADDRESS) ?> <br>
                        <?= htmlspecialchars(Config::P_STATION_CONTACT) ?> <br>
                    </td>
                    <td style="width: 16.6666%; padding: 0; text-align: center; vertical-align:middle;">
                        <img src="<?= $bfp_base64 ?>" alt="IMG NOT FOUND"
                            style="max-width: 100%; display: inline; margin: 0;" />
                    </td>
                </tr>
            </table>
            
<hr>
                <!-- Header: Inspection Order / Date -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;margin-top:20px;">
                    <tr style="vertical-align: top;">
                        <td style="width: 50%; padding: 0;vertical-align: top; text-align: left">
                            <span style="margin-top: 0;">
                                <b>INSPECTION ORDER</b> <br>
                                <span>NUMBER</span>
                                <u style="display: inline-block; min-width: 250px; border-bottom: 1px solid #000; text-align: center;">
                                    <?= htmlspecialchars($data['order_number'] ?? '') ?>
                                </u><br>
                                <i style="text-align: center; font-style: italic; display: block;">(Pre-numbered)</i>
                            </span>
                        </td>
                        <td style="width: 50%; padding: 0; text-align: center; vertical-align: top;">
                            <u style="display: inline-block; min-width: 200px; padding-bottom: 5px">
                                <?php
                                    if (!empty($data['scheduled_date']) && !empty($data['schedule_time'])) {
                                        $date = date('M. d, Y', strtotime($data['scheduled_date']));
                                        $time = date('h:i A', strtotime($data['schedule_time']));
                                        echo $date . ' at ' . $time;
                                    } else {
                                        echo 'Date/Time not set';
                                    }
                                    ?>
                            </u><br>
                            <span>DATE</span>
                        </td>
                    </tr>
                </table>
                <table style="width: 98%; border-collapse: collapse; border: 1px solid #000; margin: 20px 20px 20px 20px;">
                    <tbody>
                        <tr>
                            <td style="font-weight: bold; width: 25%; border: 1px solid #000; padding: 12px;">TO</td>
                            <td style="width: 8.3333%; border: 1px solid #000; padding: 12px; vertical-align: middle">:</td>
                            <td style="width: 66.6667%; border: 1px solid #000; padding: 12px;">
                                <?= htmlspecialchars($data['to_officer'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">PROCEED</td>
                            <td style="border: 1px solid #000; padding: 12px; vertical-align: middle">:</td>
                            <td style="border: 1px solid #000; padding: 12px;">
                                <?= htmlspecialchars($data['proceed_instructions'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">PURPOSE</td>
                            <td style="border: 1px solid #000; padding: 12px; vertical-align: middle">:</td>
                            <td style="border: 1px solid #000; padding: 12px;">
                                <?= htmlspecialchars($data['purpose'] ?? '') ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">DURATION</td>
                            <td style="border: 1px solid #000; padding: 12px; vertical-align: middle">:</td>
                            <td style="border: 1px solid #000; padding: 12px;">
                                <?= htmlspecialchars($data['duration'] ?? '') ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">REMARKS OR ADDITIONAL
                                INSTRUCTION/S</td>
                            <td style="border: 1px solid #000; padding: 12px; vertical-align: middle">:</td>
                            <td style="border: 1px solid #000; padding: 12px;">
                                <?= htmlspecialchars($data['remarks'] ?? '') ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table style="width: 100%; border-collapsed: collapse; margin-top: 0px;">
                    <tr>
                        <td style="width: 50%; padding: 0; margin-top: 0; text-align: center; vertical-align: top; position: relative">
                            <p style="text-align: left;font-weight: bold; margin: 0.25rem 0;">RECOMMEND APPROVAL:</p>
                            <div style="text-align: center;">
                                <?php if (!empty($rec_sig_base64)): ?>
                                <img src="<?= $rec_sig_base64 ?>" alt="No Signature"
                                    style="position: absolute; top: 0; left: 10px; mix-blend-mode: darken; max-width: 50%; height: 40px;">
                                <?php endif; ?>
                                <br>
                                <u style="text-transform: uppercase; display: inline; margin:0;">
                                    <?= getUserInfo($data['RecommendingApprover']) ?>
                                </u>
                                <br>
                                <span style="text-align: center; margin: 0.25rem 0;">Chief, Fire Safety Enforcement Section
                                </span>
                            </div>
                        </td>
                        <td style="width: 50%; padding: 0; margin-top: 0; text-align: center; position: relative">
                            <p style="font-weight: bold; margin: 0.25rem 0;">APPROVED:</p>
                            <div style="text-align: center; vertical-align: top;">
                                <?php if (!empty($final_sig_base64)): ?>
                                <img src="<?= $final_sig_base64 ?>" alt="No Signature"
                                    style="position: absolute; top: 0; left: 10px; mix-blend-mode: darken; max-width: 50%; height: 40px;">
                                <?php endif; ?>
                                <br>
                                <u style="text-transform: uppercase; text-decoration: underline; display: block; margin:0;">
                                    <?= getUserInfo($data['FinalApprover']) ?>
                                </u>
                                <br>
                                <span style="text-align: center; margin: 0.25rem 0;">CITY / MUNICIPAL FIRE MARSHAL</span>
                            </div>
                        </td>
                    </tr>
                </table>
                <!-- Acknowledgement -->
                <hr>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="3" style="padding: 0; text-align: center;">
                            
                            <span style="font-weight: bold; text-align: center; margin-bottom: 0; margin-top: 0;">ACKNOWLEDGEMENT</span>
                            <br>
                                <span style="text-align: center; font-size: 10pt">
                                This is to acknowledge that permission was granted to the above-named
                                Fire Safety Inspector/s accompanied by authorized representative to conduct
                                Fire Safety Inspection within the premises in accordance with law.
                                </span>
                            
                        </td>
                    </tr>
                    <tr style="vertical-align: middle;">
                        <td style="width: 50%; padding: 0; text-align: center; position: relative">
                            <div style="text-align: center; vertical-align: top">
                                <br>
                                <?php if (!empty($client_sig_base64)): ?>
                                <img src="<?= $client_sig_base64 ?>" alt="No Signature"
                                   style="position: absolute; top: 0; left: 10px; mix-blend-mode: darken; max-width: 50%; height: 40px;">
                                <br>
                                <?php endif; ?>
                                 <u style="text-transform: uppercase; text-decoration: underline; display: block; margin:0;">
                                    <?= getUserInfo($data['AckByClient_id']) ?>
                                </u>
                                <br>
                                <span style="font-size: 0.875em; margin: 0.25rem 0;"> Signature over Printed Name/Authorized Representative</span>
                            </div>
                        </td>
                        <td style="width: 50%; padding: 0; vertical-align: middle; text-align: center;">
                                <u><?= htmlspecialchars($data['DateAckbyClient'] ?? '') ?></u><br>
                                Date/Time
                        </td>
                    </tr>
                </table>
        
        
            <!-- Footer Notes -->
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="text-align: center; color: #ff0000; font-weight: bold; padding: 8px 0; border-bottom: 1px solid #ccc; font-size: 10pt;">
                        PAALALA: "MAHIGPIT NA IPINAGBABAWAL NG PAMUNUAN NG BUREAU OF FIRE PROTECTION SA MGA KAWANI
                        NITO ANG MAGBENTA O MAGREKOMENDA NG ANUMANG BRAND NG FIRE EXTINGUISHER"
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; padding: 10px 0; font-size: 10pt;">
                        <div style="font-size: 16px; font-weight: bold;">"FIRE SAFETY IS OUR MAIN CONCERN"</div>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left; padding: 5px 0; font-size: 8pt;">
                        <strong>DISTRIBUTION:</strong> <br>
                        Duplicate (BFP Copy - Email Attachment)
                    </td>
                </tr>
            </table>
        
    </div>

    <div class="text-center mt-2">
        <small style="font-size: 7pt; text-align: left;">BFP-QSF-FSED-009 Rev. 01 (07.05.19)</small>
    </div>
</body>

</html>
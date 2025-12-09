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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
    * {
        font-family: Calibri, sans-serif;
    }
    </style>
</head>

<body style="border: 1px 1px 1px 1px; padding: 3px;">
    <div class="container-fluid card rounded-0" id="print-area">
        <div class="card-header">
            <table style="width: 100%; border-collapse: collapse; margin-top: 0px; margin-bottom:0px">
                <tr style="text-align: center; vertical-align: middle;">
                    <td style="width: 16.6666%; padding: 0;">
                        <img src="<?= $dilg_base64 ?>" height="60px" alt="IMG NOT FOUND"
                            style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                    </td>
                    <td style="width: 66.6666%; padding: 0;">
                        <p style="text-align: center; line-height: 1.2">
                            <span>Republic of the Philippines</span><br>
                            <span><b>Department of the Interior and Local Government</b></span><br>
                            <span><b>Bureau of Fire Protection</b></span>
                        </p>
                    </td>
                    <td style="width: 16.6666%; padding: 0;">
                        <img src="<?= $bfp_base64 ?>" height="60px" alt="IMG NOT FOUND"
                            style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-body">
            <!-- Top Info -->
            <table style="width: 100%">
                <tr>
                    <td style="text-align: center; vertical-align: middle; padding: 0px 0px 0px 0px;">
                        <?= htmlspecialchars(config::P_REGION) ?></td>
                </tr>
                <tr>
                    <td style="text-align: center; vertical-align: middle; padding: 0px 0px 0px 0px;">
                        <?= htmlspecialchars(config::P_DIST_OFFICE) ?> </td>
                </tr>
                <tr>
                    <td style="text-align: center; vertical-align: middle; padding: 0px 0px 0px 0px;">
                        <?= htmlspecialchars(config::P_STATION) ?> </td>
                </tr>
                <tr>
                    <td style="text-align: center; vertical-align: middle; padding: 0px 0px 0px 0px;">
                        <?= htmlspecialchars(config::P_STATION_ADDRESS) ?></td>
                </tr>
                <tr>
                    <td style="text-align: center; vertical-align: middle; padding: 0px 0px 0px 0px;">
                        <?= htmlspecialchars(config::P_STATION_CONTACT) ?></td>
                </tr>
        </div>

        <!-- Header: Inspection Order / Date -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;margin-top:20px;">
            <tr style="vertical-align: top;">
                <td style="width: 50%; padding: 0;vertical-align: top; text-align: left">
                    <p style="margin-top: 0;">

                        <b>INSPECTION ORDER</b> <br>
                        <span>NUMBER</span>
                        <span
                            style="display: inline-block; min-width: 250px; border-bottom: 1px solid #000; text-align: center;">
                            <?= htmlspecialchars($data['order_number'] ?? '') ?>
                        </span><br>
                        <i style="text-align: center; font-style: italic; display: block;">(Pre-numbered)</i>
                    </p>
                </td>
                <td style="width: 50%; padding: 0; padding-left: 8.3333%; text-align: center; vertical-align: bottom;">
                    <b
                        style="display: inline-block; min-width: 200px; border-bottom: 1px solid #000; padding-bottom: 5px">
                        <?php
                                    if (!empty($data['scheduled_date']) && !empty($data['schedule_time'])) {
                                        $date = date('M. d, Y', strtotime($data['scheduled_date']));
                                        $time = date('h:i A', strtotime($data['schedule_time']));
                                        echo $date . ' at ' . $time;
                                    } else {
                                        echo 'Date/Time not set';
                                    }
                                    ?>
                    </b><br>
                    <span>DATE</span>
                </td>
            </tr>
        </table>

        <!-- Main Details Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0; border: 2px solid #000;">
            <tbody>
                <tr>
                    <td style="font-weight: bold; width: 25%; border: 1px solid #000; padding: 12px;">TO</td>
                    <td style="width: 8.3333%; border: 1px solid #000; padding: 12px;">:</td>
                    <td style="width: 66.6667%; border: 1px solid #000; padding: 12px;">
                        <?= htmlspecialchars($data['to_officer'] ?? '') ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">PROCEED</td>
                    <td style="border: 1px solid #000; padding: 12px;">:</td>
                    <td style="border: 1px solid #000; padding: 12px;">
                        <?= htmlspecialchars($data['proceed_instructions'] ?? '') ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">PURPOSE</td>
                    <td style="border: 1px solid #000; padding: 12px;">:</td>
                    <td style="border: 1px solid #000; padding: 12px;"><?= htmlspecialchars($data['purpose'] ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">DURATION</td>
                    <td style="border: 1px solid #000; padding: 12px;">:</td>
                    <td style="border: 1px solid #000; padding: 12px;"><?= htmlspecialchars($data['duration'] ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border: 1px solid #000; padding: 12px;">REMARKS OR ADDITIONAL
                        INSTRUCTION/S</td>
                    <td style="border: 1px solid #000; padding: 12px;">:</td>
                    <td style="border: 1px solid #000; padding: 12px;"><?= htmlspecialchars($data['remarks'] ?? '') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures -->
        <?php
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

        <table style="width: 100%; border-collapsed: collapse; margin-top: 0;">
            <tr style="vertical-align: top;">
                <td style="width: 50%; padding: 0; margin-top: 0; text-align: center; position: relative">
                    <p style="font-weight: bold;  margin: 0.25rem 0;">RECOMMEND APPROVAL:</p>
                    <div style="text-align: center;">
                        <?php if (!empty($rec_sig_base64)): ?>
                        <img src="<?= $rec_sig_base64 ?>"alt="No Signature"
                            style="position: absolute; top: 0; left: 10px; mix-blend-mode: darken; max-width: 50%; height: 40px; ">
                        <?php endif; ?>
                        <br>
                        <u style="text-transform: uppercase; text-decoration: underline; display: block; margin:0;">
                            <?= getUserInfo($data['RecommendingApprover']) ?>
                        </u>
                        <p style="text-align: center; margin: 0.25rem 0;">Chief, Fire Safety Enforcement Section</p>
                    </div>
                </td>
                <td style="width: 50%; padding: 0; margin-top: 0; text-align: center; position: relative">
                    <p style="font-weight: bold; margin: 0.25rem 0;">APPROVED:</p>
                    
                    <div style="text-align: center;">
                        <?php if (!empty($final_sig_base64)): ?>
                        <img src="<?= $final_sig_base64 ?>" alt="No Signature"
                            style="position: absolute; top: 0; left: 10px; mix-blend-mode: darken; max-width: 50%; height: 40px;">
                        <?php endif; ?>
                        <br>
                        <u style="text-transform: uppercase; text-decoration: underline; display: block; margin:0;">
                            <?= getUserInfo($data['FinalApprover']) ?>
                        </u>
                        <p style="text-align: center; margin: 0.25rem 0;">CITY / MUNICIPAL FIRE MARSHAL</p>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Acknowledgement -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 40px ">
            <tr>
                <td colspan="3" style="padding: 0;">
                    <hr style="margin: 0.25rem 0; border: 0; border-top: 1px solid rgba(0,0,0,0.25);">
                    <p style="font-weight: bold; text-align: center; margin-bottom: 0;">ACKNOWLEDGEMENT</p>
                    <p style="text-align: center; font-size: 10pt">
                        This is to acknowledge that permission was granted to the above-named
                        Fire Safety Inspector/s accompanied by authorized representative to conduct
                        Fire Safety Inspection within the premises in accordance with law.
                    </p>
                </td>
            </tr>
            <tr style="vertical-align: middle;">
                <td style="width: 50%; padding: 0; text-align: center; position: relative">
                    <div style="text-align: center; position: relative">
                        <br>
                        <?php if (!empty($client_sig_base64)): ?>
                        <img src="<?= $client_sig_base64 ?>" height="60px" alt="No Signature"
                            style="position: absolute; bottom: 20%; right: 0px; mix-blend-mode: darken; max-width: 50%; height: auto; margin: 0 auto;">
                        <br>
                        <?php endif; ?>
                        <u
                            style="text-transform: uppercase; text-decoration: underline; display: block; margin: 0.25rem 0;">
                            <?= getUserInfo($data['AckByClient_id']) ?>
                        </u>
                        <p style="font-size: 0.875em; margin: 0.25rem 0;">Signature over Printed Name/Authorized
                            Representative</p>
                    </div>
                </td>
                <td style="width: 8.3333%; padding: 0;"></td>
                <td style="width: 41.6667%; padding: 0; text-align: center; position: relative;">
                    <div style="position: absolute; bottom: 0; left: 0; font-size: 0.875em;">
                        <u><?= htmlspecialchars($data['DateAckbyClient'] ?? '') ?></u><br>
                        Date/Time
                    </div>
                </td>
            </tr>
        </table>
        <!-- Footer Notes -->
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="text-align: center; color: #ff0000; font-weight: bold; padding: 8px 0; border-bottom: 1px solid #ccc;">
                    PAALALA: "MAHIGPIT NA IPINAGBABAWAL NG PAMUNUAN NG BUREAU OF FIRE PROTECTION SA MGA KAWANI
                    NITO ANG MAGBENTA O MAGREKOMENDA NG ANUMANG BRAND NG FIRE EXTINGUISHER"
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 16px; font-weight: bold;">"FIRE SAFETY IS OUR MAIN CONCERN"</div>
                </td>
            </tr>
            <tr>
                <td style="text-align: left; padding: 5px 0; font-size: 9px;">
                    <div><strong>DISTRIBUTION:</strong></div>
                    <div>Duplicate (BFP Copy - Email Attachment)</div>
                </td>
            </tr>
        </table>
    </div>
    </div>
    <div class="text-center mt-2">
        <small class="text-center small fw-bold">BFP-QSF-FSED-009 Rev. 01 (07.05.19)</small>
    </div>
</body>

</html>
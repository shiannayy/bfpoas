<?php 
include_once "../includes/_init.php";
if(isset($_GET['email_token']) && isset($_GET['schedule_id']) && isset($_GET['step'])){
    $data = $_GET;
    $schedule_id = intval($data['schedule_id']);
    $tokenExists = select('users',['email_token' => $data['email_token']]);
    $tokenExpired = false;
    if(!empty($tokenExists)){
        $tokenExpired = false;
        $user = $tokenExists[0];
        session_destroy();
        unset($_SESSION);

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['name']      = $user['full_name'];
            $_SESSION['subrole']  = $user['sub_role'] ?? null;
            $_SESSION['rolelabel'] = getRoleLabel($user['role'],$user['sub_role']); 

            $email_token = $data['email_token'];
            $step = $data['step'];
            $order_number = $data['order_number'];
            $owner_id = $data['owner_id'];
            $est_name = $data['est_name'];
            $recepient = $data['recepient'];

            if(isset($_GET['step']) && isset($_GET['schedule_id']) ){
                $ackStatus = acknowledgeSchedule($schedule_id, $user['user_id'], getRoleLabel($user['role'],$user['sub_role']) );
                switch(intval($_GET['step'])){
                    case 1: 
                         $query_params = [
                            'email_token' => $email_token,
                            'schedule_id' => $schedule_id,
                            'step' => 2,
                            'order_number' => $order_number,
                            'owner_id' => $owner_id,
                            'owner_name' => $recepientName,
                            'recepient' => $recepient,
                            'est_name' => $establishment
                            ];
                        $link = "../pages/wrapper_send_IO_mail.php?" . http_build_query($query_params);
                        header("location: {$link}");
                    break;
                    case 2:
                    default: $ackStatus = 0;
                }
            }
    }
    else{
        $tokenExpired = true;
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acknowledge Via Email</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
</head>
<body>
    <?php include_once "../pages/modals.php"; ?>
        <?php if( isset($ackStatus) && $ackStatus ) { ?>
            <?php if(!$tokenExpired){?>
            <h1 class="mt-5 text-center">Inspection Order has been acknowledged.</h1>
            <?php   //remove email token to prevent from accessing again.
                    update_data("users",['email_token'=> ''],['email_token' => $_GET['email_token']]);
            } 
            else{ ?>
                <h1 class="mt-5 text-center">Link Already been clicked before. It has already expired. Contact BFP Oas if you haven't acknowledged it yourself</h1>
            <?php }
                
            ?>
            <h3 class="text-center" id="countdown-timer"></h3>
        <?php }  ?>
</body>

<script>
setTimeout(function() {
    window.close();
}, 10000);

// Optional: Show countdown timer
let countdown = 10;
const countdownElement = document.getElementById('countdown-timer') || document.body;

const timerInterval = setInterval(function() {
    countdown--;
    if (countdownElement) {
        countdownElement.innerHTML = `This window will close in ${countdown} seconds...`;
    }
    if (countdown <= 0) {
        clearInterval(timerInterval);
        window.close();
    }
}, 1000);
</script>
</html>
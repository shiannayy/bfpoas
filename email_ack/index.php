<?php 
include_once "../includes/_init.php";

if (isset($_GET['email_token']) && isset($_GET['schedule_id']) && isset($_GET['step'])) {
    $email_token = $_GET['email_token'];
    $schedule_id = $_GET['schedule_id'];
    $step = htmlentities($_GET['step']);

    
    // Check if token exists in email_token table
    $token_info = select('email_token', ['email_token' => $email_token, 'schedule_id' => $schedule_id]);
    
    if (!empty($token_info)) {
        $token_data = $token_info[0];

        // Determine which user_id to use based on current step
        $user_id = null;
        switch($step) {
            case 1: // Client acknowledgement
                $user_id = $token_data['client_id'];
                break;
            case 2: // Inspector acknowledgement
                $user_id = $token_data['inspector_id'];
                break;
            case 3: // Chief FSES recommendation
                $user_id = $token_data['chiefFses_id'];
                break;
            case 4: // Fire Marshal approval
                $user_id = $token_data['fm_id'];
                break;
        }
        
        if ($user_id) {
            // Get user info
            $user_info = select('users', ['user_id' => $user_id])[0] ?? [];
            
            if (!empty($user_info)) {
                // Set session for acknowledgement
                $_SESSION['user_id'] = $user_info['user_id'];
                $_SESSION['role'] = $user_info['role'];
                $_SESSION['name'] = $user_info['full_name'];
                $_SESSION['subrole'] = $user_info['sub_role'] ?? null;
                $_SESSION['rolelabel'] = getRoleLabel($user_info['role'], $user_info['sub_role']);
                
                // Acknowledge the schedule
                $acknowledged = acknowledgeSchedule($schedule_id, $user_info['user_id'], getRoleLabel($user_info['role'], $user_info['sub_role']));
                
                if ($acknowledged && $step < 4) {
                    // Prepare next step
                    $next_step = $step + 1;
                    
                    // Determine next recipient based on next step
                    $next_user_id = null;
                    switch($next_step) {
                        case 2: // Next: Inspector
                            $next_user_id = $token_data['inspector_id'];
                            break;
                        case 3: // Next: Chief FSES
                            $next_user_id = $token_data['chiefFses_id'];
                            break;
                        case 4: // Next: Fire Marshal
                            $next_user_id = $token_data['fm_id'];
                            break;
                    }
                    
                    // // Get next recipient info
                    if ($next_user_id) {
                        $next_user_info = select('users', ['user_id' => $next_user_id])[0] ?? [];
                        $next_recipient = $next_user_info['email'] ?? Config::ADMIN_EMAIL;
                        $next_recipient_name = $next_user_info['full_name'] ?? 'Recipient';
                    }
                    
                    // Redirect to send next email
                    $redirect_url = "../pages/wrapper_send_IO_mail.php?email_token=" . $email_token .  
                                "&schedule_id=" . $schedule_id .  
                                "&step=" . $next_step;
                    header("Location: {$redirect_url}");
                    exit();
                }
                
                elseif ($acknowledged && $step == 4) {
                    // Final approval - send notification email
                    $ack_status = "acknowledged_and_approved";
                } else {
                    $ack_status = "acknowledged";
                }
            }
        }
    } else {
        $token_expired = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Acknowledgement</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 800px; 
            margin: 50px auto;
            text-align: center;
        }
        .status-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-box">
            <?php if (isset($ack_status) && $ack_status): ?>
                <?php if ($step == 4): ?>
                    <h1 class="success">✓ Inspection Order Approved!</h1>
                    <p class="lead">The inspection order has been fully approved and can now proceed as scheduled.</p>
                    <div class="alert alert-success mt-4">
                        <h5>Approval Complete</h5>
                        <p>All required approvals have been obtained. The inspection may proceed on the scheduled date.</p>
                        <p><small>An email notification has been sent to relevant parties.</small></p>
                    </div>
                <?php else: ?>
                    <h1 class="success">✓ Acknowledgement Received!</h1>
                    <p class="lead">Thank you for acknowledging the inspection order.</p>
                    <div class="alert alert-info mt-4">
                        <h5>Next Step Initiated</h5>
                        <p>The next person in the approval chain has been notified.</p>
                        <p><small>Step <?= $step ?> completed. Proceeding to step <?= $step + 1 ?>.</small></p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <p><strong>Schedule ID:</strong> <?= $schedule_id ?? 'N/A' ?></p>
                    <p><strong>Step Completed:</strong> <?= $step ?? 'N/A' ?></p>
                    <?php if (isset($order_number)): ?>
                        <p><strong>Order Number:</strong> <?= $order_number ?></p>
                    <?php endif; ?>
                </div>
                
            <?php elseif (isset($token_expired) && $token_expired): ?>
                <h1 class="error">✗ Link Expired or Invalid</h1>
                <p class="lead">This acknowledgement link has already been used or is no longer valid.</p>
                <div class="alert alert-warning mt-4">
                    <h5>What to do next:</h5>
                    <ul class="text-start">
                        <li>Check if you've already acknowledged this inspection order</li>
                        <li>Contact BFP-OAS if you need to re-send the acknowledgement link</li>
                        <li>Ensure you're using the most recent email link</li>
                    </ul>
                </div>
            <?php else: ?>
                <h1>Invalid Request</h1>
                <p>Required parameters are missing. Please use the link provided in your email.</p>
            <?php endif; ?>
            
            <div class="mt-5">
                <p id="countdown-timer" class="text-muted"></p>
                <button onclick="window.close()" class="btn btn-outline-secondary">Close Window</button>
            </div>
        </div>
    </div>

    <script>
    // Auto-close after 10 seconds
    setTimeout(function() {
        window.close();
    }, 10000);

    // Countdown timer
    let countdown = 10;
    const countdownElement = document.getElementById('countdown-timer');
    
    if (countdownElement) {
        const timerInterval = setInterval(function() {
            countdown--;
            countdownElement.textContent = `This window will close automatically in ${countdown} seconds...`;
            if (countdown <= 0) {
                clearInterval(timerInterval);
                window.close();
            }
        }, 1000);
    }
    
    // Optionally keep window open if user interacts
    document.addEventListener('click', function() {
        if (countdown > 3) {
            countdown = 3; // Reset to 3 seconds on interaction
        }
    });
    </script>
</body>
</html>
<?php
// cert.php - Certification Approval Handler
include_once "../includes/_init.php";

if(isset($_GET['email_sent']) && intval($_GET['email_sent']) == 1){ 
    if(isset($_GET['recipient'])){
        $recepient_url = htmlentities($_GET['recipient']);
        $act = ($recepient_url == 'ChiefFSES') ? 'Recommend' : ($recepient_url == 'FireMarshall' ? 'Approval' : 'Acknowledgement');
    }
    ?>
 <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Successful</title>
        <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 50px auto; }
            .success-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
            .success { color: #28a745; font-size: 48px; margin-bottom: 20px; }
            .compliance-rate { font-size: 36px; font-weight: bold; color: #28a745; margin: 20px 0; }
            .next-step { background: #e7f3ff; padding: 20px; border-radius: 5px; margin-top: 20px; text-align: left; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-6 offset-3">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-success">Email Sent for <?= $act ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>

<?php 
exit();
}

// Set response header
header('Content-Type: text/html; charset=UTF-8');

// Check required parameters
$token = $_GET['token'] ?? '';
$role = $_GET['role'] ?? '';
$schedule_id = $_GET['schedule_id'] ?? $_GET['scheduleId'] ?? 0;

// FIXED: Handle backward compatibility - if role not provided, use action
if (empty($role) && isset($_GET['action'])) {
    $action = $_GET['action'];
    // Map action to role
    $actionToRole = [
        'acknowledge' => 'client',
        'recommend' => 'ChiefFSES',
        'approve' => 'FireMarshall'
    ];
    $role = $actionToRole[$action];
}


// Validate token exists
$token_info = select('email_token', ['email_token' => $token]);
if (empty($token_info)) {
    showErrorPage("Invalid or expired token");
    exit();
}

$current_token_data = $token_info[0];
$token_id = $current_token_data['email_token_id'];

// If schedule_id not provided in URL, get it from token
if (!$schedule_id) {
    $schedule_id = $current_token_data['schedule_id'];
}

// Get inspection details
$inspection = getInspectionbySchedule($schedule_id)[0];

if (!$inspection) {
    showErrorPage("Inspection not found");
    exit();
}

// Check compliance rate (must be > 75%)
if (($inspection['compliance_rate'] ?? 0) <= 75) {
    showErrorPage("Compliance rate must be above 75% for certification");
    exit();
}

// Get or set user ID based on role
$user_id = null;
switch($role) {
    case 'client': 
        $user_id = $current_token_data['client_id'] ?? $_SESSION['user_id'] ?? null;
        break;
    case 'ChiefFSES': 
        $user_id = $current_token_data['chiefFses_id'] ?? $_SESSION['user_id'] ?? null;
        break;
    case 'FireMarshall': 
        $user_id = $current_token_data['fm_id'] ?? $_SESSION['user_id'] ?? null;
        break;
}

// If no user ID, require login
if (!$user_id) {
    $_SESSION['cert_redirect'] = $_SERVER['REQUEST_URI'];
    header("Location: ../?redirect=cert");
    exit();
}

// Get user info
$user_info = select('users', ['user_id' => $user_id]);
if (empty($user_info)) {
    showErrorPage("User not found");
    exit();
}

$user = $user_info[0];

// Set session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $user['role'];
$_SESSION['name'] = $user['full_name'];
$_SESSION['subrole'] = $user['sub_role'] ?? null;
$_SESSION['rolelabel'] = getRoleLabel($user['role'], $user['sub_role']);

// Handle the certification action
$result = handleCertAction($role, $schedule_id, $inspection, $user, $current_token_data);

if (is_string($result) && json_decode($result) !== null) {
    $error_data = json_decode($result, true);
    showErrorPage($error_data['message'] ?? 'An error occurred');
    exit();
}

if ($result['success']) {
    showSuccessPage($role, $schedule_id, $inspection, $result);
} else {
    showErrorPage($result['message']);
}

// ===================== FUNCTIONS =====================



function handleCertAction($role, $schedule_id, $inspection, $user, $token_data) {
    $inspection_id = $inspection['inspection_id'];
    $user_id = $user['user_id'];
    $role_label = $_SESSION['rolelabel'];
    $token_id = $token_data['email_token_id'];
    $current_token = $token_data['email_token'];
    
    $result = ['success' => false, 'message' => 'Invalid action'];
    
    switch($role) {
        case 'client':
            // Client acknowledgement
            $result = handleReceive($inspection, $role_label, $user_id, $inspection_id);
                // Check if errorResponse returned JSON string
            if (is_string($result) && json_decode($result) !== null) {
                $error_data = json_decode($result, true);
                $result = ['success' => $error_data['success'], 'message' => $error_data['message'] ?? 'Error'];
            }

            if ($result['success']) {
                // Update token with client_id
                update_data("email_token", 
                    ["client_id" => $user_id],
                    ["email_token_id" => $token_id]
                );
                
                // Get next recipient (Chief FSES)
                $next_info = getNextRecipient($role, $schedule_id);
                if ($next_info) {
                    $result['next_info'] = $next_info;
                }
            }
            break;
            
        case 'ChiefFSES':
            // Chief FSES recommendation
            $result = handleRecommend($inspection, $role_label, $user_id, $inspection_id);

            if (is_string($result) && json_decode($result) !== null) {
                $error_data = json_decode($result, true);
                $result = ['success' => $error_data['success'], 'message' => $error_data['message'] ?? 'Error'];
            }

            if ($result['success']) {
                // Update token with chiefFses_id
                update_data("email_token", 
                    ["chiefFses_id" => $user_id],
                    ["email_token_id" => $token_id]
                );
                
                // Get next recipient (Fire Marshal)
                $next_info = getNextRecipient($role, $schedule_id);
                if ($next_info) {
                    $result['next_info'] = $next_info;
                }
            }
            break;
            
        case 'FireMarshall':
            // Fire Marshal approval
            $result = handleApprove($inspection, $role_label, $user_id, $inspection_id);
            
           if (is_string($result) && json_decode($result) !== null) {
                $error_data = json_decode($result, true);
                $result = ['success' => $error_data['success'], 'message' => $error_data['message'] ?? 'Error'];
            }

            if ($result['success']) {
                // Get client info for certificate
                $next_info = getNextRecipient($role, $schedule_id);
                if ($next_info) {
                    $result['next_info'] = $next_info;
                }
            }
            break;
    }
    
    return $result;
}

function getNextRecipient($current_role, $schedule_id) {
    switch($current_role) {
        case 'client':
            // Get Chief FSES
            $chief = select('users', ['sub_role' => 'Chief FSES'], null, 1);
            if (!empty($chief)) {
                return [
                    'role' => 'ChiefFSES',
                    'email' => $chief[0]['email'],
                    'name' => $chief[0]['full_name']
                ];
            }
            break;
            
        case 'ChiefFSES':
            // Get Fire Marshal
            $fm = select('users', ['sub_role' => 'Fire Marshall'], null, 1);
            if (!empty($fm)) {
                return [
                    'role' => 'FireMarshall',
                    'email' => $fm[0]['email'],
                    'name' => $fm[0]['full_name']
                ];
            }
            break;
            
        case 'FireMarshall':
            // Get client from schedule
            $schedule = select("inspection_schedule", ["schedule_id" => $schedule_id]);
            if (!empty($schedule)) {
                $gen_info_id = $schedule[0]['gen_info_id'];
                $owner_info = getOwnerInfo($gen_info_id);
                if (!empty($owner_info)) {
                    $owner = select("users", ["user_id" => $owner_info[0]['user_id']]);
                    if (!empty($owner)) {
                        return [
                            'role' => 'client',
                            'email' => $owner[0]['email'],
                            'name' => $owner[0]['full_name']
                        ];
                    }
                }
            }
            break;
    }
    
    return null;
}

function showSuccessPage($role, $schedule_id, $inspection, $result) {
    $has_next = isset($result['next_info']) && $result['next_info'];
    $next_info = $has_next ? $result['next_info'] : null;
    
    // Get schedule info
    $schedule = select("inspection_schedule", ["schedule_id" => $schedule_id]);
    $establishment = $schedule[0]['proceed_instructions'] ?? 'Unknown Establishment';
    $order_number = $schedule[0]['order_number'] ?? '';
    
    // Action messages
    $action_messages = [
        'client' => 'Inspection Report Acknowledged',
        'ChiefFSES' => 'Recommended for Certification',
        'FireMarshall' => 'Certification Approved'
    ];
    $action_message = $action_messages[$role] ?? 'Action Completed';
    
    // Generate new token for next step if needed
    $new_token = '';
    if ($has_next && $next_info) {
        $new_token = bin2hex(random_bytes(32));
        update_data("email_token", 
            ["email_token" => $new_token, "update_ts" => date('Y-m-d H:i:s')],
            ["schedule_id" => $schedule_id]
        );
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Certification <?= ucfirst($role) ?> Successful</title>
        <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 50px auto; }
            .success-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
            .success { color: #28a745; font-size: 48px; margin-bottom: 20px; }
            .compliance-rate { font-size: 36px; font-weight: bold; color: #28a745; margin: 20px 0; }
            .next-step { background: #e7f3ff; padding: 20px; border-radius: 5px; margin-top: 20px; text-align: left; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-box">
                <div class="success">✓</div>
                <h2><?= $action_message ?></h2>
                
                <div class="compliance-rate">
                    <?= $inspection['compliance_rate'] ?? 0 ?>% Compliance
                </div>
                
                <div class="alert alert-success">
                    <p><strong><?= $result['message'] ?></strong></p>
                    <p>Schedule: <strong>#<?= $schedule_id ?></strong></p>
                    <p>Order: <strong><?= $order_number ?></strong></p>
                    <p>Establishment: <strong><?= htmlspecialchars($establishment) ?></strong></p>
                </div>
                
                <?php if ($has_next && $next_info && $new_token): ?>
                    <div class="next-step">
                        <h5>Next Step: <?= $next_info['role'] == 'ChiefFSES' ? 'Chief FSES Recommendation' : ($next_info['role'] == 'FireMarshall' ? 'Fire Marshal Approval' : 'Client Notification') ?></h5>
                        <p>Next recipient: <strong><?= $next_info['name'] ?></strong></p>
                        <p>Email: <code><?= $next_info['email'] ?></code></p>
                        
                        <form id="sendNextEmailForm" method="post" action="../email_ack/send_next_email.php">
                            <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
                            <input type="hidden" name="order_number" value="<?= $order_number ?>">
                            <input type="hidden" name="role" value="<?= $next_info['role'] ?>">
                            <input type="hidden" name="recipient_email" value="<?= $next_info['email'] ?>">
                            <input type="hidden" name="recipient_name" value="<?= $next_info['name'] ?>">
                            <input type="hidden" name="token" value="<?= $new_token ?>">
                            <input type="hidden" name="establishment" value="<?= htmlspecialchars($establishment) ?>">
                            <input type="hidden" name="compliance_rate" value="<?= $inspection['compliance_rate'] ?? 0 ?>">
                            
                            <div id="redirect-countdown" class="mt-3">
                                Automatically sending email in <span id="countdown">5</span> seconds...
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Send Email (if it didn't work)
                                </button>
                            </div>
                        </form>
                    </div>
                <?php elseif (!$has_next): ?>
                    <div class="next-step bg-light">
                        <h5 class="text-success">✓ Process Complete</h5>
                        <p>The certification has been fully approved.</p>
                        <p>An email with the certificate will be sent to the client.</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <button onclick="window.close()" class="btn btn-outline-secondary me-2">Close</button>
                    <a href="../" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </div>
        
        <?php if ($has_next && $next_info && $new_token): ?>
        <script>
            // Auto-submit form after countdown
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            
            const countdownInterval = setInterval(function() {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    console.log("Sends Next Email.");
                    document.getElementById('sendNextEmailForm').submit();
                }
            }, 1000);
            
            // Manual submit
            document.querySelector('#sendNextEmailForm button').addEventListener('click', function(e) {
                e.preventDefault();
                clearInterval(countdownInterval);
                document.getElementById('sendNextEmailForm').submit();
            });
        </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
}

function showErrorPage($message) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Certification</title>
        <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 50px auto; }
            .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
            .error { color: #dc3545; font-size: 48px; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-box">
                <div class="error">✗</div>
                <h3>Error</h3>
                <div class="alert alert-danger mt-4">
                    <?= htmlspecialchars($message) ?>
                </div>
                <div class="mt-4">
                    <button onclick="window.history.back()" class="btn btn-outline-secondary me-2">Go Back</button>
                    <button onclick="window.close()" class="btn btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
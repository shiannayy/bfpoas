<?php
header('Content-Type: application/json');

include "../includes/_init.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

    // Determine redirect path
    $redirect = "../public/index.php"; // fallback default

if (empty($username) || empty($password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Username and password are required."
    ]);
    exit;
}

// Fetch user from DB
$user = select("users", ["email" => $username ], null, 1);

if(!empty($user) && $user[0]['is_active'] === 0 ){
  echo json_encode([
        "status" => "error",
        "message" => "Unable to Login, User has been Disabled",
        "redirect" => $redirect
    ]);
    exit;
}

if ($user && password_verify($password, $user[0]['password_hash'])) {
    $user = $user[0];

    // Store session data
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['name']      = $user['full_name'];
    $_SESSION['subrole']  = $user['sub_role'] ?? null;
    $_SESSION['rolelabel'] = getRoleLabel($user['role'],$user['sub_role']); 
    
    
     /*log*/
    $now = date("Y-m-d H:i:s");
    $fn = getUserInfo($user['user_id']);
    $msg = $fn . "(".$user['role'].") " . " has logged-in"; 
    sys_log($msg,'login',$user['user_id']);
    /*end log*/

    if ($user['role'] === "Administrator") {
        $redirect = "../admin/index.php";
    } elseif ($user['role'] === "Inspector") {
        $redirect = "../inspector/index.php";
    } elseif ($user['role'] === "Client") {
        $redirect = "../client/index.php";
    }

    echo json_encode([
        "status" => "success",
        "message" => "Login successful!",
        "redirect" => $redirect
    ]);
    exit;
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid username or password."
    ]);
    exit;
}

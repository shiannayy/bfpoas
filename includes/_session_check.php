<?php
require_once "../includes/_init.php";

header('Content-Type: application/json');



if (isset($_SESSION['user_id'])) {
    
    //setRoleLabel Sessions for later use
    
    
    
    echo json_encode([
        "logged_in" => isLoggedIn(),
        "user" => [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['name'] ?? "Unknown User",
            "role" => $_SESSION['role'] ?? null,
            "subrole" => $_SESSION['subrole'] ?? null
        ]
    ]);
} else {
    echo json_encode(["logged_in" => false]);
}
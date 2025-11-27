<?php
require_once "../includes/_init.php"; 

header('Content-Type: application/json');

// Input
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$contactNo = trim($_POST['contactNo'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';
$subrole   = $_POST['subrole'] ?? '';

// ---- VALIDATION ----

// Required fields
if (empty($full_name) || empty($email) || empty($contactNo) || empty($password) || empty($role)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// 1. EMAIL VALIDATION
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit;
}

// 2. CONTACT NUMBER VALIDATION — numeric only, length=11
if (!preg_match('/^[0-9]{11}$/', $contactNo)) {
    echo json_encode(["status" => "error", "message" => "Contact number must be exactly 11 digits."]);
    exit;
}

// 3. PASSWORD VALIDATION
// - 8 chars
// - 1 uppercase
// - 1 number
// - 1 special char
$pwRegex = '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/';

if (!preg_match($pwRegex, $password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Password must be at least 8 characters long and include an uppercase letter, a number, and a special symbol."
    ]);
    exit;
}

// Check if email already exists
$existing = select("users", ["email" => $email]);
if (!empty($existing)) {
    echo json_encode(["status" => "error", "message" => "Email already registered."]);
    exit;
}
// Check if email already exists
$existingName = select("users", ["full_name" => $full_name]);
if (!empty($existingName)) {
    echo json_encode(["status" => "error", "message" => "User's Name already registered."]);
    exit;
}

// Hash password securely
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Prepare data
$data = [
    "full_name"     => $full_name,
    "email"         => $email,
    "contact_no"    => $contactNo,
    "password_hash" => $password_hash,
    "role"          => $role,
    "sub_role"      => $subrole
];

// Insert
if (insert_data("users", $data)) {
    echo json_encode(["status" => "success", "message" => "User registered successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed."]);
}

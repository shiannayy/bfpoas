<?php
include "../includes/_init.php";

header("Content-Type: application/json");

// Sanitize inputs
$user_id = intval($_POST['user_id']);
$role    = preg_replace("/[^a-zA-Z0-9]/", "", $_POST['role']); 
$image   = $_POST['image'] ?? '';

if (!$user_id || !$image) {
    echo json_encode(["status" => "error", "message" => "Missing required data"]);
    exit;
}

// Prepare folder
$folder = "../assets/signatures/";

// Filename format: SIGN-userid-role.png
$filename = "SIGN-" . $user_id . "-" . $role . ".png";
$path     = $folder . $filename;

// Decode base64 image and save
$data = explode(',', $image);
if (count($data) !== 2) {
    echo json_encode(["status" => "error", "message" => "Invalid image data"]);
    exit;
}
file_put_contents($path, base64_decode($data[1]));

// Update users table with the filename
$now = date("Y-m-d H:i:s");
$update = update_data("users", ["signature" => $filename, "updated_at"=> $now], ["user_id" => $user_id]);

if ($update) {
    echo json_encode([
        "status" => "success",
        "message" => "Signature saved successfully",
        "file"   => $filename
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update user record",
        "file" => $filename,
        "user_id" => $user_id
    ]);
}

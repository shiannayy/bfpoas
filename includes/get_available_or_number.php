<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

try {
    // Step 1: If session OR_NUMBER exists, validate it
    if (isset($_SESSION['OR_NUMBER'])) {
        $or_number = $_SESSION['OR_NUMBER'];
        $exist = select("payment", ['OR_number' => $or_number]);

        // If existing in payment table, invalidate session
        if (!empty($exist)) {
            unset($_SESSION['OR_NUMBER']);
        }
    }

    // Step 2: If no valid OR_NUMBER in session, generate a new one
    if (!isset($_SESSION['OR_NUMBER'])) {
        do {
            $or_number = Config::REGION . randomNDigits(5);
            $exist = select("payment", ['OR_number' => $or_number]);
        } while (!empty($exist));

        // Store in session
        $_SESSION['OR_NUMBER'] = $or_number;
    } else {
        $or_number = $_SESSION['OR_NUMBER'];
    }

    // Step 3: Return JSON
    echo json_encode([
        "success"   => true,
        "or_number" => $or_number
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

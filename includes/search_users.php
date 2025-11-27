<?php
require_once "../includes/_init.php";
$term = $_GET['term'] ?? '';
$output = [];

if (!empty($term)) {
    $users = select(
        "users",
        ["full_name" => "%" . $term . "%", "role" => "Client"],
        ["full_name" => "ASC"],
        10
    );

    foreach ($users as $row) {
        $output[] = [
            "label" => $row['full_name'], // shown in dropdown
            "value" => $row['full_name'], // filled in input
            "user_id" => $row['user_id'],  // optional hidden value
            "email" => $row['email'],
            "contactNo" => $row['contact_no']
        ];
    }
}
echo json_encode($output);

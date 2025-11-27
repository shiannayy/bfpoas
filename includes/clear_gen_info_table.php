<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "",
    "deleted_count" => 0,
    "data" => []
];

try {
    $to_clear = query("
        SELECT gen_info_id, created_at, updated_at 
        FROM general_info
        WHERE gen_info_status = 'Draft'
          AND TIMESTAMPDIFF(HOUR, created_at, NOW()) > 1
          AND TIMESTAMPDIFF(HOUR, updated_at, NOW()) > 1
    ");

    if (!empty($to_clear)) {
        $deleted = 0;
        foreach ($to_clear as $data) {
            $id = intval($data['gen_info_id']);
            if (delete_data("general_info", ["gen_info_id" => $id])) {
                $deleted++;
                $response["data"][] = [
                    "id" => $id,
                    "created_at" => $data["created_at"],
                    "updated_at" => $data["updated_at"]
                ];
                insert_data("logs", [
                    "log_action"  => "auto cleanup general_info",
                    "log_message" => "Removed expired draft (created {$data['created_at']})",
                    "gen_info_id" => $id
                ]);
            }
        }

        $response["success"] = true;
        $response["deleted_count"] = $deleted;
        $response["message"] = "$deleted expired draft record(s) removed.";
    } else {
        $response["success"] = true;
        $response["message"] = "No expired drafts found.";
    }
} catch (Exception $e) {
    $response["message"] = "Error: " . $e->getMessage();
}

mysqli_close($CONN);
echo json_encode($response);
?>

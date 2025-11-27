<?php
require_once "../includes/_init.php";

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$results = [];

if ($term !== '') {
    $like = "%" . $term . "%";

    $sql = "SELECT checklist_id, fsed_code, title
            FROM checklists
            WHERE checklist_status = 1
              AND (fsed_code LIKE ? OR title LIKE ?)
            ORDER BY title ASC
            LIMIT 10";

    $rows = query($sql, [$like, $like]);

    foreach ($rows as $row) {
        $results[] = [
            "id"    => $row['checklist_id'],
            "label" => $row['fsed_code'] . " - " . $row['title'],
            "value" => $row['fsed_code'] . " - " . $row['title']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($results);

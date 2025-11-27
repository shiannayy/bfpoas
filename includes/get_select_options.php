<?php
require_once "../includes/_init.php";

$item_id = $_POST['item_id'] ?? 0;

$list = select("checklist_item_select_options", ["item_id" => $item_id]);

echo json_encode($list);
?>

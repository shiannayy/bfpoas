<?php
include "../includes/_init.php";

if(isset($_POST['schedule_id'])){
    $id = intval($_POST['schedule_id']);
    $row = getSchedInfo($id);
    echo json_encode($row);
}

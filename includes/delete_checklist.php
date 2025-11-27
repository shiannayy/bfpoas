<?php 
include_once "../includes/_init.php";
enforceRoleAccess(['Administrator']);

if(isLoggedIn()){
    $user_id = $_SESSION['user_id'];
}
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $item_id  = intval($_POST['item_id']);
    $password = $_POST['password'];
    $disableOnly = $_POST['disableOnly'];
    
    $user = select("users", ["user_id" => $user_id, "sub_role" => 'Admin_Assistant' ], null, 1);
    if ($user && password_verify($password, $user[0]['password_hash'])) {
        
        if($disableOnly == 0){
            $aff = delete_data("checklist_items",['item_id' => $item_id]);
            if($aff > 0){
                echo json_encode(["status" => "success", "message" => "Checklist Item Deleted Successfully","action" => "delete"]);
                exit;   
            }
            else{
                echo json_encode(["status" => "failed", "message" => "Item not found."]);
                exit;
            } 
        } 
        else if($disableOnly == 1){
            $aff = update_data("checklist_items",['chk_item_status' => 0 ],['item_id' => $item_id]);
            if($aff > 0){
                echo json_encode(["status" => "success", "message" => "Checklist Item Disabled Successfully","action" => "disable"]);
                exit;   
            }
            else{
                echo json_encode(["status" => "failed", "message" => "Item not found."]);
                exit;
            } 
        }
        else{
            $aff = update_data("checklist_items",['chk_item_status' => 1 ],['item_id' => $item_id]);
            if($aff > 0){
                echo json_encode(["status" => "success", "message" => "Checklist Item Re-Enabled Successfully", "action" => "enable"]);
                exit;   
            }
            else{
                echo json_encode(["status" => "failed", "message" => "Item not found."]);
                exit;
            } 
        }
           
    }
    else{
        echo json_encode(["status" => "failed", "message" => "Incorrect Admin_Assistant Password"]);
        exit;
    }
}

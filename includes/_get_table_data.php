<?php
require_once "../includes/_init.php";
// assuming your select_data() is here

header( 'Content-Type: application/json' );
$table = null;
$where = [];
$orderby = null;
$limit = null;

if ( isset( $_POST['table'] ) ) {
    $table = $_POST['table'];
    
    if(isset($_POST['where'])){
        $where = $_POST['where'];
    }
    if(isset($_POST['orderby'])){
        $orderby = $_POST['orderby'];
    }
    if(isset($_POST['limit'])){
        $limit = $_POST['limit'];
    }

    $result = select_data( $table, $where, $orderby, $limit );
    
    $data = is_array( $result ) ? $result : null;
    $count = is_array( $result ) ? count( $result ) : 0;
    
    echo json_encode( ['table' => $table, 'count' => $count, 'data' => $data] );



} else {
    echo json_encode( ['error' => 'Missing parameter: table'] );
}

?>
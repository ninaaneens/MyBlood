<?php
include('../db_connect.php');

if(isset($_POST['loc_id'])) {
    $loc_id = mysqli_real_escape_string($conn, $_POST['loc_id']);
    
    $query = "SELECT * FROM location WHERE loc_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $loc_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Location not found'
        ]);
    }
}
?>

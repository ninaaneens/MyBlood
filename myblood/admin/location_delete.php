<?php
include('../db_connect.php');

if(isset($_POST['id'])) {
    $loc_id = mysqli_real_escape_string($conn, $_POST['id']);
    
    // First get the image filename
    $query = "SELECT image FROM location WHERE loc_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $loc_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    // Delete the location from database
    $delete_query = "DELETE FROM location WHERE loc_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $loc_id);
    
    if(mysqli_stmt_execute($delete_stmt)) {
        // If database deletion successful, delete the image file
        if($row && $row['image']) {
            $image_path = "../assets/images/" . $row['image'];
            if(file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $response = [
            'success' => true,
            'message' => 'Location deleted successfully'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error deleting location: ' . mysqli_error($conn)
        ];
    }
    
    mysqli_stmt_close($delete_stmt);
} else {
    $response = [
        'success' => false,
        'message' => 'Location ID not provided'
    ];
}

mysqli_close($conn);
header('Content-Type: application/json');
echo json_encode($response);
?>

<?php
include('../db_connect.php');

if(isset($_POST['update_location'])) {
    // Sanitize inputs
    $loc_id = mysqli_real_escape_string($conn, $_POST['loc_id']);
    $loc_name = mysqli_real_escape_string($conn, $_POST['loc_name']);
    $loc_state = mysqli_real_escape_string($conn, $_POST['loc_state']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);

    // Initialize image query part
    $imageQuery = "";

    // Handle image update if new image is uploaded
    if(!empty($_FILES['image']['name'])) {
        // Get old image name to delete
        $oldImageQuery = "SELECT image FROM location WHERE loc_id = ?";
        $stmt = mysqli_prepare($conn, $oldImageQuery);
        mysqli_stmt_bind_param($stmt, "i", $loc_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $oldImage = mysqli_fetch_assoc($result)['image'];

        // Delete old image if exists
        if($oldImage && file_exists("../assets/images/" . $oldImage)) {
            unlink("../assets/images/" . $oldImage);
        }

        // Upload new image
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $image);
        $imageQuery = ", image = '$image'";
    }

    // Update query with prepared statement
    $query = "UPDATE location SET 
              loc_name = ?, 
              loc_state = ?, 
              address = ?, 
              latitude = ?, 
              longitude = ?, 
              rating = ? 
              $imageQuery 
              WHERE loc_id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssdddi", 
        $loc_name, 
        $loc_state, 
        $address, 
        $latitude, 
        $longitude, 
        $rating, 
        $loc_id
    );

    if(mysqli_stmt_execute($stmt)) {
        $response = [
            'success' => true,
            'message' => 'Location updated successfully'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error updating location: ' . mysqli_error($conn)
        ];
    }

    mysqli_stmt_close($stmt);
} else {
    $response = [
        'success' => false,
        'message' => 'Missing required data'
    ];
}

mysqli_close($conn);
header('Content-Type: application/json');
echo json_encode($response);
?>

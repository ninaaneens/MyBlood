<?php
include('../db_connect.php');

if(isset($_POST['loc_name'])) {
    // Sanitize text inputs
    $loc_name = mysqli_real_escape_string($conn, $_POST['loc_name']);
    $loc_state = mysqli_real_escape_string($conn, $_POST['loc_state']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);

    // Handle image upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Get image file information
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        
        // Generate unique filename to prevent overwriting
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $image = 'location_' . time() . '.' . $image_ext;
        
        // Set upload directory
        $upload_dir = '../assets/images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Move uploaded file to destination
        if(move_uploaded_file($image_tmp, $upload_dir . $image)) {
            // Insert into database
            $query = "INSERT INTO location (loc_name, loc_state, address, latitude, longitude, rating, image) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssddds", $loc_name, $loc_state, $address, $latitude, $longitude, $rating, $image);
            
            if(mysqli_stmt_execute($stmt)) {
                $response = [
                    'success' => true,
                    'message' => 'Location added successfully'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Database error: ' . mysqli_error($conn)
                ];
            }
            mysqli_stmt_close($stmt);
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to upload image'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'No image uploaded or invalid image file'
        ];
    }
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

<?php
include('../db_connect.php'); // Include database connection

// Check if the required POST parameters are set
if (isset($_POST['date'], $_POST['time'], $_POST['status'], $_POST['loc_id'], $_POST['slot_id'])) {
    // Sanitize and assign values
    $slot_id = mysqli_real_escape_string($conn, $_POST['slot_id']);
    $avail_date = mysqli_real_escape_string($conn, $_POST['date']);
    $avail_time = mysqli_real_escape_string($conn, $_POST['time']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $loc_id = mysqli_real_escape_string($conn, $_POST['loc_id']);

    // Update query to modify the time slot data
    $query = "UPDATE available_time_slots 
              SET avail_date = '$avail_date', avail_time = '$avail_time', status = '$status', loc_id = '$loc_id' 
              WHERE slot_id = '$slot_id'";

    // Execute the query and check for success
    if (mysqli_query($conn, $query)) {
        $response = [
            'success' => true,
            'message' => 'Time slot updated successfully!'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error updating time slot: ' . mysqli_error($conn)
        ];
    }
} else {
    // If necessary parameters are missing
    $response = [
        'success' => false,
        'message' => 'Missing required parameters.'
    ];
}

// Close the database connection
mysqli_close($conn);

// Return the response as JSON
echo json_encode($response);
?>

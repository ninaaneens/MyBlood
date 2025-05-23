<?php
// Include database connection
include('../db_connect.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $avail_date = mysqli_real_escape_string($conn, $_POST['avail_date']);
    $avail_time = mysqli_real_escape_string($conn, $_POST['avail_time']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $loc_id = intval($_POST['loc_id']); // Ensure loc_id is an integer

    // Set admin_id to 1 by default
    $admin_id = 1;

    // Validate required fields
    if (empty($avail_date) || empty($avail_time) || empty($status) || empty($loc_id)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Prepare the SQL query using a prepared statement
    $query = $conn->prepare("INSERT INTO available_time_slots (avail_date, avail_time, status, loc_id, admin_id) 
                             VALUES (?, ?, ?, ?, ?)");
    $query->bind_param('sssss', $avail_date, $avail_time, $status, $loc_id, $admin_id);

    // Execute the query
    if ($query->execute()) {
        echo json_encode(['success' => true, 'message' => 'Time slot added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add time slot: ' . $query->error]);
    }

    // Close the prepared statement
    $query->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Close the database connection
mysqli_close($conn);
?>

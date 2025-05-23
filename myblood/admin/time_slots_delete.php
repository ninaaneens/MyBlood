<?php
// Include database connection
include('../db_connect.php');

// Check if the request method is POST and if slot_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']); // Ensure slot_id is an integer

    // Prepare the DELETE query
    $query = "DELETE FROM available_time_slots WHERE slot_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $slot_id);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Time slot deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete time slot: ' . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

// Close the database connection
$conn->close();

<?php
include('../db_connect.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = intval($_POST['app_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validate input
    if (empty($app_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    // Update query
    $query = "UPDATE appointments SET status = '$status', updated_at = NOW() WHERE app_id = $app_id";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update appointment status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

mysqli_close($conn);
?>

<?php
include('../db_connect.php'); // Include database connection

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete related records in the eligibility table
    $deleteEligibilityQuery = "DELETE FROM eligibility WHERE user_id = $user_id";
    mysqli_query($conn, $deleteEligibilityQuery);

    // Delete related records in the appointments table
    $deleteAppointmentsQuery = "DELETE FROM appointments WHERE user_id = $user_id";
    mysqli_query($conn, $deleteAppointmentsQuery);

    // Delete the user from the users table
    $deleteUserQuery = "DELETE FROM users WHERE user_id = $user_id";
    if (mysqli_query($conn, $deleteUserQuery)) {
        header("Location: donor.php"); // Redirect back to donor list after deletion
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>

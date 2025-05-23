<?php
session_start();

// Check session for user login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
include('db_connect.php');

// Handle appointment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null;

    // Handle cancellation
    if (isset($_POST['cancel']) && $appointment_id) {
        // Fetch current slot ID
        $get_current_slot_sql = "SELECT slot_id FROM appointments WHERE app_id = ? AND user_id = ?";
        $stmt = $conn->prepare($get_current_slot_sql);
        $stmt->bind_param("ii", $appointment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $current_slot_id = $result->fetch_assoc()['slot_id'];
        } else {
            header('Location: dashboard.php?error=not_found');
            exit();
        }
        $stmt->close();

        // Cancel the appointment and free up the slot
        $conn->begin_transaction();
        try {
            $cancel_sql = "UPDATE appointments SET status = 'cancelled' WHERE app_id = ? AND user_id = ?";
            $cancel_stmt = $conn->prepare($cancel_sql);
            $cancel_stmt->bind_param("ii", $appointment_id, $user_id);
            $cancel_stmt->execute();

            $update_slot_sql = "UPDATE available_time_slots SET status = 'available' WHERE slot_id = ?";
            $update_slot_stmt = $conn->prepare($update_slot_sql);
            $update_slot_stmt->bind_param("i", $current_slot_id);
            $update_slot_stmt->execute();

            $conn->commit();
            echo "<script>alert('Appointment cancelled successfully.'); window.location.href='dashboard.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: dashboard.php?error=cancel_failed');
        }

        exit();
    }

    // Handle appointment update
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : null;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : null;
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : null;

    if (empty($appointment_id) || empty($location_id) || empty($appointment_date) || empty($appointment_time)) {
        header('Location: dashboard.php?error=missing_fields');
        exit();
    }

    // Fetch the current slot ID
    $get_current_slot_sql = "SELECT slot_id FROM appointments WHERE app_id = ? AND user_id = ?";
    $stmt = $conn->prepare($get_current_slot_sql);
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $current_slot_id = $result->fetch_assoc()['slot_id'];
    } else {
        header('Location: dashboard.php?error=not_found');
        exit();
    }
    $stmt->close();

    // Check if the selected slot is available
    $check_slot_sql = "SELECT slot_id FROM available_time_slots 
                       WHERE loc_id = ? AND avail_date = ? AND avail_time = ? AND status = 'available'";
    $stmt = $conn->prepare($check_slot_sql);
    $stmt->bind_param("iss", $location_id, $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('The selected time slot is no longer available.'); window.location.href='dashboard.php';</script>";
        exit();
    }

    $slot_id = $result->fetch_assoc()['slot_id'];
    $stmt->close();

    // Update the appointment and slots
    $conn->begin_transaction();
    try {
        $update_appointment_sql = "UPDATE appointments SET slot_id = ?, status = 'pending' WHERE app_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_appointment_sql);
        $stmt->bind_param("iii", $slot_id, $appointment_id, $user_id);
        $stmt->execute();

        $update_new_slot_sql = "UPDATE available_time_slots SET status = 'booked' WHERE slot_id = ?";
        $stmt = $conn->prepare($update_new_slot_sql);
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();

        $update_old_slot_sql = "UPDATE available_time_slots SET status = 'available' WHERE slot_id = ?";
        $stmt = $conn->prepare($update_old_slot_sql);
        $stmt->bind_param("i", $current_slot_id);
        $stmt->execute();

        $conn->commit();
        echo "<script>alert('Appointment updated successfully.'); window.location.href='dashboard.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: dashboard.php?error=update_failed');
    }
} else {
    header('Location: dashboard.php?error=invalid_request');
    exit();
}

$conn->close();
?>

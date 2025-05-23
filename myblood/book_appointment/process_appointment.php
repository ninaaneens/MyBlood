<?php
session_start();
include('header_appointment.php');

// Check session for user login and eligibility
if (!isset($_SESSION['user_id']) || !isset($_SESSION['eligibility_checked'])) {
    header('Location: eligibility_check.php');
    exit();
}

// Database connection
include('../db_connect.php');

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : null;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : null;
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : null;

    // Check for required fields
    if (empty($location_id) || empty($appointment_date) || empty($appointment_time)) {
        displayMessage("All fields are required. Please go back and try again.", "danger");
        exit();
    }

    // Check if user has a pending appointment
    $pending_appointment_sql = "SELECT app_id FROM appointments 
                                WHERE user_id = ? AND status = 'pending'";
    $pending_appointment_stmt = $conn->prepare($pending_appointment_sql);
    $pending_appointment_stmt->bind_param("i", $user_id);
    $pending_appointment_stmt->execute();
    $pending_appointment_result = $pending_appointment_stmt->get_result();

    if ($pending_appointment_result->num_rows > 0) {
        echo "<script>alert('You already have a pending appointment. Please complete or cancel it before booking a new one.'); window.location.href='../dashboard.php';</script>";
        exit();
    }

    // Check if the user donated less than 6 months ago
    $eligibility_sql = "SELECT last_blood_donation_date FROM eligibility WHERE user_id = ?";
    $eligibility_stmt = $conn->prepare($eligibility_sql);
    $eligibility_stmt->bind_param("i", $user_id);
    $eligibility_stmt->execute();
    $eligibility_result = $eligibility_stmt->get_result();
    $eligibility_data = $eligibility_result->fetch_assoc();

    if ($eligibility_data && $eligibility_data['last_blood_donation_date']) {
        $last_donation_date = new DateTime($eligibility_data['last_blood_donation_date']);
        $six_months_ago = (new DateTime())->modify('-6 months');
        if ($last_donation_date > $six_months_ago) {
            echo "<script>alert('You are not eligible to book an appointment as your last donation was less than 6 months ago.'); window.location.href='../dashboard.php';</script>";
            exit();
        }
    }

    // Check if the selected slot is still available
    $check_slot_sql = "SELECT slot_id FROM available_time_slots 
                       WHERE loc_id = ? AND avail_date = ? AND avail_time = ? AND status = 'available'";
    $check_slot_stmt = $conn->prepare($check_slot_sql);
    $check_slot_stmt->bind_param("iss", $location_id, $appointment_date, $appointment_time);
    $check_slot_stmt->execute();
    $check_slot_result = $check_slot_stmt->get_result();

    if ($check_slot_result->num_rows === 0) {
        displayMessage("The selected time slot is no longer available. Please choose another slot.", "warning");
        exit();
    }

    // Fetch the slot ID and location details
    $slot = $check_slot_result->fetch_assoc();
    $slot_id = $slot['slot_id'];

    $location_sql = "SELECT loc_name FROM location WHERE loc_id = ?";
    $location_stmt = $conn->prepare($location_sql);
    $location_stmt->bind_param("i", $location_id);
    $location_stmt->execute();
    $location_result = $location_stmt->get_result();
    $location = $location_result->fetch_assoc();
    $location_name = $location['loc_name'];

    // Insert appointment into the database
    $insert_appointment_sql = "INSERT INTO appointments (status, user_id, slot_id) 
                               VALUES ('pending', ?, ?)";
    $insert_appointment_stmt = $conn->prepare($insert_appointment_sql);
    $insert_appointment_stmt->bind_param("ii", $user_id, $slot_id);

    if ($insert_appointment_stmt->execute()) {
        // Update the time slot status to 'pending'
        $update_slot_sql = "UPDATE available_time_slots SET status = 'pending' WHERE slot_id = ?";
        $update_slot_stmt = $conn->prepare($update_slot_sql);
        $update_slot_stmt->bind_param("i", $slot_id);
        $update_slot_stmt->execute();

        // Display success message with appointment details
        displayMessage("Appointment Booked Successfully!", "success", $appointment_date, $appointment_time, $location_name);
    } else {
        displayMessage("An error occurred while booking your appointment. Please try again later.", "danger");
    }

    // Close the statements
    $insert_appointment_stmt->close();
    $update_slot_stmt->close();
    $check_slot_stmt->close();
    $pending_appointment_stmt->close();
    $eligibility_stmt->close();
} else {
    displayMessage("Invalid request method.", "danger");
    exit();
}

// Close the database connection
$conn->close();

// Function to display messages
function displayMessage($message, $type, $appointment_date = null, $appointment_time = null, $location_name = null) {
    echo "<html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Appointment Booking - MYBlood</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
            .container-dashboard { max-width: 1200px; margin: 40px auto; padding: 20px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
            .navbar { display: flex; align-items: center; justify-content: space-between; background-color: #D32F2F; padding: 10px 20px; color: white; }
            .navbar img { height: 30px; }
            .navbar span { font-size: 16px; }
            .navbar .user-info { display: flex; align-items: center; }
            .navbar .username { display: flex; align-items: center; gap: 10px; }
            .navbar .user-avatar { width: 30px; height: 30px; border-radius: 50%; margin-right: 10px; }
            .progress-steps { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; padding: 0 50px; }
            .step { text-align: center; position: relative; z-index: 1; }
            .step-number { width: 30px; height: 30px; background-color: #ccc; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 10px; }
            .step.completed .step-number { background-color: #28a745; }
            .step.active .step-number { background-color: #D32F2F; }
            .location-header { display: flex; align-items: center; margin-bottom: 20px; }
            .location-header i { margin-right: 10px; font-size: 24px; }
            .form-control { border-radius: 5px; padding: 10px; margin-bottom: 20px; }
            .confirm-btn { background-color: #000; color: white; width: 100%; padding: 12px; border: none; border-radius: 5px; margin-top: 20px; }
            .back-link { display: block; text-align: center; margin-top: 15px; color: #D32F2F; text-decoration: none; }
			.btn-success {
                background-color: #000;
                color: white;
                border: none;
                padding: 12px;
                border-radius: 5px;
                font-size: 16px;
            }
            .btn-success:hover {
                background-color: #333;
            }
			.alert {
                border-radius: 5px;
                padding: 15px;
                font-size: 16px;
                margin-top: 20px;
            }
			.alert-info{
                background-color: white;
            }
			footer {
		background-color: #000000; /* Black background */
		color: #ffffff; /* White text */
		text-align: center;
		padding: 20px;
		font-family: Arial, sans-serif;
		margin-top: auto;
	}

	footer p {
		margin: 0;
		font-size: 14px;
	}
        </style>
    </head>
    <body>
        <div class='container-dashboard'>
            <div class='progress-steps'>
                <div class='step completed'>
                    <div class='step-number'>✓</div>
                    <div class='step-text'>Check Eligibility</div>
                </div>
                <div class='step completed'>
                    <div class='step-number'>✓</div>
                    <div class='step-text'>Choose Locations</div>
                </div>
                <div class='step completed'>
                    <div class='step-number'>✓</div>
                    <div class='step-text'>Book Appointment</div>
                </div>
            </div>
            <h2>Appointment Booked Successfully!</h2>
            <p>Your appointment has been successfully booked. Here are your appointment details:</p>
            <div class='alert alert-info'>
                <strong>Date:</strong> " . htmlspecialchars($appointment_date) . "<br>
                <strong>Time:</strong> " . htmlspecialchars($appointment_time) . "<br>
                <strong>Location:</strong> " . htmlspecialchars($location_name) . "
            </div>
            <a href='../dashboard.php' class='btn btn-success btn-block'>Go to Dashboard</a>
        </div>
		<!-- Footer -->
    <footer>
        <p>&copy; 2025 MYBlood. All Rights Reserved.</p>
    </footer>
    </body>
    </html>";
}
?>

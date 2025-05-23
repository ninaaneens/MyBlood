<?php
session_start();

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
        </style>
    </head>
    <body>
        <div class='navbar'>
            <div class='logo'>
                <img src='./assets/images/logo.png' alt='MyBlood Logo'>
                MyBlood
            </div>
            <div class='user-info'>
                <span class='username'>
                    " . htmlspecialchars($_SESSION['username']) . "
                    <img src='./assets/images/user-avatar.png' alt='User Avatar' class='user-avatar'>
                </span>
            </div>
        </div>
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
    </body>
    </html>";
}
?>

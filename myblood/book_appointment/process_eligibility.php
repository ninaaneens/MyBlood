<?php
session_start();
include('../db_connect.php');  // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $weight = floatval($_POST['weight']);
    $age = intval($_POST['age']);
    
    // Collect the last donation date and validate it
    $lastDonation = $_POST['lastDonation'] ?: null; // Use null if no date provided
    if ($lastDonation) {
        // Validate and format the date
        $lastDonationDate = new DateTime($lastDonation);
        $lastDonation = $lastDonationDate->format('Y-m-d'); // Ensure it's in Y-m-d format
    } else {
        $lastDonation = null; // Set to null if no date selected
    }
    
    // Collect other form data
    $medicalConditions = ($_POST['medicalConditions'] === 'Yes') ? 1 : 0;
    $medications = ($_POST['medications'] === 'Yes') ? 1 : 0;
    $recentIllness = ($_POST['recentIllness'] === 'Yes') ? 1 : 0;
    $malariaTravel = ($_POST['malariaTravel'] === 'Yes') ? 1 : 0;
    $tattoos = ($_POST['tattoos'] === 'Yes') ? 1 : 0;
    $user_id = intval($_SESSION['user_id']); // Ensure user_id is an integer

    // Initialize the reasons array and eligibility flag
    $reasons = [];
    $isEligible = true;

    // Eligibility checks
    if ($weight < 45) {
        $reasons[] = "Weight must be at least 45kg";
        $isEligible = false;
    }

    if ($age < 18 || $age > 60) {
        $reasons[] = "Age must be between 18 and 60 years";
        $isEligible = false;
    }

    if (!empty($lastDonation)) {
        $lastDonationDate = new DateTime($lastDonation);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($lastDonationDate);
        $monthsDiff = ($interval->y * 12) + $interval->m;

        if ($monthsDiff < 3) {
            $reasons[] = "Must wait at least 3 months between donations";
            $isEligible = false;
        }
    }

    if ($medicalConditions) {
        $reasons[] = "Cannot have existing medical conditions";
        $isEligible = false;
    }

    if ($medications) {
        $reasons[] = "Cannot be taking medications";
        $isEligible = false;
    }

    if ($recentIllness) {
        $reasons[] = "Cannot donate if recently ill";
        $isEligible = false;
    }

    if ($malariaTravel) {
        $reasons[] = "Cannot donate if recently traveled to malaria-endemic areas";
        $isEligible = false;
    }

    if ($tattoos) {
        $reasons[] = "Cannot donate if you have new tattoos or piercings within 6 months";
        $isEligible = false;
    }

    // Convert the reasons array into a string
    $reasonString = implode(", ", $reasons);

    // SQL query with ON DUPLICATE KEY UPDATE
    $sql = "
        INSERT INTO eligibility 
        (user_id, weight, age, last_blood_donation_date, new_tattoos_or_piercings, medical_conditions, taking_medications, recent_illness, traveled_malaria_area, eligibility_status, reasons) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        weight = VALUES(weight),
        age = VALUES(age),
        last_blood_donation_date = VALUES(last_blood_donation_date),
        new_tattoos_or_piercings = VALUES(new_tattoos_or_piercings),
        medical_conditions = VALUES(medical_conditions),
        taking_medications = VALUES(taking_medications),
        recent_illness = VALUES(recent_illness),
        traveled_malaria_area = VALUES(traveled_malaria_area),
        eligibility_status = VALUES(eligibility_status),
        reasons = VALUES(reasons)
    ";

    $stmt = $conn->prepare($sql);

    // Check if lastDonation is null, and bind accordingly
    if ($lastDonation === null) {
        $stmt->bind_param(
            "iidiiiiiiis",
            $user_id,
            $weight,
            $age,
            $lastDonation,  // This will be null
            $tattoos,
            $medicalConditions,
            $medications,
            $recentIllness,
            $malariaTravel,
            $isEligible,
            $reasonString
        );
    } else {
        $stmt->bind_param(
            "iidiiiiiiis",
            $user_id,
            $weight,
            $age,
            $lastDonation,  // This will be a valid date
            $tattoos,
            $medicalConditions,
            $medications,
            $recentIllness,
            $malariaTravel,
            $isEligible,
            $reasonString
        );
    }

    // Execute the query
    if ($stmt->execute()) {
        if ($isEligible) {
            $_SESSION['eligibility_checked'] = true;
            header('Location: choose_location.php');
            exit();
        } else {
            echo "<script>
                alert('You are not eligible to donate blood. Reasons: " . addslashes($reasonString) . "');
                window.location.href = '../dashboard.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Error: Could not save eligibility data. Please try again later.');
            window.location.href = '../dashboard.php';
        </script>";
        exit();
    }

    $stmt->close();
}
?>

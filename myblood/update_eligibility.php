<?php
session_start();
include('db_connect.php'); // Include database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $weight = floatval($_POST['weight']);
    $age = intval($_POST['age']);
    
    // Validate and format last donation date
    $lastDonation = $_POST['lastDonation'] ?: null; // Set to null if no date provided
    if ($lastDonation) {
        $lastDonationDate = new DateTime($lastDonation);
        $lastDonation = $lastDonationDate->format('Y-m-d'); // Ensure it's in Y-m-d format
    } else {
        $lastDonation = null;
    }
    
    // Get other form data
    $medicalConditions = ($_POST['medicalConditions'] === 'Yes') ? 1 : 0;
    $medications = ($_POST['medications'] === 'Yes') ? 1 : 0;
    $recentIllness = ($_POST['recentIllness'] === 'Yes') ? 1 : 0;
    $malariaTravel = ($_POST['malariaTravel'] === 'Yes') ? 1 : 0;
    $tattoos = ($_POST['tattoos'] === 'Yes') ? 1 : 0;
    $user_id = intval($_SESSION['user_id']); // Ensure `user_id` is an integer

    // Initialize the reasons array and eligibility flag
    $reasons = [];
    $isEligible = true;

    // Perform eligibility checks
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

    // Convert reasons array to a string
    $reasonString = implode(", ", $reasons);

    // Prepare the SQL query for updating
    $sql = "
        UPDATE eligibility
        SET 
            weight = ?, 
            age = ?, 
            last_blood_donation_date = ?, 
            new_tattoos_or_piercings = ?, 
            medical_conditions = ?, 
            taking_medications = ?, 
            recent_illness = ?, 
            traveled_malaria_area = ?, 
            eligibility_status = ?, 
            reasons = ?
        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($sql);

    // Bind the parameters
    if ($lastDonation === null) {
        $stmt->bind_param(
            "diiiiiiiisi",
            $weight,
            $age,
            $lastDonation, // Null for no date
            $tattoos,
            $medicalConditions,
            $medications,
            $recentIllness,
            $malariaTravel,
            $isEligible,
            $reasonString,
            $user_id
        );
    } else {
        $stmt->bind_param(
            "diiiiiiiisi",
            $weight,
            $age,
            $lastDonation, // Valid date
            $tattoos,
            $medicalConditions,
            $medications,
            $recentIllness,
            $malariaTravel,
            $isEligible,
            $reasonString,
            $user_id
        );
    }

    // Execute the query and handle the response
    if ($stmt->execute()) {
        if ($isEligible) {
            $_SESSION['eligibility_checked'] = true;
            echo "<script>
                alert('Eligibility updated successfully. You are eligible to donate.');
                window.location.href = '../dashboard.php';
            </script>";
            exit();
        } else {
            echo "<script>
                alert('Eligibility updated successfully. However, you are not eligible to donate. Reasons: " . addslashes($reasonString) . "');
                window.location.href = '../dashboard.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Error: Could not update eligibility data. Please try again later.');
            window.location.href = '../dashboard.php';
        </script>";
        exit();
    }

    $stmt->close();
}
?>

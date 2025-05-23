<?php
session_start();
include('../db_connect.php'); // Include database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Check the user's eligibility status in the database
$sql = "SELECT eligibility_status FROM eligibility WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['eligibility_status'] == 1) {
        // Redirect to the appointment selection page if eligible
        header('Location: choose_location.php');
        exit();
    }
}

// If not eligible, proceed with eligibility form processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $weight = floatval($_POST['weight']);
    $age = intval($_POST['age']);
    $lastDonation = !empty($_POST['lastDonation']) ? $_POST['lastDonation'] : null;
    $tattoos = ($_POST['tattoos'] === 'Yes') ? 1 : 0;
    $medicalConditions = ($_POST['medicalConditions'] === 'Yes') ? 1 : 0;
    $medications = ($_POST['medications'] === 'Yes') ? 1 : 0;
    $recentIllness = ($_POST['recentIllness'] === 'Yes') ? 1 : 0;
    $malariaTravel = ($_POST['malariaTravel'] === 'Yes') ? 1 : 0;

    $isEligible = 1; // Assume eligible by default
    $reasons = [];

    // Eligibility checks
    if ($weight < 45) {
        $isEligible = 0;
        $reasons[] = "Weight below 45kg.";
    }

    if ($age < 18 || $age > 60) {
        $isEligible = 0;
        $reasons[] = "Age not between 18 and 60.";
    }

    if (!empty($lastDonation)) {
        $lastDonationDate = new DateTime($lastDonation);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($lastDonationDate);
        $monthsDiff = ($interval->y * 12) + $interval->m;

        if ($monthsDiff < 3) {
            $isEligible = 0;
            $reasons[] = "Last donation was less than 3 months ago.";
        }
    }

    if ($medicalConditions) {
        $isEligible = 0;
        $reasons[] = "Has medical conditions.";
    }

    if ($medications) {
        $isEligible = 0;
        $reasons[] = "Taking medications.";
    }

    if ($recentIllness) {
        $isEligible = 0;
        $reasons[] = "Recently ill.";
    }

    if ($malariaTravel) {
        $isEligible = 0;
        $reasons[] = "Traveled to malaria-endemic area.";
    }

    if ($tattoos) {
        $isEligible = 0;
        $reasons[] = "New tattoos/piercings within 6 months.";
    }

    $reasonString = implode(", ", $reasons);

    // Insert or update the database
    $sql = "INSERT INTO eligibility 
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
            reasons = VALUES(reasons)";

    $stmt = $conn->prepare($sql);
	
	// Handle NULL for 'last_blood_donation_date'
if ($lastDonation === NULL) {
    $stmt->bind_param("iidiiiiiiis", $user_id, $weight, $age, $tattoos, $medicalConditions, $medications, $recentIllness, $malariaTravel, $isEligible, $reasonString);
} else {
    $stmt->bind_param(
        "iidiiiiiiis",
        $user_id,
        $weight,
        $age,
        $lastDonation,
        $tattoos,
        $medicalConditions,
        $medications,
        $recentIllness,
        $malariaTravel,
        $isEligible,
        $reasonString
    );
}

    if ($stmt->execute()) {
        if ($isEligible) {
            $_SESSION['eligibility_checked'] = true;
            header('Location: choose_location.php');
        } else {
            echo "<script>
                alert('You are not eligible to donate blood. Reasons: " . addslashes($reasonString) . "');
                window.location.href = 'dashboard.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Error saving data. Please try again.');
            window.location.href = 'dashboard.php';
        </script>";
    }
    $stmt->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Eligibility - MYBlood</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Inherit the provided styling */
        body {
            background-color: #f8f9fa;
        }

        .container-dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Additional styles for eligibility form */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            padding: 0 50px;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background-color: #ccc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 10px;
        }

        .step.active .step-number {
            background-color: #D32F2F;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px;
        }

        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
    </style>
</head>
<body>
    <div class="container-dashboard">
        <div class="progress-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-text">Check Eligibility</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Choose Locations</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Book Appointment</div>
            </div>
        </div>

        <h2 class="text-center mb-4">Check Eligibility</h2>

        <form action="process_eligibility.php" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="weight">Weight (in kg):</label>
                        <input type="number" class="form-control" id="weight" name="weight" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="age">Age:</label>
                        <input type="number" class="form-control" id="age" name="age" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="lastDonation">Last Blood Donation Date:</label>
                <input type="date" class="form-control" id="lastDonation" name="lastDonation">
            </div>

            <div class="form-group">
                <label for="medicalConditions">Do you have any medical conditions?</label>
                <select class="form-control" id="medicalConditions" name="medicalConditions" required>
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="medications">Are you taking any medications?</label>
                <select class="form-control" id="medications" name="medications" required>
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="recentIllness">Have you been sick recently (e.g., fever, flu)?</label>
                <select class="form-control" id="recentIllness" name="recentIllness" required>
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="malariaTravel">Have you traveled to a malaria-endemic area recently?</label>
                <select class="form-control" id="malariaTravel" name="malariaTravel" required>
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tattoos">Do you have any new tattoos or piercings (within the last 6 months)?</label>
                <select class="form-control" id="tattoos" name="tattoos" required>
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">Check Eligibility</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
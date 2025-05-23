<?php
session_start();
include('db_connect.php');  // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Get the user_id from the session
$user_id = intval($_SESSION['user_id']);

// Fetch the user's eligibility data from the database
$sql = "SELECT * FROM eligibility WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Eligibility</title>
    <style>
       body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
    color: #333;
}

.container {
    max-width: 100%; /* Make it responsive */
    width: 90%; /* Slightly smaller for smaller screens */
    margin: 20px auto; /* Reduced margin */
    padding: 20px; /* Compact padding */
    background-color: #ffffff;
    border-radius: 5px; /* Smaller border radius */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
}

h2 {
    text-align: center;
    margin-bottom: 15px;
    color: #000;
    font-size: 20px; /* Smaller font size */
}

.form-group {
    margin-bottom: 10px; /* Reduced margin */
}

label {
    display: block;
    margin-bottom: 5px; /* Smaller margin */
    font-weight: bold;
    font-size: 12px; /* Smaller font size */
    color: #333;
}

input[type="text"],
input[type="number"],
input[type="date"],
select {
    width: 100%;
    padding: 8px; /* Compact padding */
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 12px; /* Smaller font size */
    background-color: #fff;
}

.btn {
    display: inline-block;
    width: 100%;
    padding: 10px; /* Smaller padding */
    font-size: 14px; /* Slightly smaller font */
    color: #fff;
    background-color: #000;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #333;
}

.form-footer {
    text-align: center;
    margin-top: 10px; /* Reduced margin */
}

.form-footer a {
    color: #000;
    text-decoration: none;
    font-size: 12px; /* Smaller font size */
    font-weight: bold;
    transition: color 0.3s ease;
}

.form-footer a:hover {
    color: #555;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px; /* Adjust padding for smaller screens */
    }

    h2 {
        font-size: 18px;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"],
    select {
        font-size: 11px; /* Smaller inputs for smaller screens */
    }

    .btn {
        padding: 8px;
        font-size: 13px;
    }
}

        .btn:hover {
            background-color: #333;
        }
        .form-footer {
            text-align: center;
            margin-top: 20px;
        }
        .form-footer a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .form-footer a:hover {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Eligibility Information</h2>
        <form action="update_eligibility.php" method="POST">
            <!-- Weight -->
            <div class="form-group">
                <label for="weight">Weight (kg):</label>
                <input type="number" id="weight" name="weight" step="0.1" min="0" required>
            </div>
            
            <!-- Age -->
            <div class="form-group">
                <label for="age">Age (years):</label>
                <input type="number" id="age" name="age" min="18" max="60" required>
            </div>
            
            <!-- Last Blood Donation Date -->
            <div class="form-group">
                <label for="lastDonation">Last Blood Donation Date (if any):</label>
                <input type="date" id="lastDonation" name="lastDonation">
            </div>
            
            <!-- Medical Conditions -->
            <div class="form-group">
                <label for="medicalConditions">Do you have any existing medical conditions?</label>
                <select id="medicalConditions" name="medicalConditions" required>
                    <option value="">Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            
            <!-- Medications -->
            <div class="form-group">
                <label for="medications">Are you taking any medications?</label>
                <select id="medications" name="medications" required>
                    <option value="">Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            
            <!-- Recent Illness -->
            <div class="form-group">
                <label for="recentIllness">Have you been recently ill?</label>
                <select id="recentIllness" name="recentIllness" required>
                    <option value="">Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            
            <!-- Malaria Travel -->
            <div class="form-group">
                <label for="malariaTravel">Have you recently traveled to malaria-endemic areas?</label>
                <select id="malariaTravel" name="malariaTravel" required>
                    <option value="">Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            
            <!-- Tattoos or Piercings -->
            <div class="form-group">
                <label for="tattoos">Do you have new tattoos or piercings within the last 6 months?</label>
                <select id="tattoos" name="tattoos" required>
                    <option value="">Select an option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn">Update Eligibility</button>
            </div>
        </form>
        <div class="form-footer">
            <a href="../dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

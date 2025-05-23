<?php
session_start(); // Start session to access admin username
date_default_timezone_set('Asia/Kuala_Lumpur'); // Set timezone to Malaysia

// Get current date and time
$currentDate = date("l, F j, Y"); // Example: Monday, January 20, 2025
$currentTime = date("g:i A");     // Example: 6:00 PM
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Add your CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
</head>
<body>
    <!-- Header Section -->
    <div class="header" style="
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    padding: 10px 20px; 
    background-color: #333; 
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 70px;">

        <!-- Logo -->
        <div class="logo" style="
            display: flex; 
            align-items: center;
            margin-left: 40px; /* Add margin to move logo right */
            padding-left: 10px;">
            <img src="../assets/images/logo.png" alt="MYBlood Logo" style="width: 50px; height: 50px; margin-right: 10px;">
            <h2 style="margin: 0;">MYBlood</h2>
        </div>

        <!-- Admin Info and Logout -->
        <div class="admin-info" style="
            display: flex; 
            align-items: center;">
            
            <!-- Current Date and Time -->
            <div class="date-time" style="
                margin-right: 20px;">
                <span><?php echo $currentDate; ?></span> | 
                <span><?php echo $currentTime; ?></span>
            </div>

            <!-- Admin Username -->
            <div class="username" style="
                margin-right: 10px;">
                <i class="fas fa-user-circle"></i> 
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>

            <!-- Logout Icon -->
            <a href="../logout.php" title="Logout" style="
                color: white; 
                text-decoration: none;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</body>
</html>

<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Replace with your MySQL root password
$dbname = 'myblood';

// Create a mysqli connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// Check mysqli connection
if (!$conn) {
    die("<div class='alert alert-danger'>Database connection failed: " . mysqli_connect_error() . "</div>");
}

// Create a PDO connection (optional)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">PDO Connection failed: ' . $e->getMessage() . '</div>';
    exit();
}

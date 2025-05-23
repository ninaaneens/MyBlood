<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Replace with your MySQL root password
$dbname = 'myblood';

// Create connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$conn) {
    die("<div class='alert alert-danger'>Database connection failed: " . mysqli_connect_error() . "</div>");
}

// Create a PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}
?>
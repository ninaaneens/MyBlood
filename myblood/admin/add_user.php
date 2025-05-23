<?php
include('../db_connect.php'); // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = 'donor'; // Default role is donor
    $full_name = $_POST['full_name'];
    $ic_num = $_POST['ic_num'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $marital_stat = $_POST['marital_stat'];
    $address = $_POST['address'];
    $blood_type = $_POST['blood_type'];

    // Insert query
    $query = "INSERT INTO users (username, password, email, phone, role, full_name, ic_num, date_of_birth, gender, marital_stat, address, blood_type) 
              VALUES ('$username', '$password', '$email', '$phone', '$role', '$full_name', '$ic_num', '$date_of_birth', '$gender', '$marital_stat', '$address', '$blood_type')";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

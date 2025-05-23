<?php
include('../db_connect.php'); // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $full_name = $_POST['full_name'];
    $address = $_POST['address'];
    $blood_type = $_POST['blood_type'];

    // Update query for editable fields only
    $updateQuery = "UPDATE users SET 
        email='$email', 
        phone='$phone', 
        full_name='$full_name', 
        address='$address', 
        blood_type='$blood_type' 
        WHERE user_id=$user_id";

    if (mysqli_query($conn, $updateQuery)) {
        echo "success";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

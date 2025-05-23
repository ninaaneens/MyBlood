<?php
include('../db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loc_id = $_POST['loc_id'];

    foreach ($_POST as $blood_type => $quantity) {
        if ($blood_type !== 'loc_id') {
            $query = "UPDATE blood_inventory SET quantity='$quantity' WHERE loc_id='$loc_id' AND blood_type='$blood_type'";
            mysqli_query($conn, $query);
        }
    }

    echo "success";
}
?>

<?php
include('../db_connect.php'); // Database connection

// Check if the required POST parameter 'slot_id' is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    // Sanitize input
    $slot_id = intval($_POST['slot_id']); // Convert to integer

    // Prepare the SQL query to fetch the time slot data
    $query = "SELECT ats.slot_id, ats.avail_date, ats.avail_time, ats.status, ats.loc_id, l.loc_name 
              FROM available_time_slots ats
              JOIN location l ON ats.loc_id = l.loc_id
              WHERE ats.slot_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $slot_id); // Bind the slot_id to the query

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                "success" => true,
                "data" => $row,
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No data found for the specified slot ID.",
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database query failed.",
        ]);
    }
    $stmt->close();
} else {
    // If 'slot_id' is not provided
    echo json_encode([
        "success" => false,
        "message" => "Missing 'slot_id' parameter.",
    ]);
}

$conn->close();
?>

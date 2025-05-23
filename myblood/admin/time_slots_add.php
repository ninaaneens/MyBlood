<?php
include('../db_connect.php'); // Include database connection

// Fetch locations for dropdown
$locations_query = "SELECT loc_id, loc_name FROM location ORDER BY loc_name";
$locations_result = mysqli_query($conn, $locations_query);

// Set admin_id to 1 (hardcoded)
$admin_id = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Time Slot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"> <!-- Bootstrap CSS -->
</head>
<body>
<div class="modal-header">
    <h5 class="modal-title">Add Time Slot</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addTimeSlotForm">
        <!-- Date Input -->
        <div class="mb-3">
            <label for="avail_date" class="form-label">Date</label>
            <input type="date" id="avail_date" name="avail_date" class="form-control" required>
        </div>

        <!-- Time Input -->
        <div class="mb-3">
            <label for="avail_time" class="form-label">Time</label>
            <input type="time" id="avail_time" name="avail_time" class="form-control" required>
        </div>

        <!-- Status Dropdown -->
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
                <option value="">Select Status</option>
                <option value="available">Available</option>
                <option value="booked">Booked</option>
            </select>
        </div>

        <!-- Location Dropdown -->
        <div class="mb-3">
            <label for="loc_id" class="form-label">Location</label>
            <select id="loc_id" name="loc_id" class="form-select" required>
                <option value="">Select Location</option>
                <?php while ($row = mysqli_fetch_assoc($locations_result)): ?>
                    <option value="<?php echo $row['loc_id']; ?>">
                        <?php echo htmlspecialchars($row['loc_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Hidden Admin ID -->
        <input type="hidden" name="admin_id" value="<?php echo $admin_id; ?>">

        <!-- Submit Button -->
        <button type="submit" class="btn btn-success">Add Time Slot</button>
    </form>
</div>

<script>
// Handle Add Time Slot Form Submission
$(document).on('submit', '#addTimeSlotForm', function (e) {
    e.preventDefault(); // Prevent form submission

    $.ajax({
        url: 'time_slots_save.php', // Endpoint to save the new time slot
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            const res = JSON.parse(response);
            alert(res.message);
            if (res.success) {
                $('#addModal').modal('hide');
                location.reload(); // Reload the page to see the new time slot
            }
        },
        error: function () {
            alert('Failed to add time slot. Please try again.');
        }
    });
});
</script>

<?php mysqli_close($conn); // Close database connection ?>

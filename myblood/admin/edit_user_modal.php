<?php
include('../db_connect.php'); // Include database connection

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $query = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
}
?>

<div class="modal-header">
    <h5 class="modal-title">Edit Donor</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <!-- Edit Form -->
    <form id="editUserForm">
        <!-- Hidden ID Field -->
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">

        <label>Email:</label>
        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

        <label>Phone:</label>
        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required><br>

        <label>Full Name:</label>
        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required><br>

        <label>Address:</label>
        <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea><br>

        <label>Blood Type:</label>
        <select name="blood_type" class="form-select">
            <?php
            foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $type) {
                echo "<option value='$type'" . ($user['blood_type'] == $type ? ' selected' : '') . ">$type</option>";
            }
            ?>
        </select><br>

        <!-- Submit Button -->
        <button type="button" id="updateUserBtn" class="btn btn-primary">Update Donor</button>
    </form>
</div>

<script>
// Handle Update User Form Submission
$('#updateUserBtn').on('click', function () {
    const formData = $('#editUserForm').serialize(); // Serialize form data

    $.ajax({
        url: 'edit_user.php', // Backend script to handle the update
        type: 'POST',
        data: formData,
        success: function (response) {
            alert('Donor updated successfully!');
            $('#editModal').modal('hide'); // Hide the modal
            location.reload(); // Reload the page to see changes
        },
        error: function () {
            alert('Failed to update donor.');
        }
    });
});
</script>

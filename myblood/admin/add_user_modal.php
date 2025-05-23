<?php
include('../db_connect.php'); // Include database connection
?>

<div class="modal-header">
    <h5 class="modal-title">Add New Donor</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <!-- Add Donor Form -->
    <form id="addDonorForm">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" class="form-control" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" class="form-control" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" class="form-control"><br>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" class="form-control"><br>

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" class="form-control"><br>

        <label for="ic_num">IC Number:</label>
        <input type="text" id="ic_num" name="ic_num" class="form-control"><br>

        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"><br>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" class="form-control">
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select><br>

        <label for="marital_stat">Marital Status:</label>
        <select id="marital_stat" name="marital_stat" class="form-control">
            <option value="">Select Status</option>
            <option value="single">Single</option>
            <option value="married">Married</option>
        </select><br>

        <label for="address">Address:</label>
        <textarea id="address" name="address" class="form-control"></textarea><br>

        <label for="blood_type">Blood Type:</label>
        <select id="blood_type" name="blood_type" class="form-control">
            <?php
            foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $type) {
                echo "<option value='$type'>$type</option>";
            }
            ?>
        </select><br>

        <!-- Submit Button -->
        <button type="button" id="saveDonorBtn" class="btn btn-success">Save Donor</button>
    </form>
</div>

<script>
// Handle Add Donor Form Submission
$('#saveDonorBtn').on('click', function () {
    const formData = $('#addDonorForm').serialize(); // Serialize form data

    $.ajax({
        url: 'add_user.php', // Backend script to handle the addition
        type: 'POST',
        data: formData,
        success: function (response) {
            alert('Donor added successfully!');
            $('#addModal').modal('hide'); // Hide the modal
            location.reload(); // Reload the page to see changes
        },
        error: function () {
            alert('Failed to add donor.');
        }
    });
});
</script>

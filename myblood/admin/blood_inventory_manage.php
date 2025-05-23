<?php
include('../db_connect.php');

if (isset($_GET['loc_id'])) {
    $loc_id = $_GET['loc_id'];

    // Fetch current inventory
    $query = "SELECT * FROM blood_inventory WHERE loc_id = '$loc_id'";
    $result = mysqli_query($conn, $query);

    // Fetch location name
    $locationQuery = "SELECT loc_name FROM location WHERE loc_id = '$loc_id'";
    $locationResult = mysqli_query($conn, $locationQuery);
    $locationName = mysqli_fetch_assoc($locationResult)['loc_name'];
}
?>

<div class="modal-header">
    <h5 class="modal-title">Manage Blood Inventory for <?php echo htmlspecialchars($locationName); ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form id="manageInventoryForm">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <label for="<?php echo htmlspecialchars($row['blood_type']); ?>"><?php echo htmlspecialchars($row['blood_type']); ?>:</label>
            <input type="number" id="<?php echo htmlspecialchars($row['blood_type']); ?>" name="<?php echo htmlspecialchars($row['blood_type']); ?>" value="<?php echo htmlspecialchars($row['quantity']); ?>" class="form-control mb-3" min="0" required>
        <?php endwhile; ?>

        <input type="hidden" name="loc_id" value="<?php echo htmlspecialchars($loc_id); ?>">

        <button type="button" id="updateInventoryBtn" class="btn btn-success">Update Inventory</button>
    </form>
</div>

<script>
$(document).on('click', '#updateInventoryBtn', function () {
    const formData = $('#manageInventoryForm').serialize();

    $.ajax({
        url: 'update_blood_inventory.php',
        type: 'POST',
        data: formData,
        success: function (response) {
            alert('Inventory updated successfully!');
            var manageModal = new bootstrap.Modal(document.getElementById('manageModal'));
            manageModal.hide();
            location.reload();
        },
        error: function () {
            alert('Failed to update inventory.');
        }
    });
});
</script>

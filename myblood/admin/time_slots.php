<?php
include('admin_header.php'); // Include the header (with sidebar)
include('../db_connect.php'); // Include database connection

// Fetch available time slots
$query = "SELECT ats.slot_id, ats.avail_date, ats.avail_time, ats.status, l.loc_name 
          FROM available_time_slots ats 
          JOIN location l ON ats.loc_id = l.loc_id 
          ORDER BY ats.avail_date, ats.avail_time";
$result = mysqli_query($conn, $query);
// Base query
$query = "SELECT ats.slot_id, ats.avail_date, ats.avail_time, ats.status, l.loc_name 
          FROM available_time_slots ats 
          JOIN location l ON ats.loc_id = l.loc_id";

// Initialize where conditions array
$where = [];
$params = [];

// Add filter conditions
if (isset($_GET['slot_id']) && !empty($_GET['slot_id'])) {
    $where[] = "ats.slot_id = ?";
    $params[] = $_GET['slot_id'];
}

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $where[] = "ats.avail_date = ?";
    $params[] = $_GET['date'];
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where[] = "ats.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where[] = "l.loc_id = ?";
    $params[] = $_GET['location'];
}

// Add WHERE clause if conditions exist
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY ats.avail_date, ats.avail_time";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Time Slots</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Add your CSS file -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"> <!-- Bootstrap CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS -->
</head>
<style>
        :root {
            --sidebar-width: 250px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease-in-out;
            width: calc(100% - var(--sidebar-width));
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }
    </style>
<body>
<?php include('sidebar.php'); ?>
<div class="main-content">
    <h1>Manage Time Slots</h1>

    <!-- Add Time Slot Button -->
    <button class="btn btn-success mb-3" id="addTimeSlotBtn" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Time Slot</button>
    <!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <!-- Search by Slot ID -->
            <div class="col-md-3">
                <label for="slot_id" class="form-label">Search by Slot ID</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="number" class="form-control" id="slot_id" name="slot_id" 
                           placeholder="Enter Slot ID" value="<?php echo isset($_GET['slot_id']) ? htmlspecialchars($_GET['slot_id']) : ''; ?>">
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" 
                       value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="available" <?php echo (isset($_GET['status']) && $_GET['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                    <option value="booked" <?php echo (isset($_GET['status']) && $_GET['status'] == 'booked') ? 'selected' : ''; ?>>Booked</option>
                </select>
            </div>

            <!-- Location Filter -->
            <div class="col-md-2">
                <label for="location" class="form-label">Location</label>
                <select class="form-select" id="location" name="location">
                    <option value="">All Locations</option>
                    <?php
                    $locQuery = "SELECT loc_id, loc_name FROM location ORDER BY loc_name";
                    $locResult = mysqli_query($conn, $locQuery);
                    while ($loc = mysqli_fetch_assoc($locResult)) {
                        $selected = (isset($_GET['location']) && $_GET['location'] == $loc['loc_id']) ? 'selected' : '';
                        echo "<option value='" . $loc['loc_id'] . "' " . $selected . ">" . htmlspecialchars($loc['loc_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Time Slots Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>Slot ID</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Location</th>
            <th>Action</th>
        </tr>
    </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                    <td><?php echo htmlspecialchars($row['slot_id']); ?></td> <!-- Display slot_id -->
                        <td><?php echo htmlspecialchars($row['avail_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['avail_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['loc_name']); ?></td>
                        <td class="text-center">
                            <!-- Edit Button -->
                            <button class="btn btn-primary edit-btn" data-id="<?php echo $row['slot_id']; ?>" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>

                            <!-- Delete Button -->
                            <button class="btn btn-danger delete-btn" data-id="<?php echo $row['slot_id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No time slots found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Add Modal -->
    <div id="addModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Content will be loaded dynamically via AJAX -->
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm">
            <!-- Add hidden input for slot_id -->
            <input type="hidden" id="slot_id" name="slot_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Time Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="loc_id" class="form-label">Location</label>
                        <select id="loc_id" name="loc_id" class="form-select" required>
                            <?php
                            $locQuery = "SELECT loc_id, loc_name FROM location ORDER BY loc_name";
                            $locResult = mysqli_query($conn, $locQuery);
                            while ($loc = mysqli_fetch_assoc($locResult)) {
                                echo "<option value='" . $loc['loc_id'] . "'>" . htmlspecialchars($loc['loc_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
    // Sidebar Toggle
    document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            
            document.getElementById('toggleSidebar').addEventListener('click', function() {
                mainContent.classList.toggle('expanded');
            });
        });
// Handle form submission for updating time slots
$('#editForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: 'time_slots_edit.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            const res = JSON.parse(response);
            alert(res.message);
            if (res.success) {
                $('#editModal').modal('hide');
                location.reload(); // Reload page to reflect changes
            }
        },
        error: function () {
            alert('Failed to update time slot.');
        }
    });
});
</script>

<script>
// Load Add Modal Content
$('#addTimeSlotBtn').on('click', function () {
    $.ajax({
        url: 'time_slots_add.php',
        type: 'GET',
        success: function (data) {
            $('#addModal .modal-content').html(data);
        },
        error: function () {
            alert('Failed to load add time slot modal.');
        }
    });
});

$(document).on("click", ".edit-btn", function () {
    var slot_id = $(this).data("id");
    
    $.ajax({
        url: "fetch_time_slot_data.php",
        type: "POST",
        data: { slot_id: slot_id },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                // Set the hidden slot_id
                $("#editModal #slot_id").val(slot_id);
                $("#editModal #date").val(response.data.avail_date);
                $("#editModal #time").val(response.data.avail_time);
                $("#editModal #status").val(response.data.status);
                $("#editModal #loc_id").val(response.data.loc_id);
                $("#editModal").modal("show");
            } else {
                alert("Failed to fetch data: " + response.message);
            }
        },
        error: function () {
            alert("An error occurred while fetching time slot data.");
        }
    });
});


// Handle Delete Time Slot
$(document).on('click', '.delete-btn', function () {
    const slotId = $(this).data('id');
    
    if (confirm('Are you sure you want to delete this time slot?')) {
        $.ajax({
            url: 'time_slots_delete.php',
            type: 'POST',
            data: { slot_id: slotId },
            success: function (response) {
                alert('Time slot deleted successfully!');
                location.reload(); // Reload the page to see changes
            },
            error: function () {
                alert('Failed to delete time slot.');
            }
        });
    }
});
</script>

<?php mysqli_close($conn); // Close database connection ?>
</body>
</html>

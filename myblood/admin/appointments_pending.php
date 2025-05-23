<?php
include('../db_connect.php');
include('admin_header.php');
include('sidebar.php');

// Fetch distinct locations for the filter
$locationQuery = "SELECT DISTINCT loc_name FROM location ORDER BY loc_name";
$locationResult = mysqli_query($conn, $locationQuery);

// Initialize search and filter variables
$searchQuery = '';
$filterLocation = '';

// Base query
$query = "SELECT a.app_id, a.status, a.created_at, u.full_name AS donor_name, 
                 u.email AS donor_email, u.ic_num, ats.avail_date, ats.avail_time, 
                 l.loc_name AS location_name
          FROM appointments a
          JOIN users u ON a.user_id = u.user_id
          JOIN available_time_slots ats ON a.slot_id = ats.slot_id
          JOIN location l ON ats.loc_id = l.loc_id
          WHERE a.status = 'pending'";

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
    $query .= " AND (u.full_name LIKE '$searchTerm%' 
                     OR u.ic_num LIKE '$searchTerm%'
                     OR a.app_id = '$searchTerm')";
}

// Handle location filter
if (isset($_GET['filter_location']) && !empty($_GET['filter_location'])) {
    $filterLocation = mysqli_real_escape_string($conn, $_GET['filter_location']);
    $query .= " AND l.loc_name = '$filterLocation'";
}

$query .= " ORDER BY a.created_at DESC";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Your custom CSS -->
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
<div class="main-content">
    <h1>Pending Appointments</h1>

    <!-- Search Bar and Filter Toggle -->
    <form method="GET" class="d-flex mb-3">
    <input type="text" name="search" class="form-control me-2" 
       placeholder="Search by App ID, Name, or IC" 
       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <select name="filter_location" class="form-select me-2">
            <option value="">All Locations</option>
            <?php while ($locRow = mysqli_fetch_assoc($locationResult)): ?>
                <option value="<?php echo htmlspecialchars($locRow['loc_name']); ?>" 
                    <?php echo ($filterLocation == $locRow['loc_name']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($locRow['loc_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>


    <!-- Appointments Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>App ID</th>
                <th>Donor Name</th>
                <th>IC</th>
                <th>Email</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['app_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['donor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['ic_num']); ?></td>
                        <td><?php echo htmlspecialchars($row['donor_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['avail_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['avail_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="text-center">
                            <!-- Edit Icon to Open Modal -->
                            <button class="btn btn-primary btn-sm edit-btn" 
                                    data-id="<?php echo $row['app_id']; ?>"          
                                    data-status="<?php echo $row['status']; ?>"
                                    data-ic="<?php echo htmlspecialchars($row['ic_num']); ?>" 
                                    data-donor-name="<?php echo $row['donor_name']; ?>"
                                    data-email="<?php echo $row['donor_email']; ?>"
                                    data-date="<?php echo $row['avail_date']; ?>"
                                    data-time="<?php echo $row['avail_time']; ?>"
                                    data-location="<?php echo $row['location_name']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#editStatusModal">
                                    <i class="fas fa-edit"></i> Edit
                                   
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No pending appointments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>
<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editStatusModalLabel">Edit Appointment Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Receipt-like Format -->
        <div class="mb-3">
        <strong>Appointment ID:</strong> <span id="appID"></span>
        </div>
        <div class="mb-3">
          <strong>Donor Name:</strong> <span id="donorName"></span>
        </div>
        <div class="mb-3">
          <strong>IC Number:</strong> <span id="icNum"></span>
        </div>
        <div class="mb-3">
          <strong>Email:</strong> <span id="donorEmail"></span>
        </div>
        <div class="mb-3">
          <strong>Date:</strong> <span id="appointmentDate"></span>
        </div>
        <div class="mb-3">
          <strong>Time:</strong> <span id="appointmentTime"></span>
        </div>
        <div class="mb-3">
          <strong>Location:</strong> <span id="appointmentLocation"></span>
        </div>

        <!-- Editable Status Field -->
        <form id="editStatusForm">
          <!-- Hidden Field for Appointment ID -->
          <input type="hidden" id="app_id" name="app_id">

          <!-- Dropdown for Status -->
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
            <option value="cancelled">Cancelled</option> 
            <option value="pending">Pending</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary w-100">Update Status</button>
        </form>
      </div>
    </div>
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
// Populate Modal with Appointment Data
$(document).on('click', '.edit-btn', function () {
    const appId = $(this).data('id');
    const status = $(this).data('status');
    const donorName = $(this).data('donor-name');
    const icNum = $(this).data('ic'); 
    const donorEmail = $(this).data('email');
    const appointmentDate = $(this).data('date');
    const appointmentTime = $(this).data('time');
    const appointmentLocation = $(this).data('location');

    // Populate modal fields
    $('#appID').text(appId); // Set visible appointment ID in the modal
    $('#app_id').val(appId); // Set hidden input for form submission
    $('#icNum').text(icNum);
    $('#donorName').text(donorName);
    $('#donorEmail').text(donorEmail);
    $('#appointmentDate').text(appointmentDate);
    $('#appointmentTime').text(appointmentTime);
    $('#appointmentLocation').text(appointmentLocation);
    $('#status').val(status);

    // Debugging log
    console.log("Modal Data: ", {
        appId,
        status,
        donorName,
        icNum,
        donorEmail,
        appointmentDate,
        appointmentTime,
        appointmentLocation
    });
});

// Handle Form Submit for Updating Status
$('#editStatusForm').on('submit', function (e) {
    e.preventDefault();

    const appId = $('#app_id').val();
    const status = $('#status').val();

    $.ajax({
        url: 'appointments_status_update.php', // Backend script to handle status update
        type: 'POST',
        data: { app_id: appId, status: status },
        dataType: 'json', // Ensure the response is parsed as JSON
        success: function (response) {
            if (response.success) {
                alert(response.message); // Show success message
                $('#editStatusModal').modal('hide'); // Hide the modal after success
                location.reload(); // Reload the page to see changes
            } else {
                alert(response.message); // Show error message from server
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error); // Debugging log
            alert('Failed to update appointment status.');
        }
    });
});



</script>

<?php mysqli_close($conn); // Close database connection ?>
</body>
</html>

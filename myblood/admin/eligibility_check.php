<?php
include('../db_connect.php'); // Include database connection
include('admin_header.php'); // Include admin header
include('sidebar.php'); // Include sidebar

$baseQuery = "SELECT e.*, u.full_name, u.ic_num, u.blood_type 
              FROM eligibility e 
              JOIN users u ON e.user_id = u.user_id";

$where = [];
$params = [];

// Handle IC number search
if (isset($_GET['ic_search']) && !empty($_GET['ic_search'])) {
  $where[] = "u.ic_num LIKE CONCAT(?, '%')";  // Changed from LIKE '%?%' to LIKE 'prefix%'
  $params[] = $_GET['ic_search'];
}


// Add WHERE clause if conditions exist
if (!empty($where)) {
    $baseQuery .= " WHERE " . implode(" AND ", $where);
}

$baseQuery .= " ORDER BY e.created_at DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $baseQuery);
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
    <title>Eligibility Checks</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Your custom CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"> <!-- Bootstrap CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS -->
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

    .search-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .table thead th {
        background-color: #343a40;
        color: white;
    }

    .status-eligible {
        color: #198754;
        font-weight: bold;
    }

    .status-ineligible {
        color: #dc3545;
        font-weight: bold;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }
    }
</style>

</head>
<body>
<body>
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Donor Eligibility Records</h1>

            <!-- Search Section -->
            <div class="search-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="ic_search" 
                                   placeholder="Search by IC Number" 
                                   value="<?php echo isset($_GET['ic_search']) ? htmlspecialchars($_GET['ic_search']) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>

            <!-- Eligibility Records Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Donor Name</th>
                            <th>IC Number</th>
                            <th>Blood Type</th>
                            <th>Weight (kg)</th>
                            <th>Age</th>
                            <th>Last Donation</th>
                            <th>Medical Conditions</th>
                            <th>Medications</th>
                            <th>Recent Illness</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                    $isEligible = ($row['weight'] >= 45 && !$row['medical_conditions'] && 
                                                  !$row['taking_medications'] && !$row['recent_illness']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ic_num']); ?></td>
                                    <td><?php echo htmlspecialchars($row['blood_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($row['age']); ?></td>
                                    <td><?php echo $row['last_blood_donation_date'] ? date('d M Y', strtotime($row['last_blood_donation_date'])) : 'Never'; ?></td>
                                    <td><?php echo $row['medical_conditions'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $row['taking_medications'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $row['recent_illness'] ? 'Yes' : 'No'; ?></td>
                                    <td class="<?php echo $isEligible ? 'status-eligible' : 'status-ineligible'; ?>">
                                        <?php echo $isEligible ? 'Eligible' : 'Not Eligible'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No eligibility records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

// Populate Modal with Eligibility Data
$(document).on('click', '.edit-eligibility-btn', function () {
    const icNum = $(this).data('id');
    const donorName = $(this).data('donor-name');
    const donorEmail = $(this).data('email');
    const bloodType = $(this).data('blood-type');
    const medicalCondition = $(this).data('medical-condition');
    const medication = $(this).data('medication');
    const donationHistory = $(this).data('donation-history');
    const eligibilityStatus = $(this).data('eligibility-status');

    // Populate modal fields
    $('#icNum').text(icNum); // Set visible IC Number in the modal
    $('#donorName').text(donorName);
    $('#donorEmail').text(donorEmail);
    $('#bloodType').text(bloodType);
    $('#medicalCondition').text(medicalCondition);
    $('#medication').text(medication);
    $('#donationHistory').text(donationHistory);
    $('#eligibility_status').val(eligibilityStatus);

    // Set hidden input for form submission
    $('#ic_num').val(icNum);
});

// Handle Form Submit for Updating Eligibility Status
$('#editEligibilityForm').on('submit', function (e) {
    e.preventDefault();

    const icNum = $('#ic_num').val();
    const eligibilityStatus = $('#eligibility_status').val();

    $.ajax({
        url: 'eligibility_update.php', // Backend script to handle eligibility status update
        type: 'POST',
        data: { ic_num: icNum, eligibility_status: eligibilityStatus },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                alert(response.message); // Show success message
                $('#editEligibilityModal').modal('hide'); // Hide the modal after success
                location.reload(); // Reload the page to see changes
            } else {
                alert(response.message); // Show error message from server
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error); // Debugging log
            alert('Failed to update eligibility status.');
        }
    });
});
</script>

<?php mysqli_close($conn); // Close database connection ?>
</body>
</html>

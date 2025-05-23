<?php
include('../db_connect.php'); // Include database connection
include('admin_header.php'); // Include admin header
include('sidebar.php'); // Include sidebar

// Fetch distinct locations for the filter
$locationQuery = "SELECT DISTINCT loc_name FROM location";
$locationResult = mysqli_query($conn, $locationQuery);

// Initialize search and filter variables
$searchQuery = '';
$filterLocation = '';
if (isset($_GET['search'])) {
    $searchQuery = mysqli_real_escape_string($conn, $_GET['search']);
}
if (isset($_GET['filter_location'])) {
    $filterLocation = mysqli_real_escape_string($conn, $_GET['filter_location']);
}

// Fetch filtered canceled appointments
$query = "SELECT a.app_id, a.status, a.created_at, u.full_name AS donor_name, u.email AS donor_email, 
                 u.ic_num, ats.avail_date, ats.avail_time, l.loc_name AS location_name
          FROM appointments a
          JOIN users u ON a.user_id = u.user_id
          JOIN available_time_slots ats ON a.slot_id = ats.slot_id
          JOIN location l ON ats.loc_id = l.loc_id
          WHERE a.status = 'cancelled'
          AND (u.full_name LIKE '%$searchQuery%' 
               OR u.ic_num LIKE '%$searchQuery%' 
               OR a.app_id LIKE '%$searchQuery%')";

if ($filterLocation != '') {
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
    <title>Cancelled Appointments</title>
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
    <h1>Cancelled Appointments</h1>

    <!-- Search Bar and Filter -->
    <form method="GET" class="d-flex mb-3">
        <input type="text" name="search" class="form-control me-2" placeholder="Search by App ID, Name, or IC" value="<?php echo htmlspecialchars($searchQuery); ?>">
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
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No cancelled appointments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
    // Sidebar Toggle
    document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            
            document.getElementById('toggleSidebar').addEventListener('click', function() {
                mainContent.classList.toggle('expanded');
            });
        });
</script>

<?php mysqli_close($conn); // Close database connection ?>
</body>
</html>

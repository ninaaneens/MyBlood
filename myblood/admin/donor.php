<?php
include('admin_header.php'); // Include the header (with sidebar)
include('../db_connect.php'); // Include database connection

// Handle search and filter functionality
$searchQuery = '';
$bloodTypeFilter = '';

if (isset($_GET['search']) || isset($_GET['blood_type'])) {
    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
    $bloodTypeFilter = isset($_GET['blood_type']) ? $_GET['blood_type'] : '';

    $query = "SELECT * FROM users WHERE role = 'donor'";

    if (!empty($searchQuery)) {
        // Changed from LIKE '%search%' to LIKE 'search%'
        $query .= " AND full_name LIKE '$searchQuery%'";
    }

    if (!empty($bloodTypeFilter)) {
        $query .= " AND blood_type = '$bloodTypeFilter'";
    }
} else {
    $query = "SELECT * FROM users WHERE role = 'donor'";
}


$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Management</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Add your CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
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

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            width: 100%;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 400px;
            width: 100%;
            max-width: 1200px;
        }

        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            cursor: pointer;
            flex: 1;
            min-width: 250px;
            max-width: 300px;
        }
    </style>
<body>
<?php include('sidebar.php'); ?>
<div class="main-content" >
    <h1>Donor Management</h1>

    <!-- Search and Filter Bar -->
    <form method="GET" action="" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search for donor by name or ID" value="<?php echo htmlspecialchars($searchQuery); ?>" style="
            width: 300px; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px;">

        <select name="blood_type" class="form-select" style="
            width: 200px;">
            <option value="">Filter by Blood Type</option>
            <?php
            foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $type) {
                echo "<option value='$type'" . ($bloodTypeFilter == $type ? ' selected' : '') . ">$type</option>";
            }
            ?>
        </select>

        <button type="submit" style="
            padding: 10px 20px; 
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            border-radius: 5px;">Search</button>
    </form>

    <!-- Donor Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>IC Number</th>
                <th>Blood Type</th>
                <th>Last Donation</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="donorTableBody">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $sn = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    // Fetch last donation date from the `eligibility` table
                    $donationQuery = "SELECT last_blood_donation_date FROM eligibility WHERE user_id = " . $row['user_id'];
                    $donationResult = mysqli_query($conn, $donationQuery);
                    $lastDonationDate = mysqli_num_rows($donationResult) > 0 ? mysqli_fetch_assoc($donationResult)['last_blood_donation_date'] : 'Never donated';
                    ?>
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['ic_num']); ?></td>
                        <td><?php echo htmlspecialchars($row['blood_type']); ?></td>
                        <td><?php echo $lastDonationDate; ?></td>
                        <td class="text-center">
                            <!-- Edit Button -->
                            <button class="btn btn-primary edit-btn" data-id="<?php echo $row['user_id']; ?>" data-bs-toggle="modal" data-bs-target="#editModal"> <i class="fas fa-edit"></i> Edit</button>

                            <!-- Delete Button -->
                            <a href="./delete_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this donor?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No donors found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Add New Donor Button -->
    <button class="btn btn-success" id="addDonorBtn" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Donor</button>

    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Content will be loaded dynamically via AJAX -->
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Content will be loaded dynamically via AJAX -->
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
// Load Edit Modal Content
$(document).on('click', '.edit-btn', function () {
    const userId = $(this).data('id');
    
    $.ajax({
        url: 'edit_user_modal.php',
        type: 'GET',
        data: { id: userId },
        success: function (data) {
            $('#editModal .modal-content').html(data);
        }
    });
});

// Load Add Modal Content
$('#addDonorBtn').on('click', function () {
    $.ajax({
        url: 'add_user_modal.php',
        type: 'GET',
        success: function (data) {
            $('#addModal .modal-content').html(data);
        }
    });
});
</script>

<?php mysqli_close($conn); // Close database connection ?>
</body>
</html>

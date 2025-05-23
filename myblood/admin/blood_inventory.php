<?php
include('admin_header.php'); // Include the header (with sidebar)
include('../db_connect.php'); // Include database connection

// Handle search and filter functionality
$searchQuery = '';
$stateFilter = '';
$whereClauses = [];

if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $whereClauses[] = "l.loc_name LIKE '%" . mysqli_real_escape_string($conn, $searchQuery) . "%'";
}

if (isset($_GET['state']) && $_GET['state'] !== '') {
    $stateFilter = $_GET['state'];
    $whereClauses[] = "l.loc_state = '" . mysqli_real_escape_string($conn, $stateFilter) . "'";
}

$whereSQL = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$query = "SELECT l.loc_id, l.loc_name, l.loc_state, SUM(bi.quantity) AS total_stock 
          FROM blood_inventory bi 
          JOIN location l ON bi.loc_id = l.loc_id 
          $whereSQL 
          GROUP BY l.loc_id, l.loc_name, l.loc_state 
          ORDER BY l.loc_name";
$result = mysqli_query($conn, $query);

// Fetch data for pie charts
$chartQuery = "SELECT bi.blood_type, SUM(bi.quantity) AS total_quantity, l.loc_id 
               FROM blood_inventory bi 
               JOIN location l ON bi.loc_id = l.loc_id 
               GROUP BY bi.blood_type, l.loc_id";
$chartResult = mysqli_query($conn, $chartQuery);

// Prepare data for pie charts
$chartData = [];
while ($row = mysqli_fetch_assoc($chartResult)) {
    $chartData[$row['loc_id']][] = [
        'blood_type' => $row['blood_type'],
        'quantity' => $row['total_quantity']
    ];
}

// Fetch distinct states for the dropdown
$stateQuery = "SELECT DISTINCT loc_state FROM location ORDER BY loc_state";
$stateResult = mysqli_query($conn, $stateQuery);
$states = [];
while ($row = mysqli_fetch_assoc($stateResult)) {
    $states[] = $row['loc_state'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <h1>Blood Inventory Management</h1>

    <!-- Search Bar and State Filter -->
    <form method="GET" action="" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
        <input type="text" name="search" placeholder="Search for location by name" value="<?php echo htmlspecialchars($searchQuery); ?>" style="width: 300px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <select name="state" class="form-select" style="width: 200px;">
            <option value="">Filter by state</option>
            <?php foreach ($states as $state): ?>
                <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $stateFilter === $state ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($state); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-success">Filter</button>
    </form>

    <!-- Blood Inventory Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Location</th>
                <th>State</th>
                <th>Blood Stock</th>
                <th>Total Blood Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php $locId = $row['loc_id']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['loc_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['loc_state']); ?></td>
                        <td style="width: 250px;">
                            <canvas id="chart_<?php echo $locId; ?>" width="200" height="200"></canvas>
                            <script>
                                const ctx_<?php echo $locId; ?> = document.getElementById('chart_<?php echo $locId; ?>').getContext('2d');
                                new Chart(ctx_<?php echo $locId; ?>, {
                                    type: 'pie',
                                    data: {
                                        labels: <?php echo json_encode(array_column($chartData[$locId], 'blood_type') ?? []); ?>,
                                        datasets: [{
                                            data: <?php echo json_encode(array_column($chartData[$locId], 'quantity') ?? []); ?>,
                                            backgroundColor: ['#e74c3c', '#3498db', '#f1c40f', '#2ecc71', '#9b59b6', '#34495e', '#e67e22', '#95a5a6'],
                                            hoverOffset: 4
                                        }]
                                    },
                                    options: {
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'bottom'
                                            }
                                        }
                                    }
                                });
                            </script>
                        </td>
                        <td style="width: 100px;"><?php echo htmlspecialchars($row['total_stock']); ?></td>
                        <td class="text-center">
                            <button class="btn btn-primary manage-btn" data-id="<?php echo $locId; ?>"> <i class="fas fa-edit"></i> Blood Inventory</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No blood inventory records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Manage Modal -->
    <div id="manageModal" class="modal fade" tabindex="-1">
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
$(document).on('click', '.manage-btn', function () {
    const locId = $(this).data('id');

    $.ajax({
        url: 'blood_inventory_manage.php',
        type: 'GET',
        data: { loc_id: locId },
        success: function (data) {
            $('#manageModal .modal-content').html(data);
            var manageModal = new bootstrap.Modal(document.getElementById('manageModal'));
            manageModal.show();
        },
        error: function () {
            alert('Failed to load inventory management modal.');
        }
    });
});
</script>

<?php mysqli_close($conn); ?>
</body>
</html>

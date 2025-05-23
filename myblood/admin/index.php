<?php
// Include necessary files
include('../db_connect.php');
include('admin_header.php');
include('sidebar.php');

// Fetch dashboard statistics
$totalDonorsQuery = "SELECT COUNT(*) AS total_donors FROM users WHERE role = 'donor'";
$totalDonorsResult = mysqli_query($conn, $totalDonorsQuery);
$totalDonors = mysqli_fetch_assoc($totalDonorsResult)['total_donors'];

$totalBloodQuery = "SELECT SUM(quantity) AS total_units FROM blood_inventory";
$totalBloodResult = mysqli_query($conn, $totalBloodQuery);
$totalBloodUnits = mysqli_fetch_assoc($totalBloodResult)['total_units'];

$totalAppointmentsQuery = "SELECT COUNT(*) AS total_appointments FROM appointments";
$totalAppointmentsResult = mysqli_query($conn, $totalAppointmentsQuery);
$totalAppointments = mysqli_fetch_assoc($totalAppointmentsResult)['total_appointments'];

$totalCentersQuery = "SELECT COUNT(*) AS total_centers FROM location";
$totalCentersResult = mysqli_query($conn, $totalCentersQuery);
$totalCenters = mysqli_fetch_assoc($totalCentersResult)['total_centers'];

// Blood type distribution query
$bloodTypeQuery = "SELECT blood_type, SUM(quantity) as total FROM blood_inventory GROUP BY blood_type";
$bloodTypeResult = mysqli_query($conn, $bloodTypeQuery);
$bloodTypes = [];
$bloodQuantities = [];
while($row = mysqli_fetch_assoc($bloodTypeResult)) {
    $bloodTypes[] = $row['blood_type'];
    $bloodQuantities[] = $row['total'];
}

// Appointment status distribution
$appointmentStatusQuery = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
$appointmentResult = mysqli_query($conn, $appointmentStatusQuery);
$appointmentStatus = [];
$appointmentCounts = [];
while($row = mysqli_fetch_assoc($appointmentResult)) {
    $appointmentStatus[] = $row['status'];
    $appointmentCounts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        .dashboard-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .dashboard-item {
            margin: 10px;
            text-align: center;
        }

        .blood-type-distribution, .appointment-status-distribution {
            width: 100%;
            max-width: 600px;
        }

        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            cursor: pointer;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 400px;
        }
        
        
        
    </style>
</head>
<body>

    <div class="main-content">
        <div class="container-fluid">
            <h1 class="text-center mb-4">Admin Dashboard</h1>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="dashboard-card card bg-danger text-white">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-users card-icon"></i>
                            <h2 class="h5 card-title">Total Donors</h2>
                            <p class="display-6 mb-0"><?php echo $totalDonors; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="dashboard-card card bg-info text-white">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-tint card-icon"></i>
                            <h2 class="h5 card-title">Blood Units</h2>
                            <p class="display-6 mb-0"><?php echo $totalBloodUnits; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="dashboard-card card bg-warning text-white">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-calendar-alt card-icon"></i>
                            <h2 class="h5 card-title">Appointments</h2>
                            <p class="display-6 mb-0"><?php echo $totalAppointments; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="dashboard-card card bg-success text-white">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-hospital card-icon"></i>
                            <h2 class="h5 card-title">Donation Centers</h2>
                            <p class="display-6 mb-0"><?php echo $totalCenters; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="bloodTypeChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="appointmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            
            document.getElementById('toggleSidebar').addEventListener('click', function() {
                mainContent.classList.toggle('expanded');
            });
        });


        // Blood Type Distribution Chart
        new Chart(document.getElementById('bloodTypeChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($bloodTypes); ?>,
                datasets: [{
                    label: 'Blood Units Available',
                    data: <?php echo json_encode($bloodQuantities); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Blood Type Distribution',
                        font: { size: 16, weight: 'bold' },
                        padding: 20
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Appointment Status Chart
        new Chart(document.getElementById('appointmentChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($appointmentStatus); ?>,
                datasets: [{
                    data: <?php echo json_encode($appointmentCounts); ?>,
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Appointment Status Distribution',
                        font: { size: 16, weight: 'bold' },
                        padding: 20
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
</body>
</html>

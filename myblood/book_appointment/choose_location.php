<?php
session_start();
include 'header_appointment.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['eligibility_checked'])) {
    header('Location: eligibility_check.php');
    exit();
}

include('../db_connect.php'); // Include your database connection

// Retrieve user details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "User123";

// Get the search query from the input (if present)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query to filter locations based on the search query
$sql = "SELECT * FROM location WHERE loc_name LIKE ? OR loc_state LIKE ?";
$stmt = $conn->prepare($sql);
$search_term = '%' . $search_query . '%';
$stmt->bind_param('ss', $search_term, $search_term);  // Bind the parameters
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
} else {
    echo "No locations found";
    exit();
}

foreach ($locations as $location) {
    $inventory_sql = "SELECT blood_type, quantity FROM blood_inventory WHERE loc_id = ?";
    $inventory_stmt = $conn->prepare($inventory_sql);
    $inventory_stmt->bind_param('i', $location['loc_id']);
    $inventory_stmt->execute();
    $inventory_result = $inventory_stmt->get_result();

    $blood_data = [];
    while ($row = $inventory_result->fetch_assoc()) {
        $blood_data[$row['blood_type']] = (int)$row['quantity'];
    }

    // Pass blood data to JavaScript
    echo "<script>
        var bloodData_" . htmlspecialchars($location['loc_id']) . " = " . json_encode($blood_data) . ";
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Location - MYBlood</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Leaflet CSS for map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container-dashboard {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
		.navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #D32F2F;
            padding: 10px 20px;
            color: white;
        }

        .navbar img {
            height: 30px;
        }

        .navbar span {
            font-size: 16px;
        }
		
		.navbar .user-info {
        display: flex;
        align-items: center;
		}
		
		.navbar .user-info .username {
        display: flex;
        align-items: center;
        gap: 10px; /* Space between username and avatar */
		}

		.navbar .user-avatar {
			width: 30px; /* Adjust size as needed */
			height: 30px;
			border-radius: 50%; /* Makes the image circular */
			margin-right: 10px; /* Adds space between the image and username */
		}

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            padding: 0 50px;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background-color: #ccc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 10px;
        }

        .step.active .step-number {
            background-color: #D32F2F;
        }

        #map {
            height: 1760px;
            width: 100%;
            border-radius: 10px;
        }

        .hospital-card {
    display: flex;
    align-items: flex-start; /* Changed from center to allow proper chart height */
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    background-color: #fff;
    width: 500px;
    min-height: 400px; /* Added minimum height */
}

.hospital-info {
    flex: 1;
    padding-right: 20px;
}

.hospital-chart {
    flex: 1;
    width: 200px;
    height: 300px; /* Increased height */
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
	margin-bottom: 10px;
}

.blood-level-chart {
    width: 100%;
    height: 100%;
    position: relative;
}

        .hospital-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .search-box {
			margin-bottom: 20px;
			display: flex; /* Align items horizontally */
			align-items: center; /* Vertically center items */
			gap: 10px; /* Space between input and button */
			width: 500px; /* Ensure the container spans the full width */
		}

		.search-input {
			flex: 1; /* Allow the input to take all available space */
			padding: 8px;
			border: 1px solid #ddd;
			border-radius: 5px;
			font-size: 16px;
			width:410px;
		}

		.filter-button {
			padding: 8px 16px; /* Add padding to the button */
			background-color: #f8f9fa;
			border: 1px solid #ddd;
			border-radius: 5px;
			white-space: nowrap; /* Prevent text wrapping */
			text-align: center; /* Center text in the button */
			font-size: 16px;
		}

        .book-appointment-btn {
			background-color: #000000; /* Black */
			color: white;
			border: none;
			padding: 8px 16px;
			border-radius: 5px;
			width: 190px;
		}

		.book-appointment-btn:hover {
			background-color: #333333; /* Darker shade of black for hover */
		}

        .rating {
            color: #ffc107;
        }

        
		.step.completed .step-number {
            background-color: #28a745;
        }
    </style>
</head>
<div class="container-dashboard">
    <div class="progress-steps">
        <div class="step completed">
            <div class="step-number">✓</div>
            <div class="step-text">Check Eligibility</div>
        </div>
        <div class="step active">
            <div class="step-number">2</div>
            <div class="step-text">Choose Locations</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-text">Book Appointment</div>
        </div>
    </div>

    <h2 class="mb-4">Blood Donation Locations</h2>

    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" class="search-input" placeholder="Search locations based on city or zip code" value="<?php echo isset($search_query) ? htmlspecialchars($search_query) : ''; ?>">
            <button class="filter-button" type="submit">
                <i class="fas fa-filter"></i> Search
            </button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-5">
            <?php
            foreach ($locations as $location) {
                $blood_level = rand(50, 100); // Replace with actual blood level from the database
                echo '<div class="hospital-card">
                    <div class="hospital-info">
                        <h4>' . htmlspecialchars($location['loc_name']) . '</h4>
       
						<div class="rating">
                            ' . htmlspecialchars($location['rating']) . ' ★★★★☆
                        </div>
						<img src="../assets/images/' . $location['image'] . '" alt="' . $location['loc_name'] . '" class="hospital-image">
                        <p><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($location['address']) . '</p>
                        <form action="book_appointment.php" method="POST">
                            <input type="hidden" name="loc_id" value="' . htmlspecialchars($location['loc_id']) . '">
                            <button type="submit" class="book-appointment-btn">Book Appointment</button>
                        </form>
                    </div>
                    <div class="hospital-chart">
                        <div class="blood-level-chart">
                             <canvas id="bloodChart-' . htmlspecialchars($location['loc_id']) . '" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>';
            }
            ?>
        </div>
        <div class="col-md-7">
            <div id="map"></div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.0.1/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize map
var map = L.map('map').setView([3.0733, 101.5185], 13); // Default Shah Alam coordinates
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add markers for filtered locations dynamically
<?php foreach ($locations as $location) { ?>
    L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>])
        .bindPopup('<?php echo htmlspecialchars($location['loc_name']); ?>')
        .addTo(map);
<?php } ?>
</script>
<script>
	<?php foreach ($locations as $location): ?>
    var ctx = document.getElementById('bloodChart-<?php echo htmlspecialchars($location['loc_id']); ?>').getContext('2d');
    var bloodData = Object.values(bloodData_<?php echo htmlspecialchars($location['loc_id']); ?>);

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'],
            datasets: [{
                label: 'Blood Levels',
                data: bloodData,
                backgroundColor: ['#D32F2F', '#F44336', '#1976D2', '#0288D1', '#4CAF50', '#8BC34A', '#FFC107', '#FFEB3B'],
            }]
        },
        options: {
            responsive: true,
			 maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' units';
                        }
                    }
                }
            }
        }
    });
<?php endforeach; ?>
</script>
</html>
<?php
session_start();
include ('../header.php');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['eligibility_checked'])) {
    header('Location: eligibility_check.php');
    exit();
}

include('../db_connect.php');
// Retrieve user details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "User123";

// Get location details from POST
$location_id = $_POST['loc_id'];

// Fetch location details
$sql = "SELECT * FROM location WHERE loc_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $location_id);
$stmt->execute();
$location = $stmt->get_result()->fetch_assoc();

// Fetch available time slots for the location
$sql_slots = "SELECT slot_id, avail_date, avail_time FROM available_time_slots 
              WHERE loc_id = ? AND status = 'available' 
              ORDER BY avail_date, avail_time";
$stmt_slots = $conn->prepare($sql_slots);
$stmt_slots->bind_param("i", $location_id);
$stmt_slots->execute();
$result_slots = $stmt_slots->get_result();

$available_slots = [];
while ($row = $result_slots->fetch_assoc()) {
    $available_slots[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - MYBlood</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
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

        .step.completed .step-number {
            background-color: #28a745;
        }

        .step.active .step-number {
            background-color: #D32F2F;
        }

        .location-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .location-header i {
            margin-right: 10px;
            font-size: 24px;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .confirm-btn {
            background-color: #000;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #D32F2F;
            text-decoration: none;
        }
    </style>
</head>
<body>
<!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="./assets/images/logo.png" alt="MyBlood Logo">
            MyBlood
        </div>
        <div class="user-info">
			<span class="username">
				<?php echo htmlspecialchars($username); ?>
				<img src="./assets/images/user-avatar.png" alt="User Avatar" class="user-avatar">
			</span>
		</div>
    </div>
	
    <div class="container-dashboard">
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number">✓</div>
                <div class="step-text">Check Eligibility</div>
            </div>
            <div class="step completed">
                <div class="step-number">✓</div>
                <div class="step-text">Choose Locations</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-text">Book Appointment</div>
            </div>
        </div>

        <div class="location-header">
            <i class="fas fa-map-marker-alt"></i>
            <h2><?php echo htmlspecialchars($location['loc_name']); ?></h2>
        </div>
        <form action="process_appointment.php" method="POST">
            <input type="hidden" name="location_id" value="<?php echo $location_id; ?>">
            
            <div class="form-group">
                <label>Available Appointment Date</label>
                <select class="form-control" name="appointment_date" required>
                    <option value="">Select Date</option>
                    <?php foreach ($available_slots as $slot) { ?>
                        <option value="<?php echo $slot['avail_date']; ?>">
                            <?php echo date('l, F j, Y', strtotime($slot['avail_date'])); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Available Time</label>
                <select class="form-control" name="appointment_time" required>
                    <option value="">Select Time</option>
                    <?php foreach ($available_slots as $slot) { ?>
                        <option value="<?php echo $slot['avail_time']; ?>">
                            <?php echo date('g:i A', strtotime($slot['avail_time'])); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" class="confirm-btn">Confirm Appointment</button>
        </form>

        <a href="choose_location.php" class="back-link">Back to Locations</a>
    </div>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
	<script>
$(document).ready(function() {
  // Handle the change event for the date dropdown
  $('select[name="appointment_date"]').on('change', function() {
    var selectedDate = $(this).val();
    var timeDropdown = $('select[name="appointment_time"]'); // Get the time dropdown

    // Clear any existing options in the time dropdown
    timeDropdown.empty().append('<option value="">Select Time</option>');

    // Iterate through the available slots
    $.each(available_slots, function(index, slot) { 
      if (slot.avail_date == selectedDate) { 
        timeDropdown.append('<option value="' + slot.avail_time + '">' +
                          moment(slot.avail_time, 'HH:mm:ss').format('h:mm A') + '</option>');
      }
    });
  });
});

// You'll likely need to define the available_slots variable globally
var available_slots = <?php echo json_encode($available_slots); ?>; 
</script>

</body>
</html>
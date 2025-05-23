<?php
// Start session
session_start();

include 'header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once 'db_connect.php';

// Check if the user is a donor
if ($_SESSION['role'] !== 'donor') {
    header('Location: unauthorized.php');
    exit();
}

// Retrieve user details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "User123";

// Check if user details are set in session
$full_name = $_SESSION['full_name'] ?? null;
$blood_type = $_SESSION['blood_type'] ?? null;
$nric = $_SESSION['ic_num'] ?? null;

// If session variables are not set, fetch from database
if (!$full_name || !$blood_type || !$nric) {
    try {
        $stmt = $pdo->prepare("SELECT full_name, blood_type, ic_num FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $full_name = $user_data['full_name'] ?? "Your Name";
            $blood_type = $user_data['blood_type'] ?? "Unknown";
            $nric = $user_data['ic_num'] ?? "Not Provided";
        } else {
            // If no user data found, set default values
            $full_name = "Your Name";
            $blood_type = "Unknown";
            $nric = "Not Provided";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Query user donation history
$times_donated = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM appointments WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $times_donated = $result['total'] ?? 0;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch upcoming appointment
$upcoming_appointment = [
    'date' => 'No appointment scheduled',
    'countdown' => 'N/A'
];
try {
    $stmt = $pdo->prepare("
        SELECT a.app_id AS appointment_id, ats.avail_date AS appointment_date, 
           ats.avail_time AS appointment_time, loc.loc_name AS location_name,
           ats.loc_id AS location_id 
		FROM appointments AS a
		JOIN available_time_slots AS ats ON a.slot_id = ats.slot_id
		JOIN location AS loc ON ats.loc_id = loc.loc_id
		WHERE a.user_id = ? AND a.status = 'pending'
		ORDER BY ats.avail_date ASC, ats.avail_time ASC
    ");
    $stmt->execute([$user_id]);
    $bookedAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error retrieving booked appointments: " . $e->getMessage();
}
// Retrieve location details if POST data is provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loc_id'])) {
    $location_id = $_POST['loc_id']; // Ensure location_id is passed in the POST request

    // Fetch location details for the given location ID
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYBlood Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">

	<style>
		body {
		font-family: Arial, sans-serif;
		background-color: #f0f0f0; /* Light gray background */
		margin: 0;
		padding: 0;
	}

	.container {
		max-width: 1200px;
		margin: 20px auto;
		padding: 20px;
	}

	.header {
		text-align: center;
		margin-bottom: 30px;
	}

	.header h1 {
		margin-bottom: 10px;
		color: #000000; /* Black text */
	}

	.container-dashboard {
		max-width: 1200px;
		margin: 20px auto;
		padding: 20px;
		background-color: #FFFFFF; /* White background */
		border-radius: 10px;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	}

	h2 {
		font-weight: bold;
		color: #000000; /* Black text */
	}

	.btn-primary {
		background-color: #000000; /* Black background */
		border-color: #000000; /* Black border */
		color: #FFFFFF; /* White text */
	}

	.btn-primary:hover {
		background-color: #555555; /* Dark gray */
		border-color: #555555;
	}

	.navbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		background-color: #000000; /* Black background */
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

	.grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
		gap: 20px;
		justify-content: center; /* Centers the boxes horizontally */
		align-items: stretch; /* Ensures all boxes have the same height */
	}

	.activities-section .grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
		gap: 20px;
		justify-content: center; /* Centers the boxes horizontally */
		align-items: stretch; /* Ensures all boxes have the same height */
	}

	.card {
		background-color: #FFFFFF; /* White background */
		border-radius: 10px;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		padding: 20px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		border: 2px solid #000000;
	}

	.map-placeholder {
    background-image: url('./assets/images/map.jpg');
    background-size: cover; /* Ensures the image covers the entire container */
    background-position: center; /* Centers the image */
    height: 300px; /* Set the height of the placeholder */
    border-radius: 8px; /* Optional: Rounds the corners */
    margin: 10px 0; /* Adds spacing around the element */
}

	.card h4 {
		margin-bottom: 15px;
		color: #000000; /* Black text */
		font-weight: bold;
	}
	
	.blood-donation-centers h4 {
    margin-bottom: 0; /* Removes default margin below the h4 */
	}
	
	.card p {
		margin-top: 0px;
		color: #000000; /* Black text */
	}
	.blood-donation-centers p {
		margin-top: 0; /* Removes default margin above the p */
		margin-bottom: 11rem; /* Adds some spacing below the p (optional) */
	}

	
	.image-placeholder {
	background-image: url('./assets/images/map.jpg');
    background-size: cover; /* Ensures the image covers the entire container */
    background-position: center; /* Centers the image */
    height: 200px; /* Set the height of the placeholder */
    border-radius: 8px; /* Optional: Rounds the corners */
    margin: 10px 0; /* Adds spacing around the element */
	}

	.activities-section h3 {
		margin-top: 40px;
		color: #000000; /* Black text */
	}

	.countdown-timer {
		display: flex;
		justify-content: center;
		align-items: center;
		gap: 10px;
		font-family: 'Poppins', sans-serif;
		margin-top: 8px;
		margin-bottom: 15px;
	}

	.time-box {
		display: inline-block;
		background-color: #000000; /* Black background */
		color: #FFFFFF; /* White text */
		font-size: 20px;
		font-weight: bold;
		padding: 10px 15px;
		border-radius: 8px;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		text-align: center;
		min-width: 50px;
		background: linear-gradient(45deg, #000000, #555555);
		background-size: 200% 200%;
		animation: gradientShift 3s infinite;
	}

	.label {
		font-size: 12px;
		color: #555;
		font-weight: 600;
		text-align: center;
	}

	.time-box:hover {
		transform: scale(1.1);
		transition: transform 0.2s ease-in-out;
	}

	@keyframes gradientShift {
		0% { background-position: 0% 50%; }
		50% { background-position: 100% 50%; }
		100% { background-position: 0% 50%; }
	}

	.donation-history {
		display: flex;
		justify-content: space-between;
	}

	.donation-history div {
		text-align: center;
	}

	.dashboard-card {
		border: 1px solid #ddd;
		border-radius: 10px;
		padding: 20px;
		margin: 10px 0;
		background-color: #f8f9fa;
		text-align: center;
	}

	.dashboard-card img {
		max-width: 100%;
		border-radius: 10px;
	}

	.dashboard-card h4 {
		font-weight: bold;
		color: #000000; /* Black text */
	}

	.donation-history {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.donation-card {
		text-align: left;
	}

	.privilege {
		text-align: left;
	}

	.appointments-section {
		margin: 20px;
		padding: 20px;
		background-color: #ffffff; /* White background */
		border-radius: 8px;
	}

	footer {
		background-color: #000000; /* Black background */
		color: #ffffff; /* White text */
		text-align: center;
		padding: 20px;
		font-family: Arial, sans-serif;
		margin-top: auto;
	}

	footer p {
		margin: 0;
		font-size: 14px;
	}

	.card button.btn-danger {
		background-color: #ff5c5c;
		color: white;
		border: 1px solid #ff5c5c;
		border-radius: 20px;
		padding: 10px 20px;
		font-weight: bold;
		font-size: 14px;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.card button.btn-danger:hover {
		background-color: #ff3b3b;
		border-color: #ff3b3b;
		transform: scale(1.05);
	}

	.btn-container {
		display: flex;
		gap: 10px;
		align-items: center;
		justify-content: start;
	}

	.info-grid {
		display: flex;
		gap: 20px;
	}

	.info-grid .card {
		flex: 1;
		min-width: 300px;
	}
	@media (max-width: 768px) {
		.info-grid {
			flex-direction: column;
		}
	}
	.blood-donation-centers {
		background-image: url('./assets/images/map.jpg');
		background-size: cover; /* Ensures the image covers the card area */
		background-position: center; /* Centers the image */
		background-repeat: no-repeat; 
		background-color: rgba(255, 255, 255, 0.5); /* Adds a semi-transparent overlay */
		background-blend-mode: overlay; /* Blends the color with the image */
		color: #fff; /* Optional: Adjust text color for better contrast */
		padding: 20px; /* Adjust padding for content inside the card */
		border-radius: 8px; /* Optional: Add rounded corners to the card */
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for styling */
	}
	.book-appointment {
		background-image: url('./assets/images/donate.jpg');
		background-size: cover; /* Ensures the image covers the card area */
		background-position: center; /* Centers the image */
		background-repeat: no-repeat; 
		background-color: rgba(255, 255, 255, 0.5); /* Adds a semi-transparent overlay */
		background-blend-mode: overlay; /* Blends the color with the image */
		color: #fff; /* Optional: Adjust text color for better contrast */
		padding: 20px; /* Adjust padding for content inside the card */
		border-radius: 8px; /* Optional: Add rounded corners to the card */
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for styling */
	}
	</style>
</head>
<body>

	 <!-- Main Container -->
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Hi, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Ready to be a hero and save lives?</p>
        </div>

	<!-- Grid Section -->
        <div class="grid">
            <!-- Blood Donation Centers -->
            <div class="card blood-donation-centers">
				<h4>Blood Donation Centers:</h4>
				<p>See available blood donation spots near you</p>
				<a href="location.php" class="btn btn-primary mt-3">View Location</a>
			</div>

			
			<!-- Book Appointment -->
			<div class="card book-appointment">
				<h4>Book Appointment Now</h4>
				<a href="book_appointment/eligibility_check.php" class="btn btn-primary">Start</a>
			</div>
		</div>
				
			<!-- Activities Section -->
			<div class="activities-section">
			<h3>Your Activities</h3>
			<!-- Upcoming Appointments -->
			<div class="info-grid">
			<div class="card">
			<h4>Upcoming Appointment:</h4>
			<?php if (!empty($bookedAppointments)): ?>
				<?php foreach ($bookedAppointments as $appointment): ?>
						<h5><?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?></h5>
						<p><i class="fas fa-clock"></i> Time: <?php echo htmlspecialchars($appointment['appointment_time']); ?></p>
						<p><i class="fas fa-map-marker-alt"></i> Location: <?php echo htmlspecialchars($appointment['location_name']); ?></p>
						<div class="countdown-timer" id="countdown-timer">
							<span class="time-box" id="days">00</span>
							<span class="label">Days</span>
							<span class="time-box" id="hours">00</span>
							<span class="label">Hours</span>
							<span class="time-box" id="minutes">00</span>
							<span class="label">Minutes</span>
							<span class="time-box" id="seconds">00</span>
							<span class="label">Seconds</span>
						</div>
						<script>
							const appointmentDate = "<?php echo $appointment['appointment_date']; ?>";
							const appointmentTime = "<?php echo $appointment['appointment_time']; ?>";
						</script>
						<!-- Edit Form -->
						<div class="btn-container">
						<!-- Reschedule Button -->
						<button class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $appointment['appointment_id']; ?>">
							Reschedule
						</button>
						
						<!-- Cancel Form -->
						<form method="POST" action="update_appointment.php" style="display:inline;">
							<input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
							<button type="submit" name="cancel" class="btn btn-danger"  style="background-color:#B71C1C;">Cancel</button>
						</form>
					</div>

					<!-- Edit Modal -->
					<div class="modal fade" id="editModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="editModalLabel">Edit Appointment</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<!-- Form to Edit Appointment -->
									<form action="update_appointment.php" method="POST">
										<input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>"> <!-- Hidden field for appointment_id -->
										<input type="hidden" name="location_id" value="<?php echo $appointment['location_id']; ?>"> <!-- Hidden field for location_id -->

										<!-- Fetch available slots for the location dynamically -->
										<?php
										// Ensure $appointment['location_id'] is available before querying
										$location_id = $appointment['location_id']; 
										$available_slots = [];
										$sql_slots = "SELECT slot_id, avail_date, avail_time FROM available_time_slots 
													  WHERE loc_id = ? AND status = 'available' 
													  ORDER BY avail_date, avail_time";
										$stmt_slots = $pdo->prepare($sql_slots);
										$stmt_slots->execute([$location_id]);
										$available_slots = $stmt_slots->fetchAll(PDO::FETCH_ASSOC);
										?>
										
										<div class="form-group">
											<label>Available Appointment Date</label>
											<select class="form-control" id="appointment_date_<?php echo $appointment['appointment_id']; ?>" name="appointment_date" required>
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
											<select class="form-control" id="appointment_time_<?php echo $appointment['appointment_id']; ?>" name="appointment_time" required>
												<option value="">Select Time</option>
												<?php foreach ($available_slots as $slot) { ?>
													<option value="<?php echo $slot['avail_time']; ?>">
														<?php echo date('g:i A', strtotime($slot['avail_time'])); ?>
													</option>
												<?php } ?>
											</select>
										</div>

										<button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
									</form>
								</div>
							</div>
						</div>
					</div>

				<?php endforeach; ?>
			<?php else: ?>
				<p>No upcoming appointments.</p>
			<?php endif; ?>
			</div>

        <!-- Donation History -->
			<div class="card">
				<h4>Donation History</h4>
				 <p><strong>Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
				 <p><strong>NRIC:</strong> <?php echo htmlspecialchars($nric); ?></p>
						<p><strong>Blood Type:</strong> <?php echo htmlspecialchars($blood_type); ?></p>
					<div class="donation-history">
					<div>
						Times Donated:
						<h2 style="color:#D32F2F;"><?php echo htmlspecialchars($times_donated); ?></h4>
			
					</div>
					<div style="text-align:left;">
						Privileges Earned:
						<ul style="margin-top:10px;">
							<li>Free outpatient and medical treatment</li>
							<li>Second-class wards for upcoming 4 months</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
    <!-- Footer -->
    <footer>
        <p>&copy; 2025 MYBlood. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

	 <!-- Countdown Timer Script -->
    <script>
    function startCountdown(appointmentDate, appointmentTime) {
    // Combine the date and time into a single Date object
    const targetDateTime = new Date(`${appointmentDate}T${appointmentTime}`);

    function updateTimer() {
        const now = new Date().getTime();
        const distance = targetDateTime - now;

        if (distance <= 0) {
            // If the countdown is over
            document.getElementById("countdown-timer").innerHTML = "<span>Appointment is happening now!</span>";
            return;
        }

        // Calculate time components
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Update the countdown display
        document.getElementById("days").textContent = days.toString().padStart(2, '0');
        document.getElementById("hours").textContent = hours.toString().padStart(2, '0');
        document.getElementById("minutes").textContent = minutes.toString().padStart(2, '0');
        document.getElementById("seconds").textContent = seconds.toString().padStart(2, '0');
    }

    // Update the timer every second
    setInterval(updateTimer, 1000);
}

// Start the countdown using the appointment date and time
startCountdown(appointmentDate, appointmentTime);


    </script>
	<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.submit-edit').forEach(button => {
            button.addEventListener('click', function () {
                const appointmentId = this.getAttribute('data-id');
                const form = document.getElementById(`editForm${appointmentId}`);
                const formData = new FormData(form);

                // Send AJAX request
                fetch('update_appointment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Appointment updated successfully!');
                        location.reload(); 
                    } else {
                        alert('Failed to update appointment: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    });
</script>
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

var available_slots = <?php echo json_encode($available_slots); ?>; 
</script>

</body>
</html>
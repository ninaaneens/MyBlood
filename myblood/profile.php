<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// Include database connection
include('db_connect.php');

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user profile data
$sql_user = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>User Profile</title>
    <style>
       body {
    font-family: 'Poppins', sans-serif;
    background-color: #ffffff;
    color: #121212; /* Change text color to black for better readability on white */
}

.card {
    background-color: #f4f4f4; /* Light gray card for subtle contrast */
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Softer shadow for light theme */
}

.profile-header {
    background-color: #121212; /* Black header */
    color: #ffffff; /* White text for the header */
    padding: 40px;
    border-radius: 12px 12px 0 0;
    text-align: center;
}

h3 {
    font-size: 1.8rem;
    color: #ffffff; /* Black text for headings */
}

p {
    font-size: 0.9rem;
    color: #ffffff; /* Black text for body */
}

label {
    font-weight: 600;
    color: #121212; /* Black labels */
}

.form-control {
    background-color: #ffffff; /* White input fields */
    color: #121212; /* Black text inside input fields */
    border: 1px solid #cccccc; /* Light border */
    border-radius: 8px;
}

.form-control:focus {
    background-color: #ffffff;
    color: #121212;
    border-color: #121212; /* Black border on focus */
    box-shadow: none;
}

.btn-primary {
    background-color: #121212; /* Black button */
    color: #ffffff; /* White text */
    border: none;
    padding: 10px 20px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
}

.btn-primary:hover {
    background-color: #333333; /* Darker black on hover */
}

.btn-secondary {
    background-color: #ffffff; /* White button */
    color: #121212; /* Black text */
    border: 1px solid #121212; /* Black border */
    padding: 10px 20px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
}

.btn-secondary:hover {
    background-color: #f4f4f4; /* Slightly darker white on hover */
    color: #000000; /* Black text remains */
}


    </style>
</head>
<body>
<div class="container mt-5">
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="profile-header">
            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="card-body">
            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="form-group mb-3">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="full_name">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="password">Change Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

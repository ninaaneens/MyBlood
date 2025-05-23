<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include('db_connect.php');

// Get user ID from session
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize variables
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $blood_type = $_POST['blood_type'] ?? '';
    $profile_picture_path = null;

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES['profile_picture']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
            $profile_picture_path = $target_path;
        }
    }

    // Check if the username is unique
    $check_username_sql = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $check_username_stmt = $conn->prepare($check_username_sql);
    $check_username_stmt->bind_param("si", $username, $user_id);
    $check_username_stmt->execute();
    $check_username_result = $check_username_stmt->get_result();

    if ($check_username_result->num_rows > 0) {
        $_SESSION['error'] = "Username is already taken. Please choose another one.";
        header('Location: profile.php');
        exit;
    }

    // Build the base update query
    $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, blood_type = ?";
    $params = [$username, $full_name, $email, $phone, $blood_type];

    // Add password update if provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params[] = $hashed_password;
    }

    // Add profile picture update if uploaded
    if ($profile_picture_path) {
        $sql .= ", profile_picture = ?";
        $params[] = $profile_picture_path;
    }

    $sql .= " WHERE user_id = ?";
    $params[] = $user_id;

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params) - 1) . 'i', ...$params);

    // Update the session
    $_SESSION['username'] = $username;

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['error'] = "Failed to update profile. Please try again.";
        header('Location: profile.php');
        exit;
    }
}

// Fetch current user data to populate the form
$sql = "SELECT username, full_name, email, phone, blood_type, profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
</head>
<body>
    <h1>Update Profile</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>

        <label for="full_name">Full Name:</label><br>
        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

        <label for="phone">Phone:</label><br>
        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required><br><br>

        <label for="blood_type">Blood Type:</label><br>
        <input type="text" name="blood_type" id="blood_type" value="<?php echo htmlspecialchars($user['blood_type']); ?>" required><br><br>

        <label for="password">New Password (leave blank if not changing):</label><br>
        <input type="password" name="password" id="password"><br><br>

        <label for="profile_picture">Profile Picture:</label><br>
        <input type="file" name="profile_picture" id="profile_picture"><br><br>

        <button type="submit">Update Profile</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>

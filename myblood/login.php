<?php 
// Start a session
session_start();

// Connect to the database (using your 'myblood' database)
$conn = new mysqli('localhost', 'root', '', 'myblood');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message variable
$error_message = "";

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
	$expected_role = $_POST['expected_role'];

    // Sanitize inputs
    $email = $conn->real_escape_string($email);

    // Use prepared statement to prevent SQL injection
    $sql = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $sql->bind_param('s', $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if the role matches the expected role
            if ($user['role'] !== $expected_role) {
                $error_message = "Access denied: Incorrect login type.";
            } else {
			// Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username']; 
            $_SESSION['role'] = $user['role'];  // Store the role in session

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /myblood/admin'); // Redirect admin to /admin page
            } else {
                header('Location: dashboard.php'); // Redirect regular users to their dashboard
            }
            exit();
			}
        } else {
            // Invalid password
            $error_message = "Invalid password.";
        }
    } else {
        // No account found
        $error_message = "No account found with this email.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - MYBlood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

header {
    position: relative;
    width: 100%;
}

.main-content {
    display: flex;
    justify-content: center;
    align-items: center;
    height: calc(100vh - 60px); /* Adjust the height to subtract the header's height */
    padding-top: 20px;
}
        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .tabs {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            font-weight: bold;
        }
        .tab.active {
            background-color: #000;
            color: white;
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
        .btn-primary {
            background-color: #000;
            border: none;
        }
        .btn-primary:hover {
            background-color: #444;
        }
        footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<body>
<?php include 'homepage_header.php'; ?>
<div class="main-content">
<div class="login-container">
    <!-- Tabs for different login types -->
    <div class="tabs">
        <div class="tab active" onclick="showForm('user')">User</div>
        <div class="tab" onclick="showForm('admin')">Admin</div>
    </div>
	
	<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error_message; ?>
    </div>
	<?php endif; ?>
    <!-- User Login Form -->
    <form id="user-form" class="form-container active" action="login.php" method="POST">
        <input type="hidden" name="expected_role" value="donor">
		<div class="mb-3">
            <label for="user-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="user-email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="mb-3">
            <label for="user-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="user-password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <!-- Admin Login Form -->
    <form id="admin-form" class="form-container" action="login.php" method="POST">
        <input type="hidden" name="expected_role" value="admin">
		<div class="mb-3">
            <label for="admin-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="admin-email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="mb-3">
            <label for="admin-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="admin-password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
	
	

    <!-- Footer with Sign Up link -->
    <footer>
        <p>Don’t have an account? <a href="signup.php" class="text-danger">Sign up here</a></p>
    </footer>
</div>
</div>

<script>
    function showForm(tabName) {
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        // Remove active class from all forms
        document.querySelectorAll('.form-container').forEach(form => form.classList.remove('active'));
        // Add active class to the selected tab and form
        document.querySelector(`.tab[onclick="showForm('${tabName}')"]`).classList.add('active');
        document.getElementById(`${tabName}-form`).classList.add('active');
    }
</script>

</body>
</html>

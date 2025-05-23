<?php
// Initialize error message variable
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $full_name = trim($_POST['full-name']);
    $ic_number = trim($_POST['ic-number']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $marital_status = $_POST['marital-status'];
    $blood_type = $_POST['blood-type'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

   // Check if passwords match
if ($password !== $confirm_password) {
    $error_message = "Passwords do not match!";
}

// Proceed with signup logic if no error
if (empty($error_message)) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'myblood');

    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username already exists
    $check_username = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check_username->bind_param('s', $username);
    $check_username->execute();
    $username_result = $check_username->get_result();

    if ($username_result->num_rows > 0) {
        $error_message = "Username already exists!";
    } else {
        // Check if email already exists
        $sql = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $sql->bind_param('s', $email);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email already exists!";
        } else {
            // Insert user into the database
            $sql = $conn->prepare("INSERT INTO users (username, email, password, phone, full_name, ic_num, date_of_birth, gender, marital_stat, address, blood_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param(
                'sssssssssss',
                $username,
                $email,
                $hashed_password,
                $phone,
                $full_name,
                $ic_number,
                $dob,
                $gender,
                $marital_status,
                $address,
                $blood_type
            );
            
            if ($sql->execute()) {
                header('Location: login.php');
                exit();
            } else {
                $error_message = "Error during sign-up, please try again.";
            }
        }
    }

    // Close database connection
    $conn->close();
    }
}
?>
<?php include 'homepage_header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MYBlood</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
       body {
    background-color: #f0f0f0;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.page-layout {
    position: relative;
    display: flex;
    justify-content: flex-end;
    min-height: calc(100vh - 60px);
    background-color: #f0f0f0;
}

.background-image {
    position: absolute;
    left: 0;
    top: 0;
    width: 40%;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0;
}

.background-image img {
    width: 1000px;
    height: 1000px;
    object-fit: contain;
}


.form-container {
    width: 70%;
    margin: 20px 40px;
    padding: 10px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}


@media (max-width: 768px) {
    .page-layout {
        flex-direction: column;
    }
    
    .background-image {
        width: 100%;
        height: 200px;
        position: relative;
    }
    
    .form-container {
        width: auto;
        margin: 20px;
    }
}

.login-text {
    text-align: right;
    margin-bottom: 20px;
}

.login-text a {
    color: #dc3545;
    text-decoration: none;
}

.form-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.form-box {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-box h4 {
    color: black;
    margin-bottom: 20px;
    font-size: 18px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    color: black;
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn-primary {
    background-color: black;
    color: white;
    border: none;
    padding: 10px 20px;
    width: auto;
    display: block;
    margin: 20px auto 0;
    min-width: 120px;
}

.form-check {
    margin-top: 15px;
}

.form-check-input {
    width: auto;
    margin-right: 8px;
}

.form-check-label {
    font-size: 14px;
    color: #666;
}

@media (max-width: 768px) {
    .form-wrapper {
        grid-template-columns: 1fr;
    }
    
    .form-container {
        margin: 20px;
    }
}
</style>
</head>
<body>


    </header>

    <!-- Sign-Up Form Section -->
    <main>
    <!-- Main Content -->
    <div class="page-layout">
        <div class="background-image">
            <img src="./assets/images/signup.png" alt="Background Logo">
        </div>
        <div class="form-container">
            <h2>Register Account</h2>
            <form action="signup.php" method="POST">
                <div class="form-wrapper">
                    <div class="form-box">
                        <h4>Account Information</h4>
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
        
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
        
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
        
                        <label for="confirm-password">Confirm Password:</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-enter your password" required>
                    </div>
        
                    <div class="form-box">
                        <h4>Personal Information</h4>
                        <label for="full-name">Full Name:</label>
                        <input type="text" id="full-name" name="full-name" placeholder="Full Name According to IC" required>
        
                        <label for="ic-number">IC Number:</label>
                        <input type="text" id="ic-number" name="ic-number" placeholder="000000-00-0000" required>
        
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="dob" required>
        
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
        
                        <label for="marital-status">Marital Status:</label>
                        <select id="marital-status" name="marital-status" required>
                            <option value="">Select Marital Status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                        </select>
        
                        <label for="blood-type">Blood Type:</label>
                        <select id="blood-type" name="blood-type" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
        
                        <label for="phone">Mobile Phone Number:</label>
                        <input type="tel" id="phone" name="phone" placeholder="+60 XXXXXXXXXX" required>
        
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" placeholder="Enter your home address" required>
        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="declaration" required>
                            <label class="form-check-label" for="declaration">I declare that all the information provided is true and accurate.</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary mt-3">Sign Up</button>
            </form>
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>        
    </main>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2025 MYBlood. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php if(isset($error_message) && !empty($error_message)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: '<?php echo $error_message; ?>',
            confirmButtonColor: '#dc3545'
        });
    </script>
    <?php endif; ?>
</body>

</html>
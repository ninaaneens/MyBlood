<?php
// Start the session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-pzjw8f+ua7Kw1TIqjiuyFhCnM6J6p7MO8R9j13mFs5d4f8c/hBuhmZDeBu8h91wj" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Malaysia Blood Donation Portal | MYBlood</title> 
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <style>
       .navbar {
    background-color: #000000 !important;
    padding: 10px 20px;
}

.navbar-brand {
    display: flex;
    align-items: center;
    font-family: 'Poppins', sans-serif;
    font-size: 1.8rem;
    font-weight: bold;
    color: #FFFFFF !important;
}

.navbar-brand img {
    width: 30px;
    height: 30px;
    margin-right: 15px;
}

.navbar-nav {
    margin-right: 20px;
}

.navbar-nav .nav-link {
    color: #FFFFFF !important;
    font-size: 1rem;
    font-weight: 500;
    padding: 8px 15px;
}

.auth-buttons {
    display: flex;
    gap: 10px;
}

.btn-outline-light {
    color: #FFFFFF !important;
    border: 1px solid #FFFFFF;
    padding: 6px 20px;
}

.btn-light {
    background-color: #FFFFFF;
    color: #000000 !important;
    padding: 6px 20px;
}

/* Remove hover effects to match wireframe */
.navbar-nav .nav-link:hover {
    color: #FFFFFF !important;
    transform: none;
}

.navbar {
    background-color: #000000 !important;
    padding: 10px 20px;
    position: relative;
    z-index: 1000;
}


        .btn-register {
            background-color: #FFFFFF;
            color: #000000;
            border: 2px solid #FFFFFF;
            font-size: 1rem;
            font-weight: bold;
            padding: 8px 20px;
            border-radius: 25px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-register:hover {
            background-color: #000000;
            color: #FFFFFF;
        }

        .dropdown-menu {
            min-width: 150px;
            padding: 0.5rem;
        }

        .dropdown-item {
            font-size: 0.9rem;
            padding: 8px 15px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #000;
        }
        main {
    margin-top: 60px; /* Adjust based on header height */
    position: relative;
    z-index: 1;
}

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="./assets/images/logo.png" alt="MYBlood Logo">
            MyBlood
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav me-3">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $is_logged_in ? 'dashboard.php' : 'login.php'; ?>">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="location.php">Location</a>
                </li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn btn-outline-light">Log In</a>
                <a href="signup.php" class="btn btn-light">Register</a>
            </div>
        </div>
    </div>
</nav>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

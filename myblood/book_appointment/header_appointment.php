<?php  
// Start the session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';
?>
<!doctype html>
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
        /* General Navbar Styling */
        .navbar {
            background-color: #000000 !important; /* Black */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: bold;
            color: #FFFFFF !important; /* White */
        }

        .navbar-brand img {
            width: 30px;
            height: auto;
            margin-right: 15px;
            border-radius: 8px;
            object-fit: contain;
        }

        .navbar-nav .nav-link {
            color: #FFFFFF !important;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #CCCCCC;
            transform: translateY(-3px);
            text-decoration: none;
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
    </style>
</head>
<body>
    <!-- Navigation bar --> 
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="MYBlood Logo">
            <span>MYBlood</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-person" style="font-size: 1.3em; padding-right: 8px;"></i> <?php echo htmlspecialchars($username); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="profile.php"><i class="bi bi-pencil"></i> Edit Profile</a>
                            <a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-register nav-link" href="../signup.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Required Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha384-oPtwQVRbCvHpCuKDTYM25aOzVV9A1UdFvMc7VglFGOzG1pJ9Ujh6h1NUr3eGdQgs" crossorigin="anonymous"></script>
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-zt7jQTFujv8jB7IzbgdGHlx2vA5VVCLi0huxF46ddanftzHBd13vJjtt66lneT5s" crossorigin="anonymous"></script>
    <!-- Bootstrap JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIqjiuyFhCnM6J6p7MO8R9j13mFs5d4f8c/hBuhmZDeBu8h91wj" crossorigin="anonymous"></script>
</body>
</html>

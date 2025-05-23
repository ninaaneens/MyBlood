<?php
// Add this at the top of sidebar.php
include_once('../db_connect.php'); // If not already included

// Get admin name from session or database
$admin_query = "SELECT full_name FROM users WHERE user_id = " . $_SESSION['user_id'] . " AND role = 'admin'";
$admin_result = mysqli_query($conn, $admin_query);
$admin_name = "Admin"; // Default fallback

if ($admin_result && mysqli_num_rows($admin_result) > 0) {
    $admin_data = mysqli_fetch_assoc($admin_result);
    $admin_name = $admin_data['full_name'];
}
?>

<div class="sidebar" id="mySidebar">
    <div class="side-header" style="text-align: center; padding-top: 20px;">
        <img src="../assets/images/logo.png" width="100" height="100" alt="MYBlood">
        <h5 style="color: white;">Hello, <?php echo htmlspecialchars($admin_name); ?></h5>
    </div>


    <!-- Navigation Links -->
    <a href="./index.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="./donor.php"><i class="fa fa-users"></i> Donors</a>
    <a href="./blood_inventory.php"><i class="fa fa-tint"></i> Blood Inventory</a>

    <!-- Appointments Dropdown -->
    <div class="dropdown">
        <a href="#" onclick="toggleDropdown()"><i class="fa fa-calendar"></i> Appointments 
            <i class="fa fa-caret-down"></i></a>
        <div class="dropdown-content">
            <a href="./time_slots.php"><i class="fa fa-edit"></i> Available Time Slots</a>
            <a href="./appointments_pending.php"><i class="fa fa-clock"></i> Pending</a>
            <a href="./appointments_completed.php"><i class="fa fa-check"></i> Completed</a>
            <a href="./appointments_cancelled.php"><i class="fa fa-times"></i> Cancelled</a>
        </div>
    </div>

    <!-- Other Links -->
    <a href="./location.php"><i class="fa fa-hospital-o"></i> Donation Centers</a>
    <a href="./eligibility_check.php"><i class="fa fa-check-square-o"></i> Eligibility Records</a>

    <!-- Logout Button -->
    <a href="../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Toggle Sidebar Button -->
<div id="main">
    <button id="toggleSidebar" onclick="toggleSidebar()">☰</button>
</div>

<style>
.sidebar {
    height: calc(100% - 70px);
    width: 250px;
    position: fixed;
    top: 70px;
    left: 0;
    background-color: #333;
    overflow-x: hidden;
    transition: 0.3s;
    z-index: 999;
}

#toggleSidebar {
    font-size: 20px;
    cursor: pointer;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
}

#main {
    margin-left: 250px;
    margin-top: 70px;
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: calc(100% - 250px);
}

.sidebar a {
    padding: 10px 15px;
    text-decoration: none;
    font-size: 18px;
    color: white;
    display: block;
}

.sidebar a:hover {
    background-color: #575757;
}

.dropdown-content {
    display: none;
}

.dropdown-content a {
    padding-left: 30px;
}

.dropdown.active .dropdown-content {
    display: block;
}

/* Collapsed states */
.sidebar.collapsed {
    width: 0;
    margin-left: -250px;
}

#main.collapsed {
    margin-left: 0;
    width: 100%;
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById("mySidebar");
    const mainContent = document.getElementById("main");
    
    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("collapsed");
    
    // Center content when sidebar is collapsed
    if (mainContent.classList.contains("collapsed")) {
        mainContent.style.justifyContent = "center";
    } else {
        mainContent.style.justifyContent = "flex-start";
    }
}

function toggleDropdown() {
    const dropdown = document.querySelector('.dropdown');
    dropdown.classList.toggle('active');
}
</script>

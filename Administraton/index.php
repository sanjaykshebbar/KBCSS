<?php

// Start session
session_start();



// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = 'login.php';</script>";
    exit();
}

// Include the database connection
include './php/db_connection.php';

// Store login time in a variable
$loginTime = date('Y-m-d H:i:s');

// Set the admin email and construct the profile image path
$adminEmail = $_SESSION['email'];
$profileImagePath = "./Profile-Pic/Admin/" . strtolower($adminEmail) . "/" . strtolower($adminEmail) . ".jpg";

// Default profile image path
$defaultProfileImage = "./Assets/Images/placeholder.png";

// Check if the folder and image file exist
if (file_exists($profileImagePath)) {
    $profileImage = $profileImagePath;
} else {
    $profileImage = $defaultProfileImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css?v=1.0"> <!-- Custom styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between; /* Space between left and right sections */
            align-items: center; /* Vertically center the items */
            padding: 10px 20px;
            background-color: #343a40;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10; /* Ensure it stays above other elements */
            width: 100%;
            height: 100px; /* Fixed height for uniformity */
        }

        .header .welcome-message {
            display: flex;
            align-items: center; /* Vertically align image and text */
            gap: 15px; /* Add spacing between the image and text */
        }

        .header .welcome-message img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff; /* Optional border for better visibility */
        }

        .header .welcome-message h3 {
            font-size: 1.2rem;
            margin: 0; /* Remove extra spacing */
            color: white;
        }

        .header .welcome-message p {
            font-size: 0.9rem;
            margin: 0;
            color: #007bff; /* Accent color for login time */
        }

        .header .clock {
            font-size: 1rem;
            font-weight: bold;
            padding: 8px 15px;
            background-color: #007bff;
            border-radius: 5px;
            color: white;
            text-align: center;
            margin: 0 20px; /* Add spacing around the clock */
        }

        .logout-btn:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }
        /* New Layout for Full Width Sections */
        .main-content {
            display: flex;
            width: 100%;
            height: calc(100vh - 120px); /* Full height minus header and footer */
        }

        .left-container {
            width: 250px;
            background-color: #f4f4f4;
            padding: 20px;
            box-sizing: border-box;
            position: fixed;
            top: 100px;
            bottom: 0;
            left: 0;
            height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .right-container {
            margin-left: 250px; /* Leave space for the left sidebar */
            width: calc(100% - 250px);
            padding: 5px;
            box-sizing: border-box;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .button {
            display: block;
            margin: 10px 0;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-align: center;
            width: 100%;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        iframe {
            width: 100%;
            height: 90%;
            border: none;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #343a40;
            color: white;
            width: 100%;
            position: absolute;
            bottom: 0;
        }

        .welcome-message p {
            font-size: 1.2rem;
            color: #007bff;
        }

        .default-message {
            font-size: 1.5rem;
            color: #333;
            text-align: center;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .main-content {
                flex-direction: column;
                height: auto;
            }

            .left-container {
                position: static;
                width: 100%;
                height: auto;
            }

            .right-container {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <!-- Welcome message with profile picture -->
        <div class="welcome-message">
            <img src="<?php echo $profileImage; ?>" alt="Admin Profile Picture"> 
            <div>
                <h3>Welcome, <?php echo $_SESSION['email']; ?>!</h3>
                <p>Login Time: <?php echo $loginTime; ?></p>
            </div>
        </div>

        <!-- Clock -->
        <div class="clock" id="clock">Loading time...</div>

        <!-- Logout button -->
        <form method="POST" action="logout.php" style="margin: 0;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>


    <!-- Main Content Section -->
    <div class="main-content">
        <!-- Left Container for Buttons -->
        <div class="left-container">
            <button class="button" onclick="loadPage('templates/manage_users.php')">Manage Users</button>
            <button class="button" onclick="loadPage('templates/manage_roles.php')">Manage Roles</button>
            <button class="button" onclick="loadPage('templates/add_users.php')">Add Users</button>
            <button class="button" onclick="loadPage('templates/remove_users.php')">Remove Users</button>
            <button class="button" onclick="loadPage('manage_admin_profile.php')">Manage Profile</button>
            <button class="button" onclick="loadPage('login_logout_activity.php')">User Login Activity</button>
            <button class="button" onclick="loadPage('password-assist.php')">Password Assist</button>
            <button class="button" onclick="loadPage('./Tickets.php')">Ticketing</button>
        </div>

        <!-- Right Container for Default Message and Iframe Content -->
        <div class="right-container">
            <div id="default-message" class="default-message">
                <p>Welcome to the Admin Dashboard! Choose an option from the menu to manage users, roles, and more.</p>
            </div>
            <iframe id="contentFrame" src=""></iframe>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Admin Dashboard | All rights reserved.</p>
    </div>

    <!-- JavaScript for Clock, Page Loading and Default Message -->
    <script>
        // Clock update functionality
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
        }

        // Load page into iframe
        function loadPage(url) {
            document.getElementById("default-message").style.display = 'none';  // Hide default message
            document.getElementById("contentFrame").style.display = 'block';     // Show iframe
            document.getElementById("contentFrame").src = url;
        }

        // Add event listener for iframe loading
        const contentFrame = document.getElementById("contentFrame");
        contentFrame.addEventListener("load", function () {
            console.log("Page loaded successfully.");
        });

        // Set interval for clock and initial update
        setInterval(updateClock, 1000);
        updateClock(); // Initial call to display the time immediately

        // Session timeout after 10 minutes
        setTimeout(function () {
            alert('Session timed out. Please log in again.');
            window.location.href = 'logout.php';
        }, 10 * 60 * 1000); // 10 minutes in milliseconds
    </script>
</body>
</html>
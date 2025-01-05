<?php
// Start session
session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}


// Include the database connection
include './php/db_connection.php';



// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

// Store login time in a variable
$loginTime = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Custom styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #007bff;
            color: white;
        }
        .header .welcome-message {
            flex-grow: 1;
            text-align: left;
        }
        .header .clock {
            font-size: 20px;
            font-weight: bold;
            padding: 10px;
            background-color: #343a40;
            border-radius: 5px;
            color: #fff;
            text-align: center;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 20px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .container {
            margin-top: 40px;
        }
        .container h1 {
            color: #333;
            font-size: 2.5em;
            text-align: center;
        }
        .btn {
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            width: 220px;
            margin: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-primary:hover, .btn-secondary:hover, .btn-success:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Header with Welcome Message, Clock, and Logout Button -->
    <div class="header">
        <div class="welcome-message">
            <h3>Welcome, <?php echo $_SESSION['email']; ?>!</h3>
            <p>Login Time: <?php echo $loginTime; ?></p>
        </div>
        <div class="clock" id="clock">Loading time...</div>
        <form method="POST" action="logout.php">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h1>Empowering Your Control: Admin Dashboard</h1>
        <div class="d-flex justify-content-center gap-3">
            <a href="templates/manage_users.php" class="btn btn-primary">Manage Users</a>
            <a href="templates/manage_roles.php" class="btn btn-secondary">Manage Roles</a>
            <a href="templates/add_users.php" class="btn btn-success">Add Users</a>
            <a href="templates/remove_users.php" class="btn btn-success">Remove Users</a>
            <a href="login_logout_activity.php" class="btn btn-success">User Login Activity</a>
        </div>
    </div>

    <!-- JavaScript for Clock -->
    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Initial call to display the time immediately
    </script>
</body>
</html>

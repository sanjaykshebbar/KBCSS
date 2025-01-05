<?php
// Include the database connection
include './php/db_connection.php'; // Adjusted to reflect the correct path

// Start session
session_start();

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields!');</script>";
    } else {
        try {
            // Check user credentials from the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && md5($password) === $user['password']) {
                // Check if there is already an active session for this user
                $stmt = $pdo->prepare("SELECT * FROM login_activity WHERE email = ? AND logout_time IS NULL");
                $stmt->execute([$email]);
                $activeSession = $stmt->fetch();

                if ($activeSession) {
                    echo "<script>alert('You are already logged in!');</script>";
                } else {
                    // Store session data and login activity
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['userType'] = $user['userType'];
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['login_time'] = time(); // Store login time

                    // Record login activity in the login_activity table
                    $login_time = date("Y-m-d H:i:s");
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $stmt = $pdo->prepare("INSERT INTO login_activity (id, email, userType, login_time, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['id'], $_SESSION['email'], $_SESSION['userType'], $login_time, $ip_address, $user_agent]);

                    // Redirect to the Admin Dashboard or appropriate page
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                echo "<script>alert('Invalid credentials! Please try again.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    // Get logout time and calculate session duration
    $logout_time = date("Y-m-d H:i:s");
    $session_duration = time() - $_SESSION['login_time']; // Calculate the session duration in seconds

    // Update the logout time and session duration in the login_activity table
    $stmt = $pdo->prepare("UPDATE login_activity SET logout_time = ?, session_duration = ? WHERE id = ? AND logout_time IS NULL");
    $stmt->execute([$logout_time, $session_duration, $_SESSION['id']]);

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header('Location: login.php');
    exit();
}

// Fetch All Login Activity for Display
$stmt = $pdo->query("SELECT * FROM login_activity ORDER BY login_time DESC");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Activity</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Adjusted to reflect the correct path -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        max-width: 1200px;
        margin-top: 30px;
    }

    .filter-container {
        display: none;
        margin-top: 20px;
    }

    .filter-container input {
        margin-right: 10px;
        border-radius: 5px;
        padding: 8px;
    }

    .table {
        width: 100%;
        table-layout: fixed;
    }

    .table th, .table td {
        text-align: center;
        vertical-align: middle;
        padding: 12px 15px;
    }

    .table th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    .table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .table tr:hover {
        background-color: #e9ecef;
    }

    .table td {
        word-wrap: break-word;
        word-break: break-word;
    }

    /* Adjust the User Agent column width */
    .table td:nth-child(9), .table th:nth-child(9) {
        width: 250px; /* Increase width for User Agent column */
    }

    .filter-container input,
    .btn {
        border-radius: 5px;
    }

    .filter-toggle-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
        gap: 10px; /* Adds space between the text and toggle switch */
        padding: 10px;
        border: 2px solid #007bff; /* Border around the entire filter section */
        border-radius: 5px;
        background-color: #e9f7ff; /* Light background color for the filter section */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Adds subtle shadow for depth */
    }

    .filter-toggle-container label {
        font-size: 18px;
        font-weight: 500;
    }

    .filter-toggle-container .form-check {
        margin: 0;
    }

    .btn {
        padding: 10px 20px;
        font-size: 16px;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    #exportCSV {
        width: 200px;
    }
</style>



</head>
<body>

<?php if (!isset($_SESSION['email'])) { ?>
    <!-- Login Form -->
    <div class="container">
        <h2 class="text-center mb-4">Admin Login</h2>
        <form method="POST" action="login_logout_activity.php" class="border p-4 rounded shadow-sm">
            <div class="mb-3">
                <input type="email" name="email" placeholder="Email" class="form-control" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" placeholder="Password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
<?php } else { ?>
    <!-- Admin Dashboard -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Welcome, <?php echo $_SESSION['email']; ?>!</h1>
        <p class="text-center">You are logged in as an <?php echo $_SESSION['userType']; ?>.</p>

        <!-- Logout Button -->
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="index.php" class="btn btn-primary">Go-Back</a>
            <a href="login_logout_activity.php?logout=true" class="btn btn-danger">Logout</a>
        </div>

        <!-- Filter Toggle and Options -->
        <div class="filter-toggle-container">
            <label for="filterToggle" class="mr-2">Enable Filters</label>
            <div class="form-check form-switch d-inline-block">
                <input class="form-check-input" type="checkbox" id="filterToggle">
            </div>
        </div>

        <div id="filterContainer" class="filter-container">
            <div class="d-flex justify-content-center mt-3">
                <input type="text" id="filterEmail" placeholder="Filter by Email" class="form-control mr-2">
                <input type="text" id="filterUserType" placeholder="Filter by User Type" class="form-control mr-2">
                <input type="text" id="filterIP" placeholder="Filter by IP Address" class="form-control mr-2">
                <button id="applyFilters" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>

        <!-- Export to CSV Button -->
        <div class="d-flex justify-content-center mt-4">
            <button id="exportCSV" class="btn btn-success">Export to CSV</button>
        </div>

        <!-- Login Activity Table -->
        <h2 class="text-center mt-5">Login Activity</h2>
        <table class="table table-bordered mt-3" id="loginTable">
            <thead>
                <tr>
                    <th>Login ID</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Session Duration (seconds)</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity) { ?>
                    <tr>
                        <td><?php echo $activity['Login_id']; ?></td>
                        <td><?php echo $activity['id']; ?></td>
                        <td><?php echo $activity['email']; ?></td>
                        <td><?php echo $activity['userType']; ?></td>
                        <td><?php echo $activity['login_time']; ?></td>
                        <td><?php echo $activity['logout_time'] ? $activity['logout_time'] : 'Still Logged In'; ?></td>
                        <td><?php echo $activity['session_duration'] ? $activity['session_duration'] : 'Ongoing'; ?></td>
                        <td><?php echo $activity['ip_address']; ?></td>
                        <td><?php echo $activity['user_agent']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>

<script>
    // Get the filter toggle switch and filter container
    const filterToggle = document.getElementById('filterToggle');
    const filterContainer = document.getElementById('filterContainer');

    // Get filter input elements
    const filterEmail = document.getElementById('filterEmail');
    const filterUserType = document.getElementById('filterUserType');
    const filterIP = document.getElementById('filterIP');
    const applyFiltersButton = document.getElementById('applyFilters');

    // Get the table and rows
    const table = document.getElementById('loginTable');
    const tableRows = table.querySelectorAll('tbody tr');

    // Toggle filter container visibility
    filterToggle.addEventListener('change', function () {
        if (this.checked) {
            filterContainer.style.display = 'flex';
        } else {
            filterContainer.style.display = 'none';
            resetFilters(); // Reset filters if turned off
        }
    });

    // Apply filters
    applyFiltersButton.addEventListener('click', function () {
        filterTable();
    });

    // Function to filter the table
    function filterTable() {
        const email = filterEmail.value.toLowerCase();
        const userType = filterUserType.value.toLowerCase();
        const ip = filterIP.value.toLowerCase();

        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowEmail = cells[2].textContent.toLowerCase();
            const rowUserType = cells[3].textContent.toLowerCase();
            const rowIP = cells[7].textContent.toLowerCase();

            let match = true;
            
            if (email && !rowEmail.includes(email)) {
                match = false;
            }
            if (userType && !rowUserType.includes(userType)) {
                match = false;
            }
            if (ip && !rowIP.includes(ip)) {
                match = false;
            }

            // Show or hide row based on match
            if (match) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Function to reset filters
    function resetFilters() {
        filterEmail.value = '';
        filterUserType.value = '';
        filterIP.value = '';
        filterTable(); // Reapply filters after resetting
    }

    // Export table data to CSV
    document.getElementById('exportCSV').addEventListener('click', function () {
        let csvContent = "Login ID,User ID,Email,User Type,Login Time,Logout Time,Session Duration (seconds),IP Address,User Agent\n";

        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowData = Array.from(cells).map(cell => cell.textContent.trim());
            csvContent += rowData.join(",") + "\n";
        });

        // Create a downloadable CSV file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'login_activity.csv';
        link.click();
    });
</script>

</body>
</html>




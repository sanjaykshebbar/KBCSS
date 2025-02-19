<?php
session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

// Include the database connection
require_once '../php/db_connection.php';

// Query for total active counts of Admin, Faculty, Student
$activeCountsQuery = "SELECT 
                            SUM(userType = 'Admin' AND userState = 'Active') AS active_admin,
                            SUM(userType = 'Faculty' AND userState = 'Active') AS active_faculty,
                            SUM(userType = 'Student' AND userState = 'Active') AS active_student,
                            SUM(userState = 'Disabled') AS disabled_count,
                            SUM(userState = 'Inactive') AS inactive_count
                      FROM users";
$stmt = $pdo->prepare($activeCountsQuery);
$stmt->execute();
$counts = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch users and their roles from the database
$query = "SELECT id, username, userType, userState FROM users";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to update user role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['user_type'])) {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];

    // Prevent updating back to 'yet-to-confirm'
    if ($user_type === 'yet-to-confirm') {
        echo "<script>alert('You cannot update a user\'s role back to \"Yet to Confirm\".');</script>";
    } else {
        // Update user role
        try {
            $updateQuery = "UPDATE users SET userType = :user_type WHERE id = :user_id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->bindParam(':user_type', $user_type);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Display success message
            echo "<script>
                    alert('User role updated successfully!');
                    window.location.href = 'manage_roles.php';
                  </script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error updating user role: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Roles</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 36px;
            color: #007bff;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }

        .action-buttons {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .action-buttons a {
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #e9f7fa;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .summary div {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .summary div span {
            display: block;
            font-size: 24px;
            color: #007bff;
        }

        .filter-section {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .filter-section select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        th {
            background-color: #007bff;
            color: #fff;
            font-weight: 700;
        }

        td {
            background-color: #ffffff;
            color: #333;
        }

        button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .form-select {
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 150px;
            background-color: #fff;
            color: #333;
        }
    </style>
    <script>
        function filterTable() {
            const roleFilter = document.getElementById("roleFilter").value.toLowerCase();
            const statusFilter = document.getElementById("statusFilter").value.toLowerCase();
            const rows = document.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const role = row.cells[2].innerText.toLowerCase();
                const status = row.cells[3].innerText.toLowerCase();
                row.style.display =
                    (roleFilter === "all" || role === roleFilter) &&
                    (statusFilter === "all" || status === statusFilter)
                        ? ""
                        : "none";
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage User Roles</h1>

        <!-- Summary Section -->
        <div class="summary">
            <div>
                <strong>Total Active Admin</strong>
                <span><?php echo $counts['active_admin']; ?></span>
            </div>
            <div>
                <strong>Total Active Faculty</strong>
                <span><?php echo $counts['active_faculty']; ?></span>
            </div>
            <div>
                <strong>Total Active Student</strong>
                <span><?php echo $counts['active_student']; ?></span>
            </div>
            <div>
                <strong>Total Disabled Users</strong>
                <span><?php echo $counts['disabled_count']; ?></span>
            </div>
            <div>
                <strong>Total Inactive Users</strong>
                <span><?php echo $counts['inactive_count']; ?></span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <select id="roleFilter" onchange="filterTable()">
                <option value="all">All Roles</option>
                <option value="admin">Admin</option>
                <option value="faculty">Faculty</option>
                <option value="student">Student</option>
                <option value="yet-to-confirm">Yet to Confirm</option>
            </select>
            <select id="statusFilter" onchange="filterTable()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="disabled">Disabled</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <!-- Table to display users and their roles -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>User Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['userType']); ?></td>
                        <td><?php echo htmlspecialchars($user['userState']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="user_type" class="form-select" required>
                                    <option value="Student" <?php echo $user['userType'] == 'Student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="Admin" <?php echo $user['userType'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Faculty" <?php echo $user['userType'] == 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
                                    <option value="yet-to-confirm" <?php echo $user['userType'] == 'yet-to-confirm' ? 'selected' : ''; ?>>Yet to Confirm</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
                    
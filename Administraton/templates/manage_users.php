<?php
session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

require '../php/db_connection.php'; // Include database connection

// Fetch summary counts with COALESCE to ensure all roles are represented
$summaryQuery = "
    SELECT 
        userType, 
        COUNT(*) as count 
    FROM users 
    GROUP BY userType
";

// Initialize counts for roles
$roles = [
    'yet-to-confirm' => 0,
    'admin' => 0,
    'student' => 0,
    'Faculty' => 0,
];

$stmt = $pdo->prepare($summaryQuery);
$stmt->execute();
$roleSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Populate the roles array with the counts from the query
foreach ($roleSummary as $role) {
    $roles[$role['userType']] = (int) $role['count'];
}

// Fetch all users for the table
$query = "SELECT id, firstname, lastname, username, email, phone, userType, userState FROM users";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f4f7fc;
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
    }

    /* Header Summary Section */
    .header-summary {
        background-color: #343a40; /* Darker shade for admin professionalism */
        color: #ffffff;
        padding: 20px 30px;
        margin-bottom: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .header-summary h1 {
        font-size: 2rem;
        font-weight: 700;
        text-align: center;
        margin: 0;
    }

    /* Summary Cards */
    .summary-container {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 20px;
    }

    .summary-card {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
        flex: 1;
        max-width: 250px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        color: #495057; /* Professional text color */
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .summary-card h3 {
        font-size: 1.4rem;
        color: #007bff; /* Emphasized card title color */
        margin-bottom: 10px;
    }

    .summary-card p {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .highlight-purple {
        background-color: #e1bee7;
        color: #4a148c;
    }

    /* Table Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    thead {
        background-color: #007bff;
        color: #ffffff;
        text-transform: uppercase;
    }

    th, td {
        padding: 15px;
        text-align: center;
        font-size: 0.95rem;
    }

    th {
        font-weight: 600;
    }

    tbody tr {
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.3s ease;
    }

    tbody tr:hover {
        background-color: #f8f9fa;
    }

    tbody tr:nth-child(even) {
        background-color: #f4f6f9;
    }

    td {
        color: #495057;
    }

    /* Dropdown Button */
    .dropdown .btn {
        background-color: #007bff;
        color: white;
        font-size: 0.85rem;
        padding: 5px 10px;
        border-radius: 6px;
        border: none;
        transition: background-color 0.3s ease;
    }

    .dropdown .btn:hover {
        background-color: #0056b3;
    }

    .dropdown-menu {
        border-radius: 6px;
        border: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .dropdown-menu a {
        padding: 10px 15px;
        font-size: 0.85rem;
        text-align: left;
        color: #343a40;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dropdown-menu a:hover {
        background-color: #007bff;
        color: white;
    }

    /* Mobile Responsiveness */
    @media screen and (max-width: 768px) {
        .summary-container {
            flex-direction: column;
            align-items: center;
        }

        .summary-card {
            max-width: 100%;
        }

        table {
            font-size: 0.85rem;
        }
    }
</style>

</head>
<body>
    <div class="container">
        <!-- Header Summary -->
        <div class="header-summary">
            <h1 class="text-center">User Management Dashboard</h1>
            <div class="summary-container">
                <div class="summary-card <?= $roles['yet-to-confirm'] > 0 ? 'highlight-purple' : ''; ?>">
                    <h3>Unconfirmed</h3>
                    <p><?= $roles['yet-to-confirm']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Admins</h3>
                    <p><?= $roles['Admin']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Students</h3>
                    <p><?= $roles['Student']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Faculty</h3>
                    <p><?= $roles['Faculty']; ?></p>
                </div>
            </div>
        </div>

        <!-- User Table -->
        <h2 class="text-center mb-4">Manage Users</h2>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= htmlspecialchars($user['phone']); ?></td>
                        <td><?= htmlspecialchars($user['userType']); ?></td>
                        <td><?= htmlspecialchars($user['userState']); ?></td>
                        <td>
                            <form method="post" action="../php/update_user_status.php">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?= $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Change Status
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?= $user['id']; ?>">
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(<?= $user['id']; ?>, 'active')">Activate</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(<?= $user['id']; ?>, 'disabled')">Disable</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus(<?= $user['id']; ?>, 'inactive')">Deactivate</a></li>
                                    </ul>
                                </div>
                                <input type="hidden" name="action" id="action-<?= $user['id']; ?>">
                                <input type="submit" style="display: none;" id="submit-<?= $user['id']; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(userId, status) {
            document.getElementById(`action-${userId}`).value = status; // Set the action value
            document.getElementById(`submit-${userId}`).click(); // Trigger the form submission
        }
    </script>
</body>
</html>

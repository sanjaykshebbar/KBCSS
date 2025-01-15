<?php

session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

// Include the database connection file
require_once '../php/db_connection.php';

// Handle individual and bulk deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle individual deletion
    if (isset($_POST['delete_individual']) && !empty($_POST['delete_individual'])) {
        $id = $_POST['delete_individual'];
        $deleteSQL = "DELETE FROM users WHERE id = :id AND userType != 'Admin'";
        $stmt = $pdo->prepare($deleteSQL);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Handle selected deletion
    if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids']) && count($_POST['selected_ids']) > 0) {
        $ids = implode(",", array_map('intval', $_POST['selected_ids']));
        $deleteSQL = "DELETE FROM users WHERE id IN ($ids) AND userType != 'Admin'";
        $pdo->query($deleteSQL);
    }

    // Handle delete all
    if (isset($_POST['delete_all'])) {
        $deleteSQL = "DELETE FROM users WHERE userType IN ('Student', 'Faculty', 'yet-to-confirm')";
        $pdo->query($deleteSQL);
    }
}

// Fetch users for display
$fetchSQL = "SELECT * FROM users WHERE userType != 'Admin'";
$stmt = $pdo->query($fetchSQL);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Users</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            margin: 20px;
            color: #333;
        }

        .note {
            width: 80%;
            background: #ffeb3b;
            color: #333;
            padding: 15px;
            border: 1px solid #f0c14b;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .container {
            width: 80%;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background: #2196f3;
            color: #fff;
        }

        .btn {
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin-right: 10px;
        }

        .btn-primary {
            background: #2196f3;
        }

        .btn-danger {
            background: #f44336;
        }

        .btn:hover {
            opacity: 0.8;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <h2>Remove Users</h2>

    <!-- Note Section -->
    <div class="note">
        <strong>Note:</strong> Avoid deleting users whenever possible. Deleting a user removes the ID permanently, and it cannot be reused. 
        Instead, consider disabling the user by visiting the 
        <a href="manage_users.php" target="_blank">User Management Dashboard</a>.
    </div>

    <div class="container">
        <?php if (!empty($users)): ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>User Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_ids[]" value="<?= $user['id'] ?>"></td>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['firstName'] ?></td>
                            <td><?= $user['lastName'] ?></td>
                            <td><?= $user['username'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['phone'] ?></td>
                            <td><?= $user['userType'] ?></td>
                            <td>
                                <button type="submit" name="delete_individual" value="<?= $user['id'] ?>" class="btn btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="form-actions">
                <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected</button>
                <button type="submit" name="delete_all" class="btn btn-danger">Delete All</button>
            </div>
        </form>
        <?php else: ?>
            <p>No users available for deletion.</p>
        <?php endif; ?>
    </div>
    <script>
        // Select all checkboxes functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</body>
</html>

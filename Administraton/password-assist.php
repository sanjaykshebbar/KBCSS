<?php
// Start the session
session_start();

// Include the database connection
include './php/db_connection.php'; // Adjust path as needed

// Ensure only logged-in Admins can access
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Admin.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

// Get the admin's user ID and email from the session
$adminEmail = $_SESSION['email'];

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updatePassword'])) {
    $userId = $_POST['userId']; // Selected user ID
    $userEmail = $_POST['userEmail']; // Selected user email
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $reason = $_POST['reason']; // Admin-provided reason

    if (empty($newPassword) || empty($confirmPassword) || empty($reason) || empty($userId)) {
        echo "<script>alert('Please fill all fields!');</script>";
    } elseif ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
    } elseif (strlen($newPassword) < 6) {
        echo "<script>alert('Password must be at least 6 characters long!');</script>";
    } else {
        try {
            // MD5 hash the new password
            $hashedPassword = md5($newPassword);
    
            // Update the password for the selected user
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
    
            // Log the action in the people_action table
            $actionType = 'Password_Change';
            $actionTime = date('Y-m-d H:i:s');
    
            $logStmt = $pdo->prepare("INSERT INTO people_action (actioned_by, altered_to, actionType, notes, action_time) 
                                      VALUES (?, ?, ?, ?, ?)");
            $logStmt->execute([$adminEmail, $userEmail, $actionType, $reason, $actionTime]);
    
            echo "<script>alert('Password updated and action logged successfully!');</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
    
}

// Handle user filtering
$filterQuery = "SELECT u.id, u.email, u.userType, s.registerNumber, f.employeeID 
                FROM users u
                LEFT JOIN student_degree s ON u.id = s.user_id
                LEFT JOIN faculty_admin_info f ON u.id = f.user_id
                WHERE u.userType != 'admin'";

if (!empty($_GET['filterType'])) {
    $filterType = $_GET['filterType'];
    $filterQuery .= " AND u.userType = '$filterType'";
}

if (!empty($_GET['filterValue'])) {
    $filterValue = $_GET['filterValue'];
    $filterQuery .= " AND (s.registerNumber LIKE '%$filterValue%' OR f.employeeID LIKE '%$filterValue%')";
}

$stmt = $pdo->query($filterQuery);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Assistance - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Admin - Password Assistance</h2>

        <!-- Filter Section -->
        <form method="GET" action="password-assist.php" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="filterType" class="form-control">
                        <option value="">Filter by User Type</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="filterValue" class="form-control" placeholder="Registration or Employee ID">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <!-- Password Assistance Form -->
        <form method="POST" action="password-assist.php" class="border p-4 rounded shadow-sm">
            <div class="mb-3">
                <label for="userId" class="form-label">Select User</label>
                <select name="userId" id="userId" class="form-control" required>
                    <option value="">Select a User</option>
                    <?php foreach ($users as $user) { ?>
                        <option value="<?php echo $user['id']; ?>" data-email="<?php echo $user['email']; ?>">
                            <?php echo $user['email']; ?> 
                            (<?php echo $user['userType']; ?> - 
                            <?php echo $user['registerNumber'] ?? $user['employeeID']; ?>)
                        </option>
                    <?php } ?>
                </select>
                <input type="hidden" name="userEmail" id="userEmail">
            </div>
            <div class="mb-3">
    <label for="newPassword" class="form-label">New Password</label>
    <input type="password" name="newPassword" id="newPassword" class="form-control" minlength="6" required>
    <small class="form-text text-muted">Password must be at least 6 characters long.</small>
</div>
<div class="mb-3">
    <label for="confirmPassword" class="form-label">Confirm Password</label>
    <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" minlength="6" required>
</div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason for Password Change</label>
                <textarea name="reason" id="reason" class="form-control" required></textarea>
            </div>
            <button type="submit" name="updatePassword" class="btn btn-primary w-100">Update Password</button>
        </form>
    </div>

    <script>
        // Update the user email field dynamically
        document.getElementById('userId').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.querySelector('input[name="userEmail"]').value = selectedOption.getAttribute('data-email');
        });
    </script>
</body>
</html>

<?php
session_start();
require_once '../../Backend/connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: forgot-password.php");
    exit;
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Password match validation
    if ($newPassword === $confirmPassword) {
        // Password length validation (optional)
        if (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // MD5 hashing the password
            $hashedPassword = md5($newPassword);  // MD5 hash the new password

            // Update password query
            $query = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $hashedPassword, $email);

            if ($stmt->execute()) {
                // Delete the password reset entry from the database
                $deleteQuery = "DELETE FROM password_resets WHERE email = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param("s", $email);
                $deleteStmt->execute();

                // Destroy session after reset
                session_destroy();

                // Redirect with a success message
                header("Location: ../index.php?message=Password successfully reset. Please login.");
                exit;
            } else {
                $error = "Error updating password: " . $stmt->error;
            }
        }
    } else {
        $error = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Global Styles */
/* Global Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f7f7f7; /* Light, bright background */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: flex-start; /* Align items to the start (left) */
    align-items: center;
    height: 100vh;
    color: #333;
    padding-left: 20px; /* Add some padding for aesthetics */
}

/* Container for the form */
.container {
    background: linear-gradient(145deg, #ffffff, #e6e6e6); /* Subtle light gradient */
    padding: 40px;
    border-radius: 15px; /* Smooth rounded corners */
    box-shadow: 
        0 4px 10px rgba(0, 0, 0, 0.2), /* Darker main shadow */
        0 1px 20px rgba(0, 0, 0, 0.1); /* Soft secondary shadow */
    width: 100%;
    max-width: 600px;
    box-sizing: border-box;
    transition: transform 0.3s, box-shadow 0.3s;
}

.container:hover {
    transform: translateY(-5px); /* Slight hover effect */
    box-shadow: 
        0 6px 15px rgba(0, 0, 0, 0.25), /* Enhanced shadow on hover */
        0 2px 25px rgba(0, 0, 0, 0.15);
}

h2 {
    text-align: center;
    color: #4CAF50; /* Bright green color */
    margin-bottom: 20px;
}

.error {
    background-color: #f44336; /* Red for error */
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
    text-align: center;
}

label {
    font-size: 16px;
    margin-bottom: 8px;
    color: #555;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #4CAF50; /* Bright green */
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #45a049; /* Darker green on hover */
}

/* Make the form container responsive */
@media (max-width: 500px) {
    .container {
        padding: 20px;
    }
}


    </style>
</head>
<body style="background: url('../Assets/images/reset.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="container">
        <h2>Reset Password</h2>

        <!-- Display error message if any -->
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Reset Password Form -->
        <form method="POST">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>

</body>
</html>

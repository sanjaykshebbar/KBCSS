<?php
// Start the session and include the database connection
session_start();
require_once '../../Backend/connect.php';

// Get the email from the query string
$email = isset($_GET['email']) ? $_GET['email'] : null;

// If email is not provided, redirect back to forgot-password page
if (!$email) {
    header("Location: forgot-password.php");
    exit;
}

// Check for form submission (OTP verification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = $_POST['otp'];

    // Query to fetch OTP for the given email
    $query = "SELECT token, expires FROM password_resets WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $storedOtp = $row['token'];
        $expires = $row['expires'];

        // Validate OTP and expiration
        if ($storedOtp === $enteredOtp && strtotime($expires) > time()) {
            $_SESSION['email'] = $email; // Store email in session for next step
            header("Location: reset-password.php"); // Redirect to reset password page
            exit;
        } else {
            $error = "Invalid OTP or OTP has expired.";
        }
    } else {
        $error = "No OTP found for this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #2c3e50, #34495e, #2c3e50); /* Darker Gradient Background */
            color: #333;
        }

        /* Container for the form */
        .container {
            background-color: #1b1e23; /* Dark background with material feel */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3); /* Material Design Shadow */
            width: 200%;
            max-width: 600px;
            box-sizing: border-box;
            position: relative;
        }

        .container:hover {
            transform: translateY(-5px); /* Slight hover effect */
            box-shadow: 
                0 6px 15px rgba(0, 0, 0, 0.25), /* Enhanced shadow on hover */
                0 2px 25px rgba(0, 0, 0, 0.15);
        }
        
        .logo {
            position: absolute;
            top: -60px;
            left: -60px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: #f1c40f; /* Bright Yellow Color */
            margin-bottom: 20px;
        }

        .error {
            background-color: #e74c3c; /* Red for error */
            color: white;
            padding: 10px;
            border-radius: 15px;
            margin-left: 15px;
            display: inline-block;
            vertical-align: middle;
            font-size: 14px;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            color: #ddd;
        }

        input {
            width: calc(100% - 40px); /* Adjust width to leave space for the error */
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #444;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #27ae60; /* Bright green */
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2ecc71; /* Lighter green on hover */
        }

        /* Make the form container responsive */
        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }

            .error {
                margin-left: 0;
                display: block;
                margin-top: 5px;
            }

            input {
                width: 100%;
            }
        }

    </style>
</head>
<body style="background: url('../Assets/images/ForgotPassword.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="container">
        <img src="../Assets/icons/forgot-password.png" alt="Logo" class="logo">
        <h2>OTP Verification</h2>

        <!-- OTP Verification Form -->
        <form method="POST">
            <div style="display: flex; align-items: center;">
                <input type="text" name="otp" placeholder="Enter OTP" required>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>



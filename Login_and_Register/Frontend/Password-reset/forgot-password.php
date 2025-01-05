<?php
// Include database connection
include('../../Backend/connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if email exists in users table
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Insert OTP into password_resets table
        $query = "INSERT INTO password_resets (email, token, expires) 
                   VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $email, $otp, $expires);
        if ($stmt->execute()) {
            echo "OTP sent successfully!";
        } else {
            echo "Failed to store OTP.";
        }
        
        $stmt->close();

        // Send OTP via email
        require_once('../../Backend/mail_config.php');
        $mail = configureMailer();
        try {
            $mail->addAddress($email);
            $mail->Subject = "Password Reset OTP";
            $mail->Body = "Your OTP is: $otp. This OTP will expire in 10 minutes.";
            $mail->send();
        } catch (Exception $e) {
            die("Failed to send email: " . $e->getMessage());
        }

        // Redirect to OTP verification page
        echo "<script>window.location.href = 'otp-verification.php?email=" . urlencode($email) . "';</script>";
    } else {
        echo "<p style='color: red;'>Email not found. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
            background: linear-gradient(to right, #f7f7f7, #cccccc, #999999); /* Gradient Background */
            color: #333;
        }

        /* Container for the form */
        .container {
        background: linear-gradient(145deg, #e6ccff, #d1b3ff); /* Light purple gradient */
        padding: 40px;
        border-radius: 15px; /* Slightly more rounded corners */
        box-shadow: 
        0 4px 10px rgba(0, 0, 0, 0.1), /* Subtle shadow */
        0 0 20px rgba(192, 128, 255, 0.4); /* Purple glow effect */
        width: 100%;
        max-width: 600px;
        box-sizing: border-box;
        position: relative;
}


        /* Logo on top left */
        .logo {
            position: absolute;
            top: -50px;
            left: -50px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #4CAF50; /* Bright green color */
            margin-bottom: 20px;
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

        /* Go Back button */
        .go-back-btn {
            background-color: #f44336; /* Red for Go Back button */
            width: auto;
            margin-top: 20px;
        }

        /* Make the form container responsive */
        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body style="background: url('../Assets/images/ForgotPassword.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="container">
        <!-- Logo Image -->
        <img src="../Assets/icons/forgot-password.png" alt="Logo" class="logo">

        <h2>Forgot Password</h2>
        <form method="post">
            <label for="email">Enter your email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Submit</button>
            <a href="../index.php">
                <button type="button" class="go-back-btn">Go Back</button>
            </a>
        </form>
    </div>
</body>
</html>



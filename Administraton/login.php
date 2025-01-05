<?php
session_start();
include './php/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields!');</script>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Compare the MD5 hash of the input password with the stored MD5 password
                if (md5($password) === $user['password']) {
                    if ($user['userType'] === 'Admin') {
                        // If the user is Admin, proceed with the login
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['userType'] = $user['userType'];
                        $_SESSION['id'] = $user['id'];
                        $_SESSION['login_time'] = time(); // Store login time

                        // Record login activity in the login_activity table
                        $login_time = date("Y-m-d H:i:s");
                        $ip_address = $_SERVER['REMOTE_ADDR']; // User's IP address
                        $user_agent = $_SERVER['HTTP_USER_AGENT']; // User's browser details

                        // Insert login activity into the database
                        $stmt = $pdo->prepare("INSERT INTO login_activity (id, email, userType, login_time, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$_SESSION['id'], $_SESSION['email'], $_SESSION['userType'], $login_time, $ip_address, $user_agent]);

                        // Redirect to the admin page
                        header('Location: index.php'); // Redirect to Admin Dashboard
                        exit();
                    } elseif ($user['userType'] === 'Student') {
                        // If the user is a Student, show an error message
                        echo "<script>alert('You are a student and you are not allowed for administration.');</script>";
                    } elseif ($user['userType'] === 'Faculty') {
                        // If the user is Faculty, show an error message
                        echo "<script>alert('Faculties should be logging into the BookManagement Portal.');</script>";
                    } else {
                        echo "<script>alert('Access denied! Invalid user type.');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid credentials! Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Invalid credentials! Please try again.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error connecting to database: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
        }
        .login-container input {
            margin-bottom: 15px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container input:focus {
            border-color: #007bff;
            outline: none;
        }
        .login-container button {
            padding: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .login-container p {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }
        .login-container p a {
            color: #007bff;
            text-decoration: none;
        }
        .login-container p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Forgot password? <a href="..\Login_and_Register\Frontend\Password-reset\forgot-password.php">Click here</a></p>
    </div>
</body>
</html>

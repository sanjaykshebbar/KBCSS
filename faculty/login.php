<?php
// Clear cache headers
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();


// If a session is active, destroy it and redirect to login
if (isset($_SESSION['email'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: login.php");
    exit();
}


include '../Administraton/php/db_connection.php';

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
                if (md5($password) === $user['password']) {
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['userType'] = $user['userType'];
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['login_time'] = time();

                    $login_time = date("Y-m-d H:i:s");
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];

                    $stmt = $pdo->prepare("INSERT INTO login_activity (id, email, userType, login_time, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['id'], $_SESSION['email'], $_SESSION['userType'], $login_time, $ip_address, $user_agent]);

                    header('Location: f-homepage.php');
                    exit();
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
    <title>Faculty Login</title>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --background-color: rgba(104, 168, 160, 0.69);
            --text-color: #333;
        }

        body {
            font-family: Arial, sans-serif;
            background-image: url("./Assets/Images/login-bg.png");
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: #fff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 3 4px 6px rgba(104, 32, 32, 0.1);
            width: 400px;
            text-align: center;
        }

        /* Faculty Icon Section */
        .admin-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .login-container h2 {
            margin-bottom: 1.5rem;
            color: var(--text-color);
            font-size: 1.8rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group input {
            padding: 0.8rem;
            padding-right: 2.5rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(100% - 4rem);
            margin: 0 auto;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .form-group .icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #888;
            cursor: pointer;
        }

        button {
            padding: 0.8rem;
            font-size: 1rem;
            color: #fff;
            background-color: var(--primary-color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: var(--secondary-color);
        }

        .login-container p {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #555;
        }

        .login-container p a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .login-container p a:hover {
            text-decoration: underline;
        }

        .back-button {
            background-color: #ccc;
            padding: 0.8rem;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 1rem;
            width: 100%;
        }

        .back-button:hover {
            background-color: #888;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 1.5rem;
                width: 90%;
            }
        }

        .admin-icon {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    display: flex;
    justify-content: center; /* Center the content horizontally */
    align-items: center;     /* Center the content vertically */
    width: 50%;             /* Ensure it takes up the full width */
    height: 90px;           /* Set the height to center vertically */
}

    </style>
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-icon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }
    </script>
</head>
<body>
    
    <div class="login-container">
        <!-- Faculty Icon Placeholder -->
        <div class="admin-icon">
        <img src="./Assets/Images/admin.png" alt="Faculty Icon" class="admin-icon">
        </div>
        <h2>Faculty Login</h2>

        

        <form method="POST" action="login.php">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" aria-label="Email" required>
                <span class="icon">📧</span>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" aria-label="Password" required>
                <span class="icon" id="toggle-icon" onclick="togglePasswordVisibility()">👁️</span>
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Forgot password? <a href="..\Login_and_Register\Frontend\Password-reset\forgot-password.php">Click here</a></p>
        <button class="back-button" onclick="window.location.href='../index.html'">Back</button>
    </div>
    <!-- Back Button -->

</body>
</html>

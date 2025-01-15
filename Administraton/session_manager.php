<?php
// Include database connection
include './php/db_connection.php'; // Adjust path as needed

// Start the session
session_start();

// Function to handle login
function handleLogin($user) {
    global $pdo;

    // Record login time in the login_activity table
    $login_time = date("Y-m-d H:i:s");
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Insert login activity
    $stmt = $pdo->prepare("INSERT INTO login_activity (id, email, userType, login_time, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user['id'], $user['email'], $user['userType'], $login_time, $ip_address, $user_agent]);

    // Store session variables
    $_SESSION['email'] = $user['email'];
    $_SESSION['userType'] = $user['userType'];
    $_SESSION['id'] = $user['id'];
    $_SESSION['login_time'] = time();
}

// Function to handle logout
function handleLogout() {
    global $pdo;

    if (isset($_SESSION['id'])) {
        // Get the current time for logout
        $logout_time = date("Y-m-d H:i:s");

        // Update logout time in the login_activity table
        $stmt = $pdo->prepare("UPDATE login_activity SET logout_time = ? WHERE id = ? AND logout_time IS NULL");
        $stmt->execute([$logout_time, $_SESSION['id']]);

        // Destroy the session
        session_destroy();
    }
}

// Call the logout function if logout is requested
if (isset($_GET['logout'])) {
    handleLogout();
    header("Location: login.php"); // Redirect after logout
    exit();
}

// Call the login function if login data is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields!');</script>";
    } else {
        try {
            // Fetch user from the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && md5($password) === $user['password']) {
                // Check if the user already has an active session
                $stmt = $pdo->prepare("SELECT * FROM login_activity WHERE email = ? AND logout_time IS NULL");
                $stmt->execute([$email]);
                $activeSession = $stmt->fetch();

                if ($activeSession) {
                    echo "<script>alert('You are already logged in!');</script>";
                } else {
                    handleLogin($user);
                    header('Location: dashboard.php'); // Redirect to dashboard or home page
                    exit();
                }
            } else {
                echo "<script>alert('Invalid credentials!');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

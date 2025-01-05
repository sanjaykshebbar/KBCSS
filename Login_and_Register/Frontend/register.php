<?php 

include '../Backend/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signIn'])) {
        // Handle Sign In
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query to check if the user exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Check if the account status is 'active'
            if ($row['userState'] == 'Active') {
                // Encrypt the entered password using MD5 and compare it with the stored password
                if (md5($password) === $row['password']) {
                    // Check if the userType is 'Student'
                    if ($row['userType'] === 'Student') {
                        // Start the session and store the email in session
                        session_start();
                        $_SESSION['email'] = $row['email']; 
                        $_SESSION['id'] = $row['id']; // Store user ID
                        $_SESSION['userType'] = $row['userType'];
                        $_SESSION['login_time'] = time(); // Record login timestamp

                        // Capture login details
                        $ipAddress = $_SERVER['REMOTE_ADDR'];
                        $userAgent = $_SERVER['HTTP_USER_AGENT'];
                        $loginTime = date("Y-m-d H:i:s");

                        $activity_sql = "
                            INSERT INTO login_activity (id, email, userType, login_time, ip_address, user_agent) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ";
                        $activity_stmt = $conn->prepare($activity_sql);
                        $activity_stmt->bind_param("isssss", $row['id'], $row['email'], $row['userType'], $loginTime, $ipAddress, $userAgent);
                        $activity_stmt->execute();

                        // Redirect to homepage on successful login
                        header("Location: homepage.php");
                        exit();
                    } else {
                        echo "<script>alert('Access Denied: Only Students are allowed to log in.'); window.location.href = 'index.php';</script>";
                    }
                } else {
                    echo "<script>alert('Incorrect email or password.'); window.location.href = 'index.php';</script>";
                }
            } else {
                echo "<script>alert('Your account is not active. Please contact support.'); window.location.href = 'index.php';</script>";
            }
        } else {
            echo "<script>alert('User not found. Incorrect email or password.'); window.location.href = 'index.php';</script>";
        }
    } elseif (isset($_POST['signUp'])) {
        // Handle Sign Up
        $firstName = $_POST['fName'] ?? '';
        $lastName = $_POST['lName'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($phone) || empty($password)) {
            die("<script>alert('All fields are required.'); window.history.back();</script>");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("<script>alert('Invalid email format.'); window.history.back();</script>");
        }

        // Check if email already exists
        $check_email_sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email_sql);
        if (!$stmt) {
            die("Error preparing SELECT statement: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            die("<script>alert('Email already exists.'); window.history.back();</script>");
        }

        // Insert the new user into the database
        $hashed_password = md5($password); // Use MD5 for hashing
        $userType = 'yet-to-confirm';      // Default user type
        $userState = 'registered';         // Default user state

        $insert_sql = "INSERT INTO users (firstName, lastName, username, email, phone, password, userType, userState) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);

        if (!$stmt) {
            die("Error preparing INSERT statement: " . $conn->error);
        }

        // Bind the parameters
        $stmt->bind_param("ssssssss", $firstName, $lastName, $username, $email, $phone, $hashed_password, $userType, $userState);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!')</script>";
            header("Location: index.php"); // Redirect to index.php
            exit();
        } else {
            die("Error executing INSERT query: " . $stmt->error);
        }
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_start();
    $userId = $_SESSION['id'];
    $logoutTime = date("Y-m-d H:i:s");
    $sessionDuration = time() - $_SESSION['login_time'];

    $logout_sql = "
        UPDATE login_activity 
        SET logout_time = ?, session_duration = ? 
        WHERE id = ? AND logout_time IS NULL
    ";
    $logout_stmt = $conn->prepare($logout_sql);
    $logout_stmt->bind_param("sii", $logoutTime, $sessionDuration, $userId);
    $logout_stmt->execute();

    session_destroy();
    header("Location: index.php");
    exit();
}
?>

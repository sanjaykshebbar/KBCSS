<?php
// Include the database connection
include './php/db_connection.php';


// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashedPassword = md5($password); // Hash the password using MD5

    try {
        // Query to fetch user details
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Check if a user with the given email exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password (using MD5 hash)
            if ($hashedPassword === $user['password']) {
                // Check the user's state
                if ($user['userState'] !== 'Active') {
                    echo "<script>alert('Your account is {$user['userState']}. Please contact the administrator.');</script>";
                    exit();
                }

                // Redirect based on user type
                if ($user['userType'] === 'Admin') {
                    // Set session variables for the admin user
                    $_SESSION['email'] = $email;
                    $_SESSION['userType'] = $user['userType'];

                    // Redirect to the admin dashboard
                    header("Location: ./Administraton/index.php");
                    exit();
                } elseif ($user['userType'] === 'Student') {
                    echo "<script>alert('Welcome Student! Redirecting you to your dashboard.');</script>";
                    // Redirect to student dashboard (update the path as needed)
                    header("Location: ./StudentDashboard/index.php");
                    exit();
                } elseif ($user['userType'] === 'Faculty') {
                    echo "<script>alert('Welcome Faculty! Redirecting you to your dashboard.');</script>";
                    // Redirect to faculty dashboard (update the path as needed)
                    header("Location: ./FacultyDashboard/index.php");
                    exit();
                } else {
                    echo "<script>alert('Invalid user type. Access denied.');</script>";
                }
            } else {
                echo "<script>alert('Invalid password.');</script>";
            }
        } else {
            echo "<script>alert('No user found with the provided email address.');</script>";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Login</h1>
        <form method="POST" action="" class="mt-4">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>

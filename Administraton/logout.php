<?php

session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header('Location: login.php'); // Redirect to login page
exit();
?>

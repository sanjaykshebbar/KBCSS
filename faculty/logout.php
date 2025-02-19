<?php

session_start();

// Check if the user is logged in, is an Faculty, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Faculty') {
    echo "<script>alert('Unauthorized access. Please log in as an Faculty.'); 
    window.location.href = 'login.php';</script>";
    exit();
}

session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header('Location: login.php'); // Redirect to login page
exit();
?>

<?php
function checkFacultySession() {
    session_start();

    // Check if the user is logged in and has the Faculty role
    if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Faculty') {
        // Redirect to login page if not a Faculty
        header('Location: ../Login_and_Register/login.php');
        exit();
    }
}
?>

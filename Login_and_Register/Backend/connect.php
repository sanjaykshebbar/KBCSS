<?php
$servername = "localhost";  // Or your database server
$username = "kbcss_adm";         // Your database username
$password = "W1nd0vv$";             // Your database password
$dbname = "kbcss_users";   // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

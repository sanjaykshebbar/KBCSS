<?php
// Start session to access session variables
session_start();

// Include the database connection file
include './php/db_connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input values from the form
    $actionedBy = $_SESSION['email']; // Admin's email ID from the session
    $alteredFor = $_POST['altered_for']; // Email ID of the user for whom the action is taken
    $actionType = $_POST['action_type']; // Selected action type
    $notes = $_POST['notes']; // Admin remarks
    $actionTime = date('Y-m-d H:i:s'); // Current timestamp

    // Validate input fields
    if (!empty($actionedBy) && !empty($alteredFor) && !empty($actionType) && !empty($notes)) {
        // Insert data into the people_action table
        $query = "INSERT INTO people_action (actioned_by, altered_to, actionType, notes, action_time) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param('sssss', $actionedBy, $alteredFor, $actionType, $notes, $actionTime);
            if ($stmt->execute()) {
                echo "<script>alert('Action recorded successfully!'); window.location.href = 'admin_dashboard.php';</script>";
            } else {
                echo "<script>alert('Failed to record the action. Please try again.'); window.history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Failed to prepare the SQL query. Please contact support.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('All fields are required. Please fill out the form completely.'); window.history.back();</script>";
    }
}
?>

<?php

session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

// Include database connection
require_once '../php/db_connection.php';

// Check if the user is updating a role
if (isset($_POST['user_id']) && isset($_POST['user_type'])) {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];

    // Prevent updating back to 'yet-to-confirm' if the user is already confirmed
    if ($user_type === 'yet-to-confirm') {
        echo json_encode(['status' => 'error', 'message' => "You cannot update a user's role back to 'Yet to Confirm'."]);
        exit;
    }

    // Update user role in the database
    try {
        $stmt = $pdo->prepare("UPDATE users SET userType = :user_type WHERE id = :user_id");
        $stmt->bindParam(':user_type', $user_type);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => "User role updated successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => "Error updating user role: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => "Invalid request."]);
}
?>

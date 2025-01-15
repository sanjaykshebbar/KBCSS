<?php
session_start();
require '../../Administraton/php/db_connection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $userId = $_POST['user_id'];
    $action = $_POST['action'];
    $reason = trim($_POST['reason']);
    $actionedBy = $_SESSION['userID']; // Assuming user ID is stored in the session

    // Validate inputs
    if (empty($userId) || empty($action) || empty($reason)) {
        echo "<script>alert('Invalid input. All fields are required.'); window.history.back();</script>";
        exit();
    }

    try {
        // Update user state
        $updateQuery = "UPDATE users SET userState = :action WHERE id = :userId";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute(['action' => $action, 'userId' => $userId]);

        // Log the action in the people_action table
        $logQuery = "
            INSERT INTO people_action (actioned_by, altered_for, actionType, notes) 
            VALUES (:actionedBy, :alteredFor, :actionType, :notes)
        ";
        $stmt = $pdo->prepare($logQuery);
        $stmt->execute([
            'actionedBy' => $actionedBy,
            'alteredFor' => $userId,
            'actionType' => $action,
            'notes' => $reason,
        ]);

        echo "<script>alert('User status updated successfully.'); window.location.href = '../pages/manage_users.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('An error occurred: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>

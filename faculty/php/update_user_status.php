<?php

session_start();

// Check if the user is logged in, is an Faculty, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Faculty') {
    echo "<script>alert('Unauthorized access. Please log in as an Faculty.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

require '../../Administraton/php/db_connection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($userId && $action) {
        try {
            // Determine the new state
            switch ($action) {
                case 'active':
                    $newState = 'active';
                    break;
                case 'disabled':
                    $newState = 'disabled';
                    break;
                case 'inactive':
                    $newState = 'inactive';
                    break;
                default:
                    die("Invalid action.");
            }

            // Update the user state in the database
            $query = "UPDATE users SET userState = :newState WHERE id = :userId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':newState', $newState);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // Redirect back to the user management page
            header("Location: ../templates/manage_users.php");
            exit;
        } catch (PDOException $e) {
            die("Error updating user state: " . $e->getMessage());
        }
    } else {
        die("Invalid data received.");
    }
} else {
    die("Invalid request method.");
}

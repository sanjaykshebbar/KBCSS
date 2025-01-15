<?php
// Include database connection
include '../../Administraton/php/db_connection.php'; // Adjust path as needed

// Start the session
session_start();

// Check if session ID exists
if (isset($_SESSION['id'])) {
    // Get the current time for logout
    $logout_time = date("Y-m-d H:i:s");

    try {
        // Fetch the login_time from the login_activity table
        $stmt = $pdo->prepare("SELECT login_time FROM login_activity WHERE id = ? AND logout_time IS NULL");
        $stmt->execute([$_SESSION['id']]);
        $login_record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($login_record) {
            // Calculate the session duration
            $login_time = $login_record['login_time'];

            // Convert the login and logout times to DateTime objects for comparison
            $login_time_obj = new DateTime($login_time);
            $logout_time_obj = new DateTime($logout_time);

            // Calculate the difference (duration) between login and logout times
            $session_duration = $login_time_obj->diff($logout_time_obj);

            // Convert session duration to seconds
            $total_seconds = ($session_duration->h * 3600) + ($session_duration->i * 60) + $session_duration->s;

            // Format the session duration with units
            if ($total_seconds < 60) {
                $formatted_duration = '0. minutes'; // Less than a minute, displayed as 0. minutes
            } else {
                $total_minutes = floor($total_seconds / 60);
                $formatted_duration = $total_minutes . ' minutes';
            }

            // Update the logout time and session_duration in the login_activity table
            $stmt = $pdo->prepare("UPDATE login_activity SET logout_time = ?, session_duration = ? WHERE id = ? AND logout_time IS NULL");
            $stmt->execute([$logout_time, $formatted_duration, $_SESSION['id']]);

            // Store the logout time and session duration in session variables for later use
            $_SESSION['logout_time'] = $logout_time;
            $_SESSION['session_duration'] = $formatted_duration;
        } else {
            echo "<script>alert('No active session found.');</script>";
        }
    } catch (PDOException $e) {
        // Handle the error if any issue occurs
        echo "<script>alert('Error updating logout time: " . addslashes($e->getMessage()) . "');</script>";
    }

    // Destroy the session after updating the logout time
    session_destroy();
}

// Display a popup with the logout time and session duration, then redirect
if (isset($_SESSION['logout_time']) && isset($_SESSION['session_duration'])) {
    echo "<script>
            alert('Your logout time is " . $_SESSION['logout_time'] . " and your session duration was " . $_SESSION['session_duration'] . "');
            window.location.href = 'index.php'; // Redirect after popup
          </script>";
}
exit();
?>

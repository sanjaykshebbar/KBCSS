<?php
session_start();

// Ensure the faculty is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Faculty') {
    echo '<p>Access denied. Only faculty can answer questions.</p>';
    exit;
}

// Include database connection
include('../Login_and_Register/Backend/connect.php');

// Fetch the posted data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slno = $_POST['slno'];
    $answer = $_POST['answer'];
    $faculty_email = $_SESSION['username'];  // Assuming the faculty's email is stored in session as 'username'

    // Update the question with the answer
    $sql = "UPDATE `q&a` 
            SET `Answer` = ?, `A-Answered-by` = ? 
            WHERE `SLNO` = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $answer, $faculty_email, $slno);

    if ($stmt->execute()) {
        echo '<script>
                alert("Answered and the record updated.");
                window.location.href = "question-answers.php";  // Redirect to the question list page
              </script>';
    } else {
        echo 'Error updating record: ' . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

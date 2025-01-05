<?php
// check_email.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];

// Database connection
$conn = new mysqli('loalhost', 'kbcss_adm', 'W1nd0vv$', 'kbcss_users');
if ($conn->connect_error) {
    die(json_encode(['exists' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to check if email exists
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}

$stmt->close();
$conn->close();
?>

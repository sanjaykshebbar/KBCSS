<?php
include('../Login_and_Register/Backend/connect.php');

$lastUpdateTime = isset($_GET['lastUpdateTime']) ? $_GET['lastUpdateTime'] : '1970-01-01 00:00:00';

// Query to check for updates after the last timestamp
$sql = "SELECT MAX(`logged-Time`) AS lastUpdateTime FROM `q&a` WHERE `A-Answered-by` IS NOT NULL AND `logged-Time` > ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $lastUpdateTime);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$newDataAvailable = $row['lastUpdateTime'] > $lastUpdateTime;

echo json_encode([
    'newDataAvailable' => $newDataAvailable,
    'lastUpdateTime' => $row['lastUpdateTime']
]);

$stmt->close();
$conn->close();
?>

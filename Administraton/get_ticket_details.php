<?php
include('../Login_and_Register/Backend/connect.php');

if (isset($_GET['ticket_id'])) {
    $ticketId = $_GET['ticket_id'];
    $sql = "SELECT * FROM Tickets WHERE SLNO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ticket = $result->fetch_assoc();
        echo json_encode($ticket);
    } else {
        echo json_encode(['error' => 'Ticket not found']);
    }
}

$conn->close();
?>

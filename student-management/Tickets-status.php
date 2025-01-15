<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            text-align: center;
            color: #333;
            margin: 20px 0;
        }

        .ticket-container {
            width: 80%;
            max-width: 1000px;
            margin: 20px 0;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .ticket {
            margin-bottom: 15px;
        }

        .ticket p {
            margin: 5px 0;
        }

        button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Popup modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php
session_start();

// Ensure session variables are set before checking
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Student') {
    echo '<p>Access denied. Only students can view tickets.</p>';
    exit;
}

// Include database connection
include('../Login_and_Register/Backend/connect.php');

// Fetch tickets raised by the logged-in student
$sql = "SELECT * FROM Tickets WHERE Requestor = ? ORDER BY SLNO DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

$stmt->close();
$conn->close();
?>

    <h1>My Tickets</h1>
    <div class="ticket-container">
        <?php if (!empty($tickets)): ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket">
                    <p><strong>Ticket ID:</strong> <?= htmlspecialchars($ticket['Ticket_Number']) ?></p>
                    <p><strong>Subject:</strong> <?= htmlspecialchars(substr($ticket['Ticket_Subject'], 0, 20)) ?>...</p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($ticket['Status']) ?></p>
                    <button onclick="openModal(<?= htmlspecialchars(json_encode($ticket)) ?>)">View Ticket</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tickets raised yet.</p>
        <?php endif; ?>
    </div>

    <!-- Modal to view detailed ticket -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Ticket Details</h2>
            <div id="ticketDetails"></div>
        </div>
    </div>

    <script>
        function openModal(ticket) {
            const modal = document.getElementById('ticketModal');
            const ticketDetails = document.getElementById('ticketDetails');

            // Display ticket details
            ticketDetails.innerHTML = `
                <p><strong>Ticket ID:</strong> ${ticket.Ticket_Number}</p>
                <p><strong>Subject:</strong> ${ticket.Ticket_Subject}</p>
                <p><strong>Description:</strong> ${ticket.Ticket_Description}</p>
                <p><strong>Status:</strong> ${ticket.Status}</p>
                <p><strong>Assigned To:</strong> ${ticket.Assigned}</p>
                <p><strong>Comments:</strong> ${ticket.Comments || 'No comments yet'}</p>
                <p><strong>Ticket Logged Time:</strong> ${ticket.Ticket_logged_time}</p>
                ${ticket.Status === 'Closed' ? `<p><strong>Ticket Closed Time:</strong> ${ticket.Closed_Time}</p>` : ''}
                <p><strong>Total Time:</strong> ${ticket.Total_Time} seconds</p>
            `;
            
            modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('ticketModal');
            modal.style.display = 'none';
        }
    </script>
</body>
</html>

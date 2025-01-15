<?php
session_start();
include('../Login_and_Register/Backend/connect.php');

// Check if the user is an Admin
if ($_SESSION['userType'] !== 'Admin') {
    echo '<p>Access denied. Only admins can view and act on tickets.</p>';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $ticketId = $_POST['ticket_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $assignedTo = $_POST['assigned_to'] ?? 'un-assigned';
    $comment = $_POST['comment'] ?? '';

    if (!empty($ticketId) && !empty($status)) {
        // Update ticket details in the database
        $updateSql = "UPDATE Tickets 
                      SET Status = ?, Assigned = ?, Comments = CONCAT(IFNULL(Comments, ''), '\n', ?) 
                      WHERE SLNO = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssi", $status, $assignedTo, $comment, $ticketId);

        if ($stmt->execute()) {
            echo "<script>alert('Ticket updated successfully.');</script>";
        } else {
            echo "<script>alert('Failed to update ticket. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Status and Ticket ID are required.');</script>";
    }
}

// Fetch tickets where status is 'Open' or 'Pending-for-action' to show to the Admin
$sql = "SELECT * FROM Tickets WHERE Status IN ('Open', 'Pending-for-action') ORDER BY Ticket_logged_time DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$tickets = $stmt->get_result();

// Fetch Admin users for assignment dropdown
$adminSql = "SELECT id, email FROM users WHERE userType = 'Admin'";
$adminStmt = $conn->prepare($adminSql);
$adminStmt->execute();
$admins = $adminStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        h1, h2 {
            text-align: center;
        }
        .ticket-list {
            width: 90%;
            margin: 20px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f4f4f4;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        button {
            padding: 8px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }
        .modal-content {
            margin: 10% auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 60%;
            position: relative;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            resize: vertical;
        }
        .modal-footer {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Manage Tickets</h1>
    <div class="ticket-list">
        <h2>Tickets to Manage</h2>
        <?php if ($tickets->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Ticket Number</th>
                        <th>Subject</th>
                        <th>Requestor</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ticket = $tickets->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($ticket['Ticket_Number']) ?></td>
                            <td><?= htmlspecialchars($ticket['Ticket_Subject']) ?></td>
                            <td><?= htmlspecialchars($ticket['Requestor']) ?></td>
                            <td><?= htmlspecialchars($ticket['Status']) ?></td>
                            <td>
                                <button class="view-ticket-btn" data-ticket-id="<?= $ticket['SLNO'] ?>">View Ticket</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tickets available for action.</p>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ticket Details</h2>
            <form method="POST" action="manage_tickets.php">
                <input type="hidden" name="ticket_id" id="ticket_id">
                <label for="status">Status:</label>
                <select name="status" id="status" required>
                    <option value="">Select a status</option>
                    <option value="Open">Open</option>
                    <option value="Pending-for-action">Pending-for-action</option>
                    <option value="Pending-for-Approval">Pending-for-Approval</option>
                    <option value="Closed">Closed</option>
                </select>
                <label for="assigned_to">Assigned to:</label>
                <select name="assigned_to" id="assigned_to">
                    <option value="un-assigned">Un-assigned</option>
                    <?php while ($admin = $admins->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($admin['email']) ?>"><?= htmlspecialchars($admin['email']) ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="comment">Comments:</label>
                <textarea name="comment" id="comment"></textarea>
                <div class="modal-footer">
                    <button type="submit" name="submit_ticket">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll(".view-ticket-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                const ticketId = this.getAttribute("data-ticket-id");
                document.getElementById("ticket_id").value = ticketId;
                document.getElementById("ticketModal").style.display = "block";
            });
        });

        document.querySelector(".close").onclick = function () {
            document.getElementById("ticketModal").style.display = "none";
        };
    </script>
</body>
</html>

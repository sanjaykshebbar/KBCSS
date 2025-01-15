<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Ticket</title>
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

        .container {
            display: flex;
            flex-direction: column;
            width: 80%;
            max-width: 1000px;
            gap: 20px;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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

        .greyed-out {
            background-color: #f1f1f1;
            color: #777;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
<?php
session_start();

// Ensure session variables are set before checking
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Student') {
    echo '<p>Access denied. Only students can submit tickets.</p>';
    exit;
}

// Include database connection
include('../Login_and_Register/Backend/connect.php');

// Handle ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ticket_subject']) && !empty($_POST['ticket_description'])) {
    $userId = $_SESSION['id'];
    $email = $_SESSION['email'];
    $ticketSubject = $_POST['ticket_subject'];
    $ticketDescription = $_POST['ticket_description'];

    // Generate ticket number
    $ticketNumber = 'INC' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

    // Insert the new ticket into the Tickets table
    $sql = "INSERT INTO Tickets (Ticket_Number, Requestor, Status, Ticket_Subject, Ticket_Description) VALUES (?, ?, 'Open', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $ticketNumber, $email, $ticketSubject, $ticketDescription);

    if ($stmt->execute()) {
        echo '<script type="text/javascript">
                alert("Ticket submitted successfully!");
                window.location.href = window.location.href;
              </script>';
    } else {
        echo '<p>Error submitting ticket: ' . htmlspecialchars($conn->error) . '</p>';
    }

    $stmt->close();
}

$conn->close();
?>

    <h1>Submit a Ticket</h1>
    <div class="container">
        <form method="POST">
            <label for="email">Email ID:</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" class="greyed-out" readonly>

            <label for="ticket_subject">Subject (max 150 characters):</label>
            <input type="text" id="ticket_subject" name="ticket_subject" maxlength="150" required>

            <label for="ticket_description">Description (max 400 characters):</label>
            <textarea id="ticket_description" name="ticket_description" rows="6" maxlength="400" required></textarea>

            <button type="submit">Submit Ticket</button>
        </form>
    </div>
</body>
</html>

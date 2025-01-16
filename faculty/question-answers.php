<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['userType'])) {
    echo '<p>Access denied. Please log in.</p>';
    exit;
}

// Include database connection
include('../Login_and_Register/Backend/connect.php');

// Handle form submission to save answers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slno'])) {
    $slno = $_POST['slno'];
    $answer = $_POST['answer'];
    $faculty_email = $_SESSION['username']; // Assuming faculty email is stored in session

    $update_sql = "UPDATE `q&a` 
                   SET `Answer` = ?, `A-Answered-by` = ? 
                   WHERE `SLNO` = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $answer, $faculty_email, $slno);

    if ($stmt->execute()) {
        echo '<script>alert("Answer submitted successfully.");</script>';
    } else {
        echo '<script>alert("Failed to submit the answer.");</script>';
    }
    $stmt->close();
}

// Fetch all questions
$sql = "SELECT q.`SLNO`, q.`Q-Asked-by`, sd.registerNumber, q.Question, q.Answer, q.`A-Answered-by` 
        FROM `q&a` q 
        LEFT JOIN `student_degree` sd ON q.ID = sd.ID 
        ORDER BY q.`SLNO` ASC";
$result = $conn->query($sql);
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question and Answer Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        textarea {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .answer-form {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Question and Answer Management</h1>

    <table>
        <thead>
            <tr>
                <th>SLNO</th>
                <th>Raised By</th>
                <th>Register Number</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $q): ?>
                <tr>
                    <td><?= htmlspecialchars($q['SLNO']) ?></td>
                    <td><?= htmlspecialchars($q['Q-Asked-by']) ?></td>
                    <td><?= htmlspecialchars($q['registerNumber']) ?></td>
                    <td><?= htmlspecialchars($q['Question']) ?></td>
                    <td>
                        <?= $q['Answer'] ? htmlspecialchars($q['Answer']) : '<span style="color: gray;">Not answered yet</span>' ?>
                    </td>
                    <td>
                        <?php if ($_SESSION['userType'] === 'Faculty' && empty($q['Answer'])): ?>
                            <button onclick="toggleAnswerForm(<?= htmlspecialchars(json_encode($q)) ?>)">Answer</button>
                        <?php elseif ($_SESSION['userType'] !== 'Faculty'): ?>
                            <span>-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="answer-form" id="form-<?= $q['SLNO'] ?>">
                    <td colspan="6">
                        <form method="POST">
                            <textarea name="answer" placeholder="Write your answer here..." required></textarea>
                            <input type="hidden" name="slno" value="<?= $q['SLNO'] ?>">
                            <button type="submit">Submit</button>
                            <button type="button" onclick="toggleAnswerForm()">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function toggleAnswerForm(question = null) {
            // Hide all answer forms
            document.querySelectorAll('.answer-form').forEach(form => form.style.display = 'none');

            if (question) {
                // Show the specific form
                document.getElementById('form-' + question.SLNO).style.display = 'table-row';
            }
        }
    </script>
</body>
</html>

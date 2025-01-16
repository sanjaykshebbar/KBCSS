<?php

session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Student') {
    echo '<p>Access denied. Only students can ask questions.</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask a Question</title>
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
            width: 90%;
            max-width: 1200px;
            gap: 20px;
            align-items: flex-start;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1;
            height: 100%; /* Ensure the form stays at a fixed height */
        }


        #questions {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 2;
            max-height: 600px; /* Set a max height for the right container */
            overflow-y: auto; /* Enable vertical scrolling when content exceeds max height */
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        #otherQuestion {
            height: 50px;
            resize: none;
        }

        button {
            width: 100%;
            background-color: #007BFF;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
            color: red;
        }

        .question {
            color: #444; /* Softer shade */
            font-weight: bold;
            margin-bottom: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 50px;
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%; /* Wider popup */
            height: 80%; /* Fixed height */
            border-radius: 8px;
            overflow-y: auto;
        }

        .modal table {
            width: 100%;
            border-collapse: collapse;
        }

        .modal table th, 
        .modal table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .modal table th {
            background-color: #f4f4f4;
            font-weight: bold;
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

        #otherQuestion {
            display: none;
        }
    </style>
    <script>
        function openModal(questionId) {
            document.getElementById(`modal-${questionId}`).style.display = "block";
        }

        function closeModal(questionId) {
            document.getElementById(`modal-${questionId}`).style.display = "none";
        }

        function toggleOtherInput(select) {
            const otherInput = document.getElementById("otherQuestion");
            const finalInput = document.getElementById("finalQuestion");
            if (select.value === "Other") {
                otherInput.style.display = "block";
                otherInput.required = true;
            } else {
                otherInput.style.display = "none";
                otherInput.required = false;
                finalInput.value = select.value;
            }
        }

        function validateForm() {
            const select = document.getElementById("questionSelect");
            const otherInput = document.getElementById("otherQuestion");
            const finalInput = document.getElementById("finalQuestion");

            if (select.value === "Other") {
                finalInput.value = otherInput.value.trim();
            }

            if (!finalInput.value) {
                alert("Please select or type a question.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<?php

include('../Login_and_Register/Backend/connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_question']) && !empty($_POST['final_question'])) {
    $userId = $_SESSION['id'];
    $email = $_SESSION['email'];
    $question = $_POST['final_question'];
    $loggedTime = date('Y-m-d H:i:s');

    $sql = "SELECT COUNT(*) AS question_count FROM `q&a` WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $studentQuestionCount = $row['question_count'] + 1;

    $sql = "INSERT INTO `q&a` (ID, `Q-Asked-by`, Question, `logged-Time`, `SLNO-q-Student`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssi', $userId, $email, $question, $loggedTime, $studentQuestionCount);

    if ($stmt->execute()) {
        echo '<script>alert("Question submitted successfully!"); window.location.href = "student-question.php";</script>';
    } else {
        echo '<p>Error logging question: ' . $conn->error . '</p>';
    }

    $stmt->close();
}

$sql = "SELECT `SLNO-q-Student`, Question, Answer, `logged-Time`, `A-Answered-by` FROM `q&a` WHERE ID = ? ORDER BY `SLNO` DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

$stmt->close();
$conn->close();
?>

    <h1>Ask a Question</h1>
    <div class="container">
        <form method="POST" onsubmit="return validateForm();">
            <label for="questionSelect">Choose a question:</label>
            <select id="questionSelect" name="question" onchange="toggleOtherInput(this);">
                <option value="">--Select a question--</option>
                <option value="What is the syllabus for the upcoming test?">What is the syllabus for the upcoming test?</option>
                <option value="Can I get an extension on my assignment?">Can I get an extension on my assignment?</option>
                <option value="What is the class schedule for next week?">What is the class schedule for next week?</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" id="otherQuestion" placeholder="Type your question here" maxlength="150">
            <input type="hidden" id="finalQuestion" name="final_question">
            <button type="submit">Submit</button>
        </form>

        <div id="questions">
            <h2>My Questions</h2>
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $qa): ?>
                    <div>
                        <p class="question">Q<?= htmlspecialchars($qa['SLNO-q-Student']) ?>: <?= htmlspecialchars($qa['Question']) ?></p>
                        <button onclick="openModal(<?= htmlspecialchars($qa['SLNO-q-Student']) ?>)">View</button>
                        <div id="modal-<?= htmlspecialchars($qa['SLNO-q-Student']) ?>" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal(<?= htmlspecialchars($qa['SLNO-q-Student']) ?>)">&times;</span>
                                <h3>Question Details</h3>
                                <table>
                                    <tr>
                                        <th>Question</th>
                                        <td><?= htmlspecialchars($qa['Question']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Raised Date</th>
                                        <td><?= htmlspecialchars($qa['logged-Time']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Answered Status</th>
                                        <td><?= $qa['Answer'] ? 'Answered' : 'Pending' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Answered By</th>
                                        <td><?= htmlspecialchars($qa['A-Answered-by']) ?: 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Answer</th>
                                        <td><?= htmlspecialchars($qa['Answer']) ?: 'Not yet answered' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No questions asked yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

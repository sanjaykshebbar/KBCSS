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
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        #questions {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 2;
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
            color: red;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .answer {
            color: green;
            margin-bottom: 15px;
        }

        #otherQuestion {
            display: none;
        }
    </style>
    <script>
        function toggleOtherInput(select) {
            const otherInput = document.getElementById('otherQuestion');
            if (select.value === 'Other') {
                otherInput.style.display = 'block';
            } else {
                otherInput.style.display = 'none';
                otherInput.value = '';
            }
        }

        function validateForm() {
            const questionSelect = document.getElementById('questionSelect');
            const otherInput = document.getElementById('otherQuestion');
            const selectedValue = questionSelect.value;
            const question = selectedValue === 'Other' ? otherInput.value.trim() : selectedValue;

            if (question === '' || question.length > 150) {
                alert('Please provide a valid question (not exceeding 150 characters).');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<?php
session_start();

// Ensure session variables are set before checking
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Student') {
    echo '<p>Access denied. Only students can ask questions.</p>';
    exit;
}

// Include database connection
include('../Login_and_Register/Backend/connect.php');

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question']) && !empty($_POST['question'])) {
    $userId = $_SESSION['id'];  // Correct session variable
    $email = $_SESSION['email'];
    $question = $_POST['question'];
    $loggedTime = date('Y-m-d H:i:s');

    // Step 1: Get the current number of questions asked by this student (SLNO-q-Student)
    $sql = "SELECT COUNT(*) AS question_count FROM `q&a` WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $studentQuestionCount = $row['question_count'] + 1;  // Increment question count

    // Step 2: Insert the new question with SLNO-q-Student value
    $sql = "INSERT INTO `q&a` (ID, `Q-Asked-by`, Question, `logged-Time`, `SLNO-q-Student`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssi', $userId, $email, $question, $loggedTime, $studentQuestionCount);

    if ($stmt->execute()) {
        // Success message and page reload
        echo '<script type="text/javascript">
                alert("Question submitted successfully!");
                window.location.href = window.location.href;
              </script>';
    } else {
        echo '<p>Error logging question: ' . $conn->error . '</p>';
    }

    $stmt->close();
}

// Fetch questions asked by the logged-in student
$sql = "SELECT `SLNO-q-Student`, Question, Answer FROM `q&a` WHERE ID = ? ORDER BY `SLNO` DESC";
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
            <input type="text" id="otherQuestion" name="otherQuestion" placeholder="Type your question here" maxlength="150">
            <button type="submit">Submit</button>
        </form>

        <div id="questions">
    <h2>My Questions & Answers</h2>
    <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $qa): ?>
            <div>
                <p class="question">Q<?= htmlspecialchars($qa['SLNO-q-Student']) ?>: <?= htmlspecialchars($qa['Question']) ?></p>
                <p class="answer">A: <?= htmlspecialchars($qa['Answer']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No questions asked yet.</p>
    <?php endif; ?>
</div>

    </div>
</body>
</html>

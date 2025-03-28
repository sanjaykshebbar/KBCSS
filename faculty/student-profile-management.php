<?php
session_start();

// Include database connection file
include('../Login_and_Register/Backend/connect.php');

// Check if the user is logged in as faculty
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get faculty email from session
$faculty_email = $_SESSION['email'];

// Fetch student data and degree options
$query = "SELECT sd.id, u.email, u.firstName, u.lastName, sd.degreeType, sd.semester, sd.registerNumber
          FROM student_degree sd
          JOIN users u ON sd.user_id = u.id
          WHERE u.userType = 'Student'";
$students = mysqli_query($conn, $query);

// Fetch Degree Type options
$degree_query = "SELECT DISTINCT degreeType FROM available_degree";
$degree_result = mysqli_query($conn, $degree_query);

// Fetch Semester options
$semester_query = "SELECT DISTINCT semester FROM semester_info";
$semester_result = mysqli_query($conn, $semester_query);

// Handle form submission for updating student data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $degreeType = $_POST['degreeType'];
    $semester = $_POST['semester'];
    $registerNumber = $_POST['registerNumber'];
    $notes = $_POST['notes'];

    // Ensure remarks are provided before updating
    if (empty($notes)) {
        echo "<script>alert('Remarks are required for the update.');</script>";
    } else {
        // Get the current values from the database
        $current_data_query = "SELECT registerNumber, degreeType, semester FROM student_degree WHERE id = $student_id";
        $current_data_result = mysqli_query($conn, $current_data_query);
        $current_data = mysqli_fetch_assoc($current_data_result);

        $changes_made = false;

        // Validate and update Register Number if it has changed
        if ($registerNumber !== $current_data['registerNumber']) {
            $update_register_query = "UPDATE student_degree SET registerNumber = '$registerNumber' WHERE id = $student_id";
            if (mysqli_query($conn, $update_register_query)) {
                $changes_made = true;

                // Log action for Register Number
                $action_query = "INSERT INTO people_action (actioned_by, altered_for, actionType, notes, action_time) 
                                 VALUES ('$faculty_email', (SELECT email FROM users WHERE id = (SELECT user_id FROM student_degree WHERE id = $student_id)), 
                                 'RegisterNumber Update', '$notes', NOW())";
                mysqli_query($conn, $action_query);
            }
        }

        // Validate and update Degree Type if it has changed
        if ($degreeType !== $current_data['degreeType']) {
            $update_degree_query = "UPDATE student_degree SET degreeType = '$degreeType' WHERE id = $student_id";
            if (mysqli_query($conn, $update_degree_query)) {
                $changes_made = true;

                // Log action for Degree Type
                $action_query = "INSERT INTO people_action (actioned_by, altered_for, actionType, notes, action_time) 
                                 VALUES ('$faculty_email', (SELECT email FROM users WHERE id = (SELECT user_id FROM student_degree WHERE id = $student_id)), 
                                 'DegreeType Update', '$notes', NOW())";
                mysqli_query($conn, $action_query);
            }
        }

        // Validate and update Semester if it has changed
        if ($semester !== $current_data['semester']) {
            $update_semester_query = "UPDATE student_degree SET semester = '$semester' WHERE id = $student_id";
            if (mysqli_query($conn, $update_semester_query)) {
                $changes_made = true;

                // Log action for Semester
                $action_query = "INSERT INTO people_action (actioned_by, altered_for, actionType, notes, action_time) 
                                 VALUES ('$faculty_email', (SELECT email FROM users WHERE id = (SELECT user_id FROM student_degree WHERE id = $student_id)), 
                                 'Semester Update', '$notes', NOW())";
                mysqli_query($conn, $action_query);
            }
        }

        // Feedback to the user
        if ($changes_made) {
            echo "<script>alert('Alteration Successful!');</script>";
        } else {
            echo "<script>alert('No changes were made as the values are the same.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Profiles</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .container {
            width: 90%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            color: #555;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        button:hover {
            background-color:rgb(37, 156, 63);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            display: center;
            flex-direction: column;
            align-items: center; /* Center the content */
            padding: 30px;
            border-radius: 20px;
            box-shadow: 5px 4px 6px rgba(0, 0, 0, 0.1);
            width: 60%; /* Width of the modal */
            background-color: white;
        }

        .modal-content form {
            width: 100%; /* Ensure the form takes the full width */
        }

        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
            width: 90%; /* Ensure label takes the full width */
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin: 10px 0px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            height: 90px;
        }

        button[type="submit"] {
            width: 100%; /* Full width of the button */
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .notes-section textarea {
            height: 80px; /* Set height of the textarea */
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 20px;
            color: #aaa;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Manage Student Profiles</h1>
    <div class="container">
        <table>
            <tr>
                <th>Sl No</th>
                <th>Student Name</th>
                <th>Student Email</th>
                <th>Degree Type</th>
                <th>Semester</th>
                <th>Register Number</th>
                <th>Action</th>
            </tr>

            <?php while ($student = mysqli_fetch_assoc($students)) { ?>
                <tr>
                    <td><?php echo $student['id']; ?></td>
                    <td><?php echo $student['firstName'] . " " . $student['lastName']; ?></td>
                    <td><?php echo $student['email']; ?></td>
                    <td><?php echo $student['degreeType']; ?></td>
                    <td><?php echo $student['semester']; ?></td>
                    <td><?php echo $student['registerNumber']; ?></td>
                    <td>
                        <button type="button" onclick="openModal(<?php echo $student['id']; ?>, '<?php echo $student['email']; ?>', '<?php echo $student['degreeType']; ?>', '<?php echo $student['semester']; ?>', '<?php echo $student['registerNumber']; ?>')">Update</button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Modal Structure -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Update Student Profile</h2>
            <form method="POST" action="">
                <input type="hidden" id="student_id" name="student_id">
                <div id="studentInfo">
                    <!-- Dynamic content goes here -->
                </div>
                <div class="notes-section">
                    <label for="notes">Remarks:</label>
                    <textarea id="notes" name="notes" placeholder="Enter remarks here..."></textarea>
                </div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(studentId, studentEmail, degreeType, semester, registerNumber) {
            let degreeOptions = '';
            <?php while ($degree = mysqli_fetch_assoc($degree_result)) { ?>
                degreeOptions += `<option value='<?php echo $degree['degreeType']; ?>'><?php echo $degree['degreeType']; ?></option>`;
            <?php } ?>

            let semesterOptions = '';
            <?php while ($semester = mysqli_fetch_assoc($semester_result)) { ?>
                semesterOptions += `<option value='<?php echo $semester['semester']; ?>'><?php echo $semester['semester']; ?></option>`;
            <?php } ?>

            document.getElementById('student_id').value = studentId;
            document.getElementById('studentInfo').innerHTML = `
                <label>Register Number:</label>
                <input type="text" name="registerNumber" value="${registerNumber}"><br>

                <label>Degree Type:</label>
                <select name="degreeType">${degreeOptions}</select><br>

                <label>Semester:</label>
                <select name="semester">${semesterOptions}</select><br>
            `;

            document.getElementById('updateModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }
    </script>
</body>
</html>

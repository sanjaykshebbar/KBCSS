<?php

session_start();

// Check if the user is logged in, is an Admin, and is active
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Admin') {
    echo "<script>alert('Unauthorized access. Please log in as an Administrator.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

// Include the database connection file
require_once '../php/db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    // Hash the password using MD5
    $password = md5($_POST['password']); // MD5 hashing
    $userType = $_POST['userType'];
    $userState = $_POST['userState'];

    // Check if username, email, or phone already exists
    $sqlCheck = "SELECT * FROM users WHERE username = :username OR email = :email OR phone = :phone";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':username', $username);
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->bindParam(':phone', $phone);

    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        // Record exists, show error message
        $errorMessage = "Error: Username, Email, or Phone already exists.";
    } else {
        // Prepare SQL query for inserting new user
        $sql = "INSERT INTO users (firstName, lastName, username, email, phone, password, userType, userState) 
                VALUES (:firstName, :lastName, :username, :email, :phone, :password, :userType, :userState)";

        try {
            // Prepare statement
            $stmt = $pdo->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':firstName', $firstName);
            $stmt->bindParam(':lastName', $lastName);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':userType', $userType);
            $stmt->bindParam(':userState', $userState);

            // Execute the statement
            if ($stmt->execute()) {
                $successMessage = "User added successfully!";
            } else {
                $errorMessage = "Error: Could not add user.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #8e8e8e 20%, #f1f1f1 50%, #8e8e8e 80%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: 400% 400%;
            animation: metallic-shine 3s infinite linear;
        }

        @keyframes metallic-shine {
            0% {
                background-position: 0 0;
            }
            50% {
                background-position: 100% 100%;
            }
            100% {
                background-position: 0 0;
            }
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            font-weight: 500;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-weight: 500;
            color: #333;
        }

        input, select {
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #2196f3;
            outline: none;
        }

        input[type="submit"] {
            background-color: #2196f3;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border: none;
            padding: 12px;
            font-size: 18px;
        }

        input[type="submit"]:hover {
            background-color: #1976d2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .status-select, .form-group select {
            margin-top: 5px;
        }

        .error, .success {
            color: red;
            font-size: 14px;
            text-align: center;
        }

        .success {
            color: green;
        }

        .message {
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 15px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #2196f3;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1976d2;
        }

        .btn-danger {
            background-color: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add User</h2>
    <form method="POST" action="add_users.php">
        <?php if (!empty($successMessage)) { echo "<div class='message success'>$successMessage</div>"; } ?>
        <?php if (!empty($errorMessage)) { echo "<div class='message error'>$errorMessage</div>"; } ?>

        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" required placeholder="Enter first name">
        </div>

        <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName" required placeholder="Enter last name">
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter username">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="Enter email">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" required placeholder="Enter phone number">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter password">
        </div>

        <div class="form-group">
            <label for="userType">User Type</label>
            <select id="userType" name="userType">
                <option value="Student">Student</option>
                <option value="Admin">Admin</option>
                <option value="Faculty">Faculty</option>
                <option value="yet-to-confirm">Yet to Confirm</option>
            </select>
        </div>

        <div class="form-group">
            <label for="userState">User State</label>
            <select id="userState" name="userState">
                <option value="registered">Registered</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="disabled">Disabled</option>
            </select>
        </div>

        <input type="submit" value="Add User">
    </form>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="../index.php" class="btn btn-primary">Go-Back</a>
        <a href="../logout.php?logout=true" class="btn btn-danger">Logout</a>
    </div>

</div>

<?php if (!empty($errorMessage)) { ?>
    <script>
        alert("<?php echo $errorMessage; ?>");
        // Reset the form after showing the error message
        document.querySelector('form').reset();
    </script>
<?php } ?>

</body>
</html>

<?php
session_start();
include("../Login_and_Register/Backend/connect.php");

// Initialize success message flags in session if not set
if (!isset($_SESSION['profileUpdateMessage'])) {
    $_SESSION['profileUpdateMessage'] = "";
}
if (!isset($_SESSION['passwordChangeMessage'])) {
    $_SESSION['passwordChangeMessage'] = "";
}

// Check if the user is logged in and is a Student
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Student') {
    echo "<script>alert('Unauthorized access. Please log in as a Student.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

$email = $_SESSION['email'];
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$userData = mysqli_fetch_assoc($userQuery);

$degreeQuery = mysqli_query($conn, "SELECT * FROM Student_Degree WHERE user_id='" . $userData['id'] . "'");
$degreeData = mysqli_fetch_assoc($degreeQuery);

$profilePicPath = $degreeData['profilePicture'] ? $degreeData['profilePicture'] : "../assets/placeholder.png";

// Handle Profile Picture Upload
if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "./Profile-Pic/Student/" . $email . "/";
    $uploadFile = $uploadDir . $email . ".jpg"; // Same filename for overwriting

    // File details
    $fileTmpName = $_FILES['profilePicture']['tmp_name'];
    $fileName = $_FILES['profilePicture']['name'];
    $fileSize = $_FILES['profilePicture']['size'];
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

    // Check if the file is a .jpg file
    if (strtolower($fileType) !== 'jpg') {
        $_SESSION['profileUpdateMessage'] = "Please upload a .jpg file only.";
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to show the message
        exit();
    }

    // Check if the file size is between 50KB and 500KB
    if ($fileSize < 50 * 1024 || $fileSize > 500 * 1024) {
        $_SESSION['profileUpdateMessage'] = "Profile picture must be between 50KB and 500KB.";
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to show the message
        exit();
    }

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Delete existing profile picture (if any) before uploading new one
    if (file_exists($uploadFile)) {
        unlink($uploadFile); // Delete old profile picture
    }

    // Move the uploaded file to the directory
    if (move_uploaded_file($fileTmpName, $uploadFile)) {
        $query = "UPDATE Student_Degree SET profilePicture = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $uploadFile, $userData['id']);
        $stmt->execute();

        // Set the session flag to display message
        $_SESSION['profileUpdateMessage'] = "Profile picture updated successfully.";
        $profilePicPath = $uploadFile;
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid message on refresh
        exit();
    } else {
        $_SESSION['profileUpdateMessage'] = "Failed to upload profile picture.";
    }
}



// Handle Registration Number Update
if (isset($_POST['updateRegisterNumber']) && !empty($_POST['registerNumber'])) {
    $registerNumber = $_POST['registerNumber'];

    $updateQuery = "UPDATE Student_Degree SET registerNumber = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $registerNumber, $userData['id']);
    $stmt->execute();

    // Set the session flag to display message
    $_SESSION['profileUpdateMessage'] = "Registration number updated successfully. (Contact Faculty for updates)";
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid message on refresh
    exit();
}

// Handle Password Change
if (isset($_POST['changePassword'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    $userId = $userData['id'];

    // Fetch the current password (MD5 format) from the database
    $passwordQuery = mysqli_query($conn, "SELECT password FROM users WHERE id = '$userId'");
    $passwordData = mysqli_fetch_assoc($passwordQuery);

    // Verify current password by comparing MD5 hashes
    if (md5($currentPassword) === $passwordData['password']) {
        if ($newPassword === $confirmPassword) {
            // Update password with MD5 hash
            $hashedNewPassword = md5($newPassword);

            $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($updatePasswordQuery);
            $stmt->bind_param("si", $hashedNewPassword, $userId);
            $stmt->execute();

            $_SESSION['passwordChangeMessage'] = "Password changed successfully.";
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid message on refresh
            exit();
        } else {
            $_SESSION['passwordChangeMessage'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['passwordChangeMessage'] = "Current password is incorrect.";
    }
}


$updateMessage = $_SESSION['profileUpdateMessage'];
$_SESSION['profileUpdateMessage'] = ""; // Reset the message flag after it's shown

$passwordChangeMessage = $_SESSION['passwordChangeMessage'];
$_SESSION['passwordChangeMessage'] = ""; // Reset password change message
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            background-color: #007bff;
            color: white;
            padding: 15px;
        }
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid white;
            object-fit: cover;
            margin-right: 20px;
        }
        .container {
            margin: 20px auto;
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            margin-top: 10px;
        }
        .alert {
            margin-top: 20px;
        }
        .modal-content {
            background-color: #f4f4f4;
        }
        .form-control {
            margin-bottom: 10px;
        }
        .profile-picture-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .button-group .btn {
            width: 48%;
        }
        /* Success Popup */
        .popup-message {
            position: fixed;
            top: 0; /* Move to top of the screen */
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.5); /* Glass effect */
            color: #007bff;
            padding: 15px 30px;
            border-radius: 8px;
            display: none;
            z-index: 9999;
            font-size: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            animation: cheers 0.5s ease-out;
        }

        /* Cheers Animation */
        @keyframes cheers {
            0% {
                transform: translateX(-50%) scale(1);
                opacity: 0;
            }
            50% {
                transform: translateX(-50%) scale(1.2);
                opacity: 1;
            }
            100% {
                transform: translateX(-50%) scale(1);
                opacity: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Success or Error Popup -->
    <?php if ($updateMessage): ?>
        <div id="successPopup" class="popup-message">
            <?php echo $updateMessage; ?>
        </div>
    <?php endif; ?>


    <!-- Password Change Popup -->
    <?php if ($passwordChangeMessage): ?>
        <div id="passwordChangePopup" class="popup-message">
            <?php echo $passwordChangeMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Header Section with Profile Picture -->
    <div class="header">
        <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" class="profile-picture">
        <h2>Welcome, <?php echo $userData['firstName'] . " " . $userData['lastName']; ?></h2>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h3>Manage Your Profile</h3>

        <!-- Profile Information -->
        <h4>Profile Information</h4>
        <p><strong>Email:</strong> <?php echo $userData['email']; ?></p>
        <p><strong>Degree Type:</strong> <?php echo $degreeData['degreeType']; ?> <span style="color: red;">(Contact Faculty for updates)</span></p>
        <p><strong>Semester:</strong> <?php echo $degreeData['semester']; ?> <span style="color: red;">(Contact Faculty for updates)</span></p>

        <!-- Update Register Number -->
        <h4>Register Number</h4>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($degreeData['registerNumber']): ?>
                <p><strong>Register Number:</strong> <?php echo $degreeData['registerNumber']; ?> <span style="color: red;">(Contact Faculty for updates)</span></p>
            <?php else: ?>
                <input type="text" name="registerNumber" class="form-control" placeholder="Enter Register Number" required>
                <button type="submit" name="updateRegisterNumber" class="btn btn-custom">Update Register Number</button>
            <?php endif; ?>
        </form>

        <!-- Change Profile Picture and Password Buttons -->
        <div class="button-group">
            <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#updateProfilePicModal">Update Profile Picture</button>
            <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
        </div>

        <!-- Logout and Home Buttons -->
        <div class="button-group" style="margin-top: 20px;">
            <a href="../Login_and_Register/Frontend/logout.php" class="btn btn-custom">Logout</a>
            <a href="../Login_and_Register/Frontend/homepage.php" class="btn btn-custom">Home</a>
        </div>
    </div>

    <!-- Modal for Profile Picture Update -->
    <div class="modal fade" id="updateProfilePicModal" tabindex="-1" aria-labelledby="updateProfilePicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProfilePicModalLabel">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="file" name="profilePicture" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-custom">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Change Password -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="password" name="currentPassword" class="form-control" placeholder="Current Password" required>
                        <input type="password" name="newPassword" class="form-control mt-2" placeholder="New Password" required>
                        <input type="password" name="confirmPassword" class="form-control mt-2" placeholder="Confirm Password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="changePassword" class="btn btn-custom">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show the success popup if the session message is set
        window.onload = function() {
            var successPopup = document.getElementById("successPopup");
            if (successPopup) {
                successPopup.style.display = "block";
                setTimeout(function() {
                    successPopup.style.display = "none";
                }, 2500); // Hide after 2.5 seconds
            }

            var passwordChangePopup = document.getElementById("passwordChangePopup");
            if (passwordChangePopup) {
                passwordChangePopup.style.display = "block";
                setTimeout(function() {
                    passwordChangePopup.style.display = "none";
                }, 2500); // Hide after 2.5 seconds
            }
        };
    </script>

</body>
</html>

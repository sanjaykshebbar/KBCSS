<?php

// Clear cache headers
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//starting the session
session_start();
include("../Login_and_Register/Backend/connect.php");

// Initialize session messages
if (!isset($_SESSION['profileUpdateMessage'])) $_SESSION['profileUpdateMessage'] = "";
if (!isset($_SESSION['passwordChangeMessage'])) $_SESSION['passwordChangeMessage'] = "";

// Ensure only logged-in Facultys can access
if (!isset($_SESSION['email']) || $_SESSION['userType'] !== 'Faculty') {
    echo "<script>alert('Unauthorized access. Please log in as an Faculty.'); 
    window.location.href = '../login.php';</script>";
    exit();
}

$email = $_SESSION['email'];
$userId = null;

// Fetch user data
$userQuery = $conn->prepare("SELECT * FROM users WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userData = $userQuery->get_result()->fetch_assoc();
$userId = $userData['id'];

// Fetch admin-specific data
$facultyQuery = $conn->prepare("SELECT * FROM Faculty_Admin_Info WHERE user_id = ?");
$facultyQuery->bind_param("i", $userId);
$facultyQuery->execute();
$facultyData = $facultyQuery->get_result()->fetch_assoc();

$profilePicPath = $facultyData['profilePicture'] ?? "./Assets/Images/placeholder.png";

// Handle Profile Picture Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePicture'])) {
    handleProfilePictureUpload($email, $userId, $facultyData, $conn);
}

// Initialize the variable before any conditions
$updateDisabled = false; // Default value

// Handle Profile Update for Employee ID and Specialization
if (isset($_POST['updateFacultyInfo'])) {
    $newEmployeeID = $_POST['employeeID'];
    $newSpecialization = $_POST['specialization'];

    // Check if Employee ID and Specialization are already updated
    if (!empty($facultyData['employeeID']) && !empty($facultyData['specialization'])) {
        $updateDisabled = true; // Disable the update button
    }

    // Check for duplicate Employee ID
    $duplicateCheckQuery = "SELECT * FROM Faculty_Admin_Info WHERE employeeID = ? AND user_id != ?";
    $stmt = $conn->prepare($duplicateCheckQuery);
    $stmt->bind_param("si", $newEmployeeID, $userData['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['profileUpdateMessage'] = "Error: Employee ID already exists. Please use a different ID.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Update Employee ID and Specialization
    $updateQuery = "UPDATE Faculty_Admin_Info SET employeeID = ?, specialization = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $newEmployeeID, $newSpecialization, $userData['id']);
    $stmt->execute();

    $_SESSION['profileUpdateMessage'] = "Profile updated successfully.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Handle Password Change
if (isset($_POST['changePassword'])) {
    handleChangePassword($userId, $conn);
}

// Reset session messages after showing
$updateMessage = $_SESSION['profileUpdateMessage'];
$passwordChangeMessage = $_SESSION['passwordChangeMessage'];
$_SESSION['profileUpdateMessage'] = "";
$_SESSION['passwordChangeMessage'] = "";

// Functions
function handleProfilePictureUpload($email, $userId, $facultyData, $conn) {
    $uploadDir = "./Profile-Pic/Faculty/" . $email . "/";
    $uploadFile = $uploadDir . $email . ".jpg";

    $fileTmpName = $_FILES['profilePicture']['tmp_name'];
    $fileName = $_FILES['profilePicture']['name'];
    $fileSize = $_FILES['profilePicture']['size'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileType !== 'jpg') {
        $_SESSION['profileUpdateMessage'] = "Please upload a .jpg file only.";
        redirect();
    }

    if ($fileSize < 50 * 1024 || $fileSize > 500 * 1024) {
        $_SESSION['profileUpdateMessage'] = "Profile picture must be between 50KB and 500KB.";
        redirect();
    }

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    if (file_exists($uploadFile)) unlink($uploadFile);

    if (move_uploaded_file($fileTmpName, $uploadFile)) {
        $query = $conn->prepare("UPDATE Faculty_Admin_Info SET profilePicture = ? WHERE user_id = ?");
        $query->bind_param("si", $uploadFile, $userId);
        $query->execute();
        $_SESSION['profileUpdateMessage'] = "Profile picture updated successfully.";
    } else {
        $_SESSION['profileUpdateMessage'] = "Failed to upload profile picture.";
    }
    redirect();
}

function handleProfileUpdate($userId, $conn) {
    $newEmployeeID = $_POST['employeeID'];
    $newSpecialization = $_POST['specialization'];

    $duplicateCheckQuery = $conn->prepare("SELECT * FROM Faculty_Admin_Info WHERE employeeID = ? AND user_id != ?");
    $duplicateCheckQuery->bind_param("si", $newEmployeeID, $userId);
    $duplicateCheckQuery->execute();
    if ($duplicateCheckQuery->get_result()->num_rows > 0) {
        $_SESSION['profileUpdateMessage'] = "Error: Employee ID already exists.";
        redirect();
    }

    $updateQuery = $conn->prepare("UPDATE Faculty_Admin_Info SET employeeID = ?, specialization = ? WHERE user_id = ?");
    $updateQuery->bind_param("ssi", $newEmployeeID, $newSpecialization, $userId);
    $updateQuery->execute();

    $_SESSION['profileUpdateMessage'] = "Profile updated successfully.";
    redirect();
}

function handleChangePassword($userId, $conn) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    $passwordQuery = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $passwordQuery->bind_param("i", $userId);
    $passwordQuery->execute();
    $passwordData = $passwordQuery->get_result()->fetch_assoc();

    if (md5($currentPassword) === $passwordData['password']) {
        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) < 6) {
                $_SESSION['passwordChangeMessage'] = "Password must be at least 6 characters long.";
            } elseif (md5($newPassword) === $passwordData['password']) {
                $_SESSION['passwordChangeMessage'] = "New password cannot be the same as the current password.";
            } else {
                $hashedNewPassword = md5($newPassword);
                $updatePasswordQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updatePasswordQuery->bind_param("si", $hashedNewPassword, $userId);
                $updatePasswordQuery->execute();
                $_SESSION['passwordChangeMessage'] = "Password changed successfully.";
                redirect();
            }
        } else {
            $_SESSION['passwordChangeMessage'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['passwordChangeMessage'] = "Current password is incorrect.";
    }
}

function redirect() {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty Profile</title>
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
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.5);
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

        /* General button styling */
        button[name="updateFacultyInfo"] {
            background-color: #4CAF50; /* Green background */
            color: white;              /* White text */
            padding: 10px 20px;        /* Padding inside the button */
            ont-size: 16px;           /* Text size */
            border: none;              /* Remove border */
            border-radius: 5px;        /* Rounded corners */
            cursor: pointer;          /* Cursor changes to pointer on hover */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition */
        }

        /* Hover effect */
        button[name="updateFacultyInfo"]:hover {
            background-color: #45a049; /* Slightly darker green on hover */
            transform: scale(1.05);     /* Slightly enlarge the button on hover */
        }

        /* Focus effect */
        button[name="updateFacultyInfo"]:focus {
            outline: none;             /* Remove outline */
            box-shadow: 0 0 10px rgba(72, 245, 72, 0.7); /* Green glowing effect on focus */
        }

        /* Hidden button */
        button[name="updateFacultyInfo"][style="display:none;"] {
            display: none;             /* Ensure the button is hidden */
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

    <!-- Header Section with Profile Picture 
    <div class="header">
        <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" class="profile-picture">
        <h2>Welcome, <?php echo $userData['firstName'] . " " . $userData['lastName']; ?></h2>
    </div>
-->
    <!-- Main Container -->
    <div class="container">
    <h3 style="text-align: center;"><b>Manage Your Profile</b></h3>


        <!-- Profile Information -->
        <h4><b><u>Profile Information</u></b></h4>
        <p><strong>Email:</strong> <?php echo $userData['email']; ?></p>
        <p><strong>Employee ID:</strong> <?php echo $facultyData['employeeID'] ?: "Not Set"; ?></p>
        <p><strong>Specialization:</strong> <?php echo $facultyData['specialization'] ?: "Not Set"; ?></p>
        <p><strong>Mobile Number:</strong> <?php echo $userData['phone']; ?></p>

        <form method="POST">
            <div class="mb-3">
                <label for="employeeID" class="form-label">Employee ID</label>
                <input type="text" name="employeeID" class="form-control" value="<?php echo $facultyData['employeeID']; ?>" <?php echo $facultyData['employeeID'] ? 'readonly' : ''; ?>>
            </div>
            <div class="mb-3">
                <label for="specialization" class="form-label">Specialization</label>
                <input type="text" name="specialization" class="form-control" value="<?php echo $facultyData['specialization']; ?>" <?php echo $facultyData['specialization'] ? 'readonly' : ''; ?>>
            </div>
            <?php if (empty($facultyData['employeeID']) || empty($facultyData['specialization'])): ?>
            <!-- Show the button if Employee ID or Specialization is empty -->
                <button type="submit" name="updateFacultyInfo">Update Profile</button>
            <?php else: ?>
            <!-- Hide the button if both Employee ID and Specialization are filled -->
                <button type="submit" name="updateFacultyInfo" style="display:none;">Update Profile</button>
            <?php endif; ?>

        </form>


        <!-- Change Profile Picture and Password Buttons -->
        <div class="button-group">
            <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#updateProfilePicModal">Update Profile Picture</button>
            <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
        </div>
    </div>

    <!-- Update Profile Picture Modal -->
    <div class="modal fade" id="updateProfilePicModal" tabindex="-1" aria-labelledby="updateProfilePicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateProfilePicModalLabel">Update Profile Picture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="file" name="profilePicture" accept="image/jpg" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="password" name="currentPassword" class="form-control" placeholder="Current Password" required>
                        <input type="password" name="newPassword" class="form-control" placeholder="New Password" required>
                        <input type="password" name="confirmPassword" class="form-control" placeholder="Confirm New Password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="changePassword" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS for Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show success message after successful update
        document.addEventListener("DOMContentLoaded", function () {
            var successMessage = "<?php echo $updateMessage; ?>";
            if (successMessage !== "") {
                document.getElementById("successPopup").style.display = "block";
                setTimeout(function () {
                    document.getElementById("successPopup").style.display = "none";
                }, 3000);
            }

            var passwordChangeMessage = "<?php echo $passwordChangeMessage; ?>";
            if (passwordChangeMessage !== "") {
                document.getElementById("passwordChangePopup").style.display = "block";
                setTimeout(function () {
                    document.getElementById("passwordChangePopup").style.display = "none";
                }, 3000);
            }
        });
    </script>
</body>
</html>

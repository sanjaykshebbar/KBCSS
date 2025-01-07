<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="text"], input[type="email"], input[type="file"], button {
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
        }
        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .profile-pic {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage User Profile</h1>
        <?php
        $conn = new mysqli('localhost', 'root', 'W1nd0vv$', 'UserManagement'); // Update DB credentials

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $user_id = 1; // For testing purposes, assume user_id = 1
        $profile_picture = '';

        // Fetch user data
        $result = $conn->query("SELECT * FROM Users WHERE User_ID = $user_id");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $profile_picture = $user['Profile_Picture'];
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir);
            }

            $file_name = $_FILES['profile_picture']['name'];
            $temp_name = $_FILES['profile_picture']['tmp_name'];
            $file_path = $upload_dir . basename($file_name);

            if (move_uploaded_file($temp_name, $file_path)) {
                $conn->query("UPDATE Users SET Profile_Picture = '$file_path' WHERE User_ID = $user_id");
                echo "<p style='color: green;'>Profile picture updated successfully!</p>";
                $profile_picture = $file_path;
            } else {
                echo "<p style='color: red;'>Failed to upload profile picture.</p>";
            }
        }

        $conn->close();
        ?>
        <div class="profile-pic">
            <?php if ($profile_picture): ?>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
            <?php else: ?>
                <img src="https://via.placeholder.com/150" alt="Default Profile Picture">
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <label for="profile_picture">Upload New Profile Picture:</label>
            <input type="file" name="profile_picture" id="profile_picture" required>
            <button type="submit">Update Profile Picture</button>
        </form>
    </div>
</body>
</html>

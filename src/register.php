<?php
session_start();
include "includes/db.php";

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username  = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = "user_" . time() . "." . $ext;
            $target = "uploads/" . $new_filename;
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                $error = "❌ There was a problem uploading the image.";
            } else {
                $profile_image = $target;
            }
        } else {
            $error = "❌ Invalid image type. Only JPG, PNG, and WEBP allowed.";
        }
    }

    if (empty($error)) {
        // Check if username already exists
        $check = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "❌ Username already taken";
        } else {
            // Updated query to include profile_image column.
            $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password_hash, profile_image, role, is_active) VALUES (?, ?, ?, ?, ?, 'registered', 1)");
            $stmt->bind_param("sssss", $username, $full_name, $email, $password, $profile_image);
            if ($stmt->execute()) {
                $success = "✅ Registration successful!";
            } else {
                $error = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blog360</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0D0D1F;
            color: #FFFFFF;
        }
        .container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background: #1A1A2E;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #2C2C44;
            color: white;
            box-sizing: border-box;
            margin-bottom: 15px;
        }
        button {
            background: #6200EA;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            font-size: 16px;
        }
        button:hover {
            background: #4527A0;
        }
        .error {
            color: #FF5252;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: rgba(255, 82, 82, 0.1);
            border-radius: 5px;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #BB86FC;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        header {
            background: #0D0D1F;
            padding: 20px;
            text-align: center;
        }
        header a {
            color: white;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php">Blog360</a>
    </header>

    <div class="container">
        <h1>Create an Account</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?= $success ?> <a href="login.php" style="color: #4CAF50; text-decoration: underline;">Login here</a>
            </div>
        <?php else: ?>
            <!-- Added enctype attribute for file uploads -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <!-- optional image upload field -->
                <div class="form-group">
                    <label for="profile_image">Profile Image (optional)</label>
                    <input type="file" name="profile_image" id="profile_image">
                </div>
                
                <button type="submit">Register</button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

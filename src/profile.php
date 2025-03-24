<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $image_path = null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $target = "uploads/" . $new_filename;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $target);
            $image_path = $target;
        } else {
            $message = "❌ Only JPG, PNG, or WEBP files allowed.";
        }
    }

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ?, profile_image = COALESCE(?, profile_image) WHERE user_id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $password_hash, $image_path, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = COALESCE(?, profile_image) WHERE user_id = ?");
        $stmt->bind_param("sssi", $full_name, $email, $image_path, $user_id);
    }

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully.";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

$stmt = $conn->prepare("SELECT username, full_name, email, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $full_name, $email, $profile_image);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #090d1f;
            font-family: 'Inter', sans-serif;
            color: #ffffff;
            line-height: 1.5;
        }
        
        a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }
        
        a:hover {
            color: #6941c6;
        }
        
        button {
            cursor: pointer;
        }
        
        .container {
            max-width: 1216px;
            margin: 0 auto;
            padding: 50px 32px;
        }
        
        .header {
            background: #090d1f;
            padding: 30px 32px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .breadcrumb {
            display: flex;
            list-style: none;
            margin-bottom: 30px;
        }
        
        .breadcrumb-item {
            color: #c0c5d0;
            font-size: 16px;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding: 0 8px;
            color: rgba(255, 255, 255, 0.3);
        }
        
        .breadcrumb-item.active {
            color: #ffffff;
        }
        
        h2 {
            color: #ffffff;
            font-family: "Inter-SemiBold", sans-serif;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 32px;
        }
        
        .alert {
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 8px;
        }
        
        .alert-info {
            background: rgba(105, 65, 198, 0.1);
            border: 1px solid rgba(105, 65, 198, 0.3);
            color: #ffffff;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
        }
        
        .form-label {
            display: block;
            color: #c0c5d0;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 12px 16px;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            margin-bottom: 24px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6941c6;
            box-shadow: 0 0 0 4px rgba(105, 65, 198, 0.2);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #6941c6;
            margin-top: 8px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: #6941c6;
            color: #ffffff;
        }
        
        .btn-primary:hover {
            background: #5836a3;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .mb-3 {
            margin-bottom: 24px;
        }
        
        .ms-2 {
            margin-left: 12px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 16px;
            }
            
            .card {
                padding: 24px 16px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div class="navbar">
        <div class="cosc-360">COSC-360</div>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <h2>👤 Your Profile</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card">
        <div class="mb-3">
            <label class="form-label">Username (readonly)</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="full_name" class="form-control" value="<?= htmlspecialchars($full_name) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input name="password" type="password" class="form-control" placeholder="••••••••">
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Image (optional)</label>
            <input type="file" name="profile_image" class="form-control">
            <?php if ($profile_image): ?>
                <div class="mt-2">
                    <img src="<?= $profile_image ?>" class="profile-img">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="index.php" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>

</body>
</html>
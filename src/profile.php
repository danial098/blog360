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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <h2 class="mb-3">👤 Your Profile</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
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
                    <img src="<?= $profile_image ?>" class="profile-img border">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="index.php" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>
</body>
</html>

<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

$categories_result = $conn->query("SELECT * FROM categories");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $selected_categories = $_POST['categories'] ?? [];

    $image_path = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = [
            "jpg"  => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png"  => "image/png",
            "gif"  => "image/gif"
        ];
        $filename = $_FILES['image']['name'];
        $filetype = $_FILES['image']['type'];
        $filesize = $_FILES['image']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $allowed)) {
            $message = "Error: Please select a valid file format (JPG, JPEG, PNG, GIF).";
        }
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $message = "Error: File size is larger than allowed limit of 5MB.";
        }
        if (in_array($filetype, $allowed) && !$message) {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            $new_filename = uniqid() . "." . $ext;
            $destination = "uploads/" . $new_filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $destination)) {
                $image_path = $destination;
            } else {
                $message = "Error: There was a problem uploading your file.";
            }
        } else if (!$message) {
            $message = "Error: There was a problem with your file upload.";
        }
    }

    if ($title && $content && !$message) {
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $content, $image_path);

        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;

            if (!empty($selected_categories)) {
                $cat_stmt = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
                foreach ($selected_categories as $cat_id) {
                    $cat_stmt->bind_param("ii", $post_id, $cat_id);
                    $cat_stmt->execute();
                }
                $cat_stmt->close();
            }

            $message = "✅ Post created successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else if (!$title || !$content) {
        $message = "❌ Title and content are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Post</li>
        </ol>
    </nav>

    <h2>Create a New Blog Post</h2>

    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm mt-3">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control">
            <div class="form-text">Optional. Allowed formats: JPG, JPEG, PNG, GIF. Max size: 5MB.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Categories</label>
            <select name="categories[]" class="form-select" multiple>
                <?php while ($row = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['category_name']) ?></option>
                <?php endwhile; ?>
            </select>
            <div class="form-text">Hold Ctrl (Windows) or Command (Mac) to select multiple. (Optional)</div>
        </div>
        <button type="submit" class="btn btn-primary">Publish</button>
        <a href="index.php" class="btn btn-secondary ms-2">Back</a>
    </form>
</div>

</body>
</html>

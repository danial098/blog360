<?php
session_start();
include "includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Fetch categories for the dropdown
$categories_result = $conn->query("SELECT * FROM categories");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $selected_categories = $_POST['categories'] ?? [];

    if ($title && $content) {
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);

        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;

            // Link categories
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
    } else {
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
    <!-- Breadcrumb -->
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

    <form method="POST" class="card p-4 shadow-sm mt-3">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6" required></textarea>
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

<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$comment_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch comment
$stmt = $conn->prepare("SELECT content, post_id FROM comments WHERE comment_id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Unauthorized or comment not found.");
}
$comment = $result->fetch_assoc();
$stmt->close();

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_content = trim($_POST['content']);
    if (!empty($new_content)) {
        $update = $conn->prepare("UPDATE comments SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE comment_id = ?");
        $update->bind_param("si", $new_content, $comment_id);
        if ($update->execute()) {
            header("Location: post.php?id=" . $comment['post_id']);
            exit();
        } else {
            $message = "❌ Update failed.";
        }
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Comment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h4>Edit Your Comment</h4>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <textarea name="content" class="form-control" rows="4" required><?= htmlspecialchars($comment['content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="post.php?id=<?= $comment['post_id'] ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>

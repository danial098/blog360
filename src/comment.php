<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'], $_POST['content'])) {
    exit;
}

$post_id = (int) $_POST['post_id'];
$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);

if ($content === "") {
    exit;
}

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $content);
$stmt->execute();
$comment_id = $stmt->insert_id;
$stmt->close();

$info = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$info->bind_param("i", $user_id);
$info->execute();
$info->bind_result($full_name);
$info->fetch();
$info->close();

$date = date("Y-m-d H:i:s");
$formatted_date = date('j M Y, H:i', strtotime($date));

// Return the new comment HTML block with the dark theme styling
?>
<div class="comment-item">
    <div class="comment-header">
        <strong><?= htmlspecialchars($full_name) ?></strong>
        <small class="date"><?= $formatted_date ?></small>
    </div>
    <div class="comment-content">
        <?= nl2br(htmlspecialchars($content)) ?>
    </div>
    <div class="comment-buttons">
        <a href="edit_comment.php?id=<?= $comment_id ?>&post=<?= $post_id ?>" class="edit-btn">Edit</a>
        <a href="delete_comment.php?id=<?= $comment_id ?>&post=<?= $post_id ?>" class="delete-btn">Delete</a>
    </div>
</div>
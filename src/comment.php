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

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $content);
$stmt->execute();

// Get commenter info
$info = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$info->bind_param("i", $user_id);
$info->execute();
$info->bind_result($full_name);
$info->fetch();
$info->close();

$date = date("Y-m-d H:i:s");

// Return the new comment HTML block
?>
<div class="card mb-2">
    <div class="card-body">
        <strong><?= htmlspecialchars($full_name) ?></strong>
        <p class="mb-1"><?= nl2br(htmlspecialchars($content)) ?></p>
        <small class="text-muted"><?= $date ?></small>
    </div>
</div>

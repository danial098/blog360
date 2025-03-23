<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user posts
$user_posts = $conn->query("
    SELECT post_id, title, created_at 
    FROM blog_posts 
    WHERE user_id = $user_id AND is_removed = 0 
    ORDER BY created_at DESC
");

// Fetch liked posts
$liked_posts = $conn->query("
    SELECT bp.post_id, bp.title, bp.created_at 
    FROM likes l 
    JOIN blog_posts bp ON l.post_id = bp.post_id 
    WHERE l.user_id = $user_id AND bp.is_removed = 0
    ORDER BY bp.created_at DESC
");

// Fetch user comments
$user_comments = $conn->query("
    SELECT c.content, c.created_at, bp.title, bp.post_id
    FROM comments c 
    JOIN blog_posts bp ON c.post_id = bp.post_id 
    WHERE c.user_id = $user_id AND bp.is_removed = 0
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Activity - Blog360</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">🧾 Your Activity</h2>

    <!-- Posts -->
    <h4>📝 Your Posts</h4>
    <?php if ($user_posts->num_rows > 0): ?>
        <ul class="list-group mb-4">
            <?php while ($row = $user_posts->fetch_assoc()): ?>
                <li class="list-group-item">
                    <a href="post.php?id=<?= $row['post_id'] ?>" class="text-decoration-none fw-bold">
                        <?= htmlspecialchars($row['title']) ?>
                    </a>
                    <small class="text-muted"> - <?= $row['created_at'] ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">You haven’t posted anything yet.</p>
    <?php endif; ?>

    <!-- Likes -->
    <h4>❤️ Posts You Liked</h4>
    <?php if ($liked_posts->num_rows > 0): ?>
        <ul class="list-group mb-4">
            <?php while ($row = $liked_posts->fetch_assoc()): ?>
                <li class="list-group-item">
                    <a href="post.php?id=<?= $row['post_id'] ?>" class="text-decoration-none">
                        <?= htmlspecialchars($row['title']) ?>
                    </a>
                    <small class="text-muted"> - <?= $row['created_at'] ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">No liked posts yet.</p>
    <?php endif; ?>

    <!-- Comments -->
    <h4>💬 Your Comments</h4>
    <?php if ($user_comments->num_rows > 0): ?>
        <ul class="list-group mb-5">
            <?php while ($row = $user_comments->fetch_assoc()): ?>
                <li class="list-group-item">
                    On <a href="post.php?id=<?= $row['post_id'] ?>" class="text-decoration-none">
                        <?= htmlspecialchars($row['title']) ?>
                    </a>:
                    <blockquote class="blockquote mt-1 mb-0"><?= nl2br(htmlspecialchars($row['content'])) ?></blockquote>
                    <small class="text-muted"><?= $row['created_at'] ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">You haven’t commented yet.</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary">← Back to Home</a>
</div>
</body>
</html>

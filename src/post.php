<?php
session_start();
include "includes/db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid post ID.");
}

$post_id = (int) $_GET['id'];

// Fetch post
$stmt = $conn->prepare("
    SELECT p.*, u.full_name 
    FROM blog_posts p 
    JOIN users u ON p.user_id = u.user_id 
    WHERE p.post_id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    die("Post not found.");
}

// Fetch post categories
$category_stmt = $conn->prepare("
    SELECT c.category_id, c.category_name 
    FROM categories c 
    JOIN post_categories pc ON c.category_id = pc.category_id 
    WHERE pc.post_id = ?
");
$category_stmt->bind_param("i", $post_id);
$category_stmt->execute();
$categories_result = $category_stmt->get_result();
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat;
}
$category_stmt->close();

// Likes
$likes = $conn->query("SELECT COUNT(*) AS count FROM likes WHERE post_id = $post_id")->fetch_assoc()['count'];
$liked = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $liked = $conn->query("SELECT 1 FROM likes WHERE post_id = $post_id AND user_id = $uid")->num_rows > 0;
}

// Comments
$comments = $conn->prepare("
    SELECT c.*, u.full_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    WHERE c.post_id = ? 
    ORDER BY c.created_at ASC
");
$comments->bind_param("i", $post_id);
$comments->execute();
$comments_result = $comments->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?> - Blog360</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($post['title']) ?></li>
        </ol>
    </nav>

    <!-- Blog Post -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0"><?= htmlspecialchars($post['title']) ?></h3>
                <small class="text-muted">by <?= htmlspecialchars($post['full_name']) ?> | <?= $post['created_at'] ?></small>
                <?php if (!empty($categories)): ?>
                    <div class="mt-1">
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category=<?= $cat['category_id'] ?>" class="badge bg-secondary text-decoration-none">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="toggle_post.php?id=<?= $post_id ?>" class="btn btn-sm btn-outline-danger">Delete Post</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="like-form" class="d-inline">
                    <input type="hidden" name="post_id" value="<?= $post_id ?>">
                    <button type="submit" class="btn btn-sm <?= $liked ? 'btn-danger' : 'btn-outline-danger' ?>">
                        ❤️ <?= $liked ? 'Unlike' : 'Like' ?> (<?= $likes ?>)
                    </button>
                </form>
            <?php else: ?>
                <p class="text-muted mt-3">❤️ <?= $likes ?> likes</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Comments Section -->
    <h5>💬 Comments</h5>
    <div id="comments">
        <?php while ($comment = $comments_result->fetch_assoc()): ?>
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?= htmlspecialchars($comment['full_name']) ?></strong>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        <small class="text-muted"><?= $comment['created_at'] ?></small>
                    </div>
                    <div class="ms-2">
                        <?php if (
                            (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']) ||
                            (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')
                        ): ?>
                            <?php if ($_SESSION['user_id'] == $comment['user_id']): ?>
                                <a href="edit_comment.php?id=<?= $comment['comment_id'] ?>&post=<?= $post_id ?>" class="btn btn-sm btn-outline-primary mb-1">Edit</a>
                            <?php endif; ?>
                            <a href="delete_comment.php?id=<?= $comment['comment_id'] ?>&post=<?= $post_id ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Comment Form -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <form id="comment-form" class="mt-4">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">
            <div class="mb-3">
                <label for="content" class="form-label">Add a comment:</label>
                <textarea name="content" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post Comment</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning mt-3">You must <a href="login.php">log in</a> to comment.</div>
    <?php endif; ?>
</div>

<script>
document.getElementById("comment-form")?.addEventListener("submit", async function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const response = await fetch("comment.php", {
        method: "POST",
        body: formData
    });
    const html = await response.text();
    document.getElementById("comments").innerHTML += html;
    form.reset();
});

document.getElementById("like-form")?.addEventListener("submit", async function (e) {
    e.preventDefault();
    const form = e.target;
    const response = await fetch("like.php", {
        method: "POST",
        body: new FormData(form)
    });
    const result = await response.text();
    location.reload(); // simple reload for now
});
</script>
</body>
</html>


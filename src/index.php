<?php
session_start();
include "includes/db.php";

// Fetch categories
$categories = $conn->query("SELECT * FROM categories");

// Filter inputs
$selected_category = isset($_GET['category']) ? (int) $_GET['category'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL Base
$sql = "
    SELECT p.*, u.full_name, COUNT(l.user_id) AS like_count
    FROM blog_posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN likes l ON l.post_id = p.post_id
";

// Category join if needed
if ($selected_category) {
    $sql .= " JOIN post_categories pc ON p.post_id = pc.post_id ";
}

// Conditions
$conditions = ["p.is_removed = 0"];
if ($selected_category) {
    $conditions[] = "pc.category_id = $selected_category";
}
if ($search_term !== '') {
    $safe = $conn->real_escape_string($search_term);
    $conditions[] = "(p.title LIKE '%$safe%' OR p.content LIKE '%$safe%')";
}

// Apply conditions
$sql .= " WHERE " . implode(" AND ", $conditions);
$sql .= " GROUP BY p.post_id ORDER BY p.created_at DESC";

// Fetch posts
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog360</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <!-- NAVBAR -->
    <nav class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Blog360</h2>
        <div>
            <?php if (isset($_SESSION['username'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-outline-dark btn-sm me-2">Admin Panel</a>
                <?php endif; ?>
                <a href="create_post.php" class="btn btn-outline-success btn-sm me-2">New Post</a>
                <a href="activity.php" class="btn btn-outline-info btn-sm me-2">Your Activity</a>
                <span class="me-2">👋 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="profile.php" class="btn btn-outline-primary btn-sm">Profile</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm ms-2">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm">Login</a>
                <a href="register.php" class="btn btn-success btn-sm ms-2">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- SEARCH BAR -->
    <form method="GET" class="mb-3 d-flex" role="search">
        <?php if ($selected_category): ?>
            <input type="hidden" name="category" value="<?= $selected_category ?>">
        <?php endif; ?>
        <input type="text" name="search" class="form-control me-2" placeholder="Search posts..." value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit" class="btn btn-outline-primary">Search</button>
    </form>

    <!-- CATEGORY FILTERS -->
    <h4 class="mb-3">📂 Filter by Category</h4>
    <div class="mb-4">
        <a href="index.php" class="btn btn-sm <?= !$selected_category ? 'btn-primary' : 'btn-outline-primary' ?> me-2">All</a>
        <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="index.php?category=<?= $cat['category_id'] ?>" class="btn btn-sm <?= ($selected_category == $cat['category_id']) ? 'btn-primary' : 'btn-outline-primary' ?> me-2">
                <?= htmlspecialchars($cat['category_name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- HOT POSTS -->
    <h4 class="mb-3">🔥 Hot Posts</h4>
    <?php
    $hot = $conn->query("
        SELECT p.post_id, p.title, COUNT(l.user_id) AS like_count 
        FROM blog_posts p 
        LEFT JOIN likes l ON l.post_id = p.post_id 
        WHERE is_removed = 0 
        GROUP BY p.post_id 
        ORDER BY like_count DESC 
        LIMIT 3
    ");
    while ($hp = $hot->fetch_assoc()):
    ?>
        <div class="mb-2">
            <a href="post.php?id=<?= $hp['post_id'] ?>" class="text-decoration-none fw-bold">
                <?= htmlspecialchars($hp['title']) ?>
            </a>
            <span class="text-muted"> (❤️ <?= $hp['like_count'] ?>)</span>
        </div>
    <?php endwhile; ?>
    <hr class="my-4">

    <!-- POSTS -->
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <strong>
                        <a href="post.php?id=<?= $row['post_id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($row['title']) ?>
                        </a>
                    </strong>
                    <span class="text-muted">by <?= htmlspecialchars($row['full_name']) ?></span>
                </div>
                <div class="card-body">
                    <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    <small class="text-muted">Posted on <?= $row['created_at'] ?></small><br>
                    <small class="text-muted">❤️ <?= $row['like_count'] ?> likes</small>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="toggle_post.php?id=<?= $row['post_id'] ?>" class="btn btn-sm btn-outline-danger ms-2">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No posts found for your search.</div>
    <?php endif; ?>
</div>

</body>
</html>



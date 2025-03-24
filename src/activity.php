<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_posts = $conn->query("
    SELECT post_id, title, created_at 
    FROM blog_posts 
    WHERE user_id = $user_id AND is_removed = 0 
    ORDER BY created_at DESC
");

$liked_posts = $conn->query("
    SELECT bp.post_id, bp.title, bp.created_at 
    FROM likes l 
    JOIN blog_posts bp ON l.post_id = bp.post_id 
    WHERE l.user_id = $user_id AND bp.is_removed = 0
    ORDER BY bp.created_at DESC
");

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
    <!-- External CSS files -->
    <link rel="stylesheet" href="./vars.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="./styles/index.css">
    <link rel="stylesheet" href="./styles/activity.css">
    <!-- Inline reset styles (as shared previously) -->
    <style>
       a,
       button,
       input,
       select,
       h1,
       h2,
       h3,
       h4,
       h5,
       * {
           box-sizing: border-box;
           margin: 0;
           padding: 0;
           border: none;
           text-decoration: none;
           background: none;
           -webkit-font-smoothing: antialiased;
       }
       
       menu, ol, ul {
           list-style-type: none;
           margin: 0;
           padding: 0;
       }

body.activity-page {
    background-color: #1d1f20;
    color: #f5f5f5;
    font-family: 'Helvetica Neue', Arial, sans-serif;
}

.header {
    background: #2b2d2f;
    padding: 20px;
    border-bottom: 1px solid #3a3c3d;
}
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.brand {
    font-size: 24px;
    font-weight: bold;
    color: #fff;
}
.menu {
    display: flex;
    gap: 15px;
}
.nav-item {
    font-size: 16px;
    color: #bbb;
    transition: color 0.3s ease;
}
.nav-item:hover {
    color: #fff;
}

.container.activity-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.page-title {
    font-size: 28px;
    margin-bottom: 30px;
    text-align: center;
    color: #fff;
}

.activity-block {
    background: #2b2d2f;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #3a3c3d;
}

.section-title {
    font-size: 20px;
    margin-bottom: 15px;
    color: #f5f5f5;
    border-bottom: 2px solid #007bff;
    display: inline-block;
    padding-bottom: 5px;
}

.list-group {
    margin: 0;
    padding: 0;
}
.list-group-item {
    background: #383a3b;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #4a4c4d;
}
.item-link {
    font-size: 18px;
    font-weight: 500;
    color: #fff;
    transition: color 0.3s ease;
}
.item-link:hover {
    color: #1da1f2;
}
.item-date {
    display: block;
    font-size: 14px;
    color: #aaa;
    margin-top: 5px;
}

.blockquote {
    border-left: 4px solid #007bff;
    margin: 10px 0;
    padding-left: 15px;
    font-style: italic;
    color: #ddd;
}

.text-muted {
    color: #999;
}

/* Back button */
.back-button {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #007bff;
    color: #fff;
    border-radius: 5px;
    transition: background 0.3s ease;
}
.back-button:hover {
    background: #0056b3;
}

.footer {
    background: #2b2d2f;
    border-top: 1px solid #3a3c3d;
    padding: 20px 0;
    margin-top: 40px;
    text-align: center;
}
.footer-container {
    display: flex;
    justify-content: center;
    gap: 20px;
}
.footer-link {
    font-size: 14px;
    color: #bbb;
    transition: color 0.3s ease;
}
.footer-link:hover {
    color: #fff;
}

    </style>
</head>
<body class="activity-page">
    <header class="header">
        <div class="navbar">
            <a href="index.php" class="brand">Blog360</a>
            <nav class="menu">
                <a href="index.php" class="nav-item">Home</a>
                <a href="profile.php" class="nav-item">Profile</a>
                <a href="logout.php" class="nav-item">Logout</a>
            </nav>
        </div>
    </header>
    
    <div class="container activity-container">
        <h2 class="page-title">🧾 Your Activity</h2>
    
        <div class="activity-block">
            <h4 class="section-title">📝 Your Posts</h4>
            <?php if ($user_posts->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($row = $user_posts->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <a href="post.php?id=<?= $row['post_id'] ?>" class="item-link">
                                <?= htmlspecialchars($row['title']) ?>
                            </a>
                            <small class="item-date"><?= date("M j, Y", strtotime($row['created_at'])) ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">You haven’t posted anything yet.</p>
            <?php endif; ?>
        </div>
    
        <div class="activity-block">
            <h4 class="section-title">❤️ Posts You Liked</h4>
            <?php if ($liked_posts->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($row = $liked_posts->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <a href="post.php?id=<?= $row['post_id'] ?>" class="item-link">
                                <?= htmlspecialchars($row['title']) ?>
                            </a>
                            <small class="item-date"><?= date("M j, Y", strtotime($row['created_at'])) ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No liked posts yet.</p>
            <?php endif; ?>
        </div>
    
        <div class="activity-block">
            <h4 class="section-title">💬 Your Comments</h4>
            <?php if ($user_comments->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($row = $user_comments->fetch_assoc()): ?>
                        <li class="list-group-item">
                            On <a href="post.php?id=<?= $row['post_id'] ?>" class="item-link">
                                <?= htmlspecialchars($row['title']) ?>
                            </a>:
                            <blockquote class="blockquote">
                                <?= nl2br(htmlspecialchars($row['content'])) ?>
                            </blockquote>
                            <small class="item-date"><?= date("M j, Y", strtotime($row['created_at'])) ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">You haven’t commented yet.</p>
            <?php endif; ?>
        </div>
    
        <a href="index.php" class="btn back-button">← Back to Home</a>
    </div>
    
    <footer class="footer">
        <div class="container footer-container">
            <nav class="menu">
                <a href="#" class="footer-link">Twitter</a>
                <a href="#" class="footer-link">LinkedIn</a>
                <a href="#" class="footer-link">Email</a>
                <a href="#" class="footer-link">RSS feed</a>
                <a href="#" class="footer-link">Add to Feedly</a>
            </nav>
        </div>
    </footer>
</body>
</html>

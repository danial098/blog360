<?php
session_start();
include "includes/db.php";

// Function to get recent posts for sidebar
function get_recent_posts($conn, $limit = 2) {
    $stmt = $conn->prepare("
        SELECT p.*, u.full_name 
        FROM blog_posts p 
        JOIN users u ON p.user_id = u.user_id 
        ORDER BY p.created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = [];
    while ($post = $result->fetch_assoc()) {
        $posts[] = $post;
    }
    $stmt->close();
    return $posts;
}

// Get post data if post ID is provided
$post = null;
$categories = [];
$likes = 0;
$liked = false;
$comments_result = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
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

    if ($post) {
        $category_stmt = $conn->prepare("
            SELECT c.category_id, c.category_name 
            FROM categories c 
            JOIN post_categories pc ON c.category_id = pc.category_id 
            WHERE pc.post_id = ?
        ");
        $category_stmt->bind_param("i", $post_id);
        $category_stmt->execute();
        $categories_result = $category_stmt->get_result();
        while ($cat = $categories_result->fetch_assoc()) {
            $categories[] = $cat;
        }
        $category_stmt->close();

        $likes = $conn->query("SELECT COUNT(*) AS count FROM likes WHERE post_id = $post_id")->fetch_assoc()['count'];
        $liked = false;
        if (isset($_SESSION['user_id'])) {
            $uid = $_SESSION['user_id'];
            $liked = $conn->query("SELECT 1 FROM likes WHERE post_id = $post_id AND user_id = $uid")->num_rows > 0;
        }

        $comments_stmt = $conn->prepare("
            SELECT c.*, u.full_name 
            FROM comments c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at ASC
        ");
        $comments_stmt->bind_param("i", $post_id);
        $comments_stmt->execute();
        $comments_result = $comments_stmt->get_result();
    }
}

$recent_posts = get_recent_posts($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? htmlspecialchars($post['title']) : "Your Blog" ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0D0D1F;
            color: #FFFFFF;
        }
        header {
            background: #0D0D1F;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        nav {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-left: 65px;
        }
        .navigation {
            margin-right: 65px;
        }
        .date {
            color: blueviolet;
        }
        .recentblogs {
            color: #FFFFFF;
            font-weight: bold;
            text-decoration: none;
        }
        .recentblogs:hover {
            text-decoration: underline;
        }
        .textclr {
            color: lightgray;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            display: inline;
        }
        nav a {
            color: white;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            display: flex;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .recent-posts {
            width: 30%;
            padding-right: 20px;
        }
        .recent-posts h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .post-preview {
            background: #1A1A2E;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .post-preview img {
            width: 100%;
            border-radius: 5px;
            height: 150px;
            object-fit: cover;
        }
        .blog-post {
            width: 70%;
            background: #1A1A2E;
            padding: 25px;
            border-radius: 10px;
        }
        .blog-post img {
            width: 100%;
            border-radius: 5px;
            max-height: 400px;
            object-fit: cover;
            margin: 15px 0;
        }
        .like-section {
            margin-top: 30px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .like-section button {
            background: #E91E63;
            border: none;
            padding: 10px 15px;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }
        .like-section button:hover {
            background: #C2185B;
        }
        .category-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
        }
        .category-tag {
            background: #4A148C;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            text-decoration: none;
        }
        .comments {
            margin-top: 30px;
        }
        .comment-item {
            background: #2C2C44;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .comment-content {
            color: #E0E0E0;
        }
        .comment-buttons {
            display: flex;
            gap: 10px;
        }
        #comment-text {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: none;
            background: #2C2C44;
            color: white;
            resize: vertical;
            min-height: 100px;
        }
        button[type='submit'] {
            background: #6200EA;
            border: none;
            padding: 12px 20px;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }
        button[type='submit']:hover {
            background: #4527A0;
        }
        .auth-message {
            background: #2C2C44;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .auth-message a {
            color: #BB86FC;
            text-decoration: none;
        }
        .auth-message a:hover {
            text-decoration: underline;
        }
        .breadcrumb {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            color: #BB86FC;
        }
        .breadcrumb a {
            color: #BB86FC;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .breadcrumb span:after {
            content: "›";
            margin-left: 10px;
        }
        .delete-btn, .edit-btn {
            background: transparent;
            border: 1px solid #E91E63;
            color: #E91E63;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .edit-btn {
            border-color: #6200EA;
            color: #6200EA;
        }
        .home-posts {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .featured-post {
            grid-column: span 2;
        }
        .post-card {
            background: #1A1A2E;
            border-radius: 8px;
            overflow: hidden;
        }
        .post-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .post-card-content {
            padding: 20px;
        }
        .post-card h3 {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .post-card a {
            color: white;
            text-decoration: none;
        }
        .post-card a:hover {
            text-decoration: underline;
        }
        .post-excerpt {
            color: lightgray;
            margin-bottom: 15px;
        }
        .read-more {
            display: inline-block;
            background: #6200EA;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .read-more:hover {
            background: #4527A0;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Blog360</div>
            <ul class="navigation">
                <li><a href="index.php">Blog</a></li>
                <li><a href="about.php">About</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Log out</a></li>
                <?php else: ?>
                    <li><a href="login.php">Log in</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <main class="container">
        <aside class="recent-posts">
            <h2>Recent blog posts</h2>
            <?php foreach ($recent_posts as $recent): ?>
                <div class="post-preview">
                    <img src="<?= !empty($recent['image']) ? htmlspecialchars($recent['image']) : 'images/default-post.jpg' ?>" alt="Post Image">
                    <p class="date"><?= date('l, j M Y', strtotime($recent['created_at'])) ?></p>
                    <h3><a class="recentblogs" href="post.php?id=<?= $recent['post_id'] ?>"><?= htmlspecialchars($recent['title']) ?></a></h3>
                    <p class="textclr"><?= htmlspecialchars(substr($recent['content'], 0, 100)) ?>...</p>
                </div>
            <?php endforeach; ?>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="post-preview">
                    <h3><a href="admin_dashboard.php" class="recentblogs">Admin Dashboard</a></h3>
                    <p class="textclr">Manage posts, users, and comments</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="post-preview">
                    <h3><a href="create_post.php" class="recentblogs">Create New Post</a></h3>
                    <p class="textclr">Share your thoughts with the world</p>
                </div>
            <?php endif; ?>
        </aside>
        
        <?php if ($post): ?>
            <!-- Single Post View -->
            <article class="blog-post">
                <div class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span></span>
                    <a href="#">Blog</a>
                    <span></span>
                    <?= htmlspecialchars($post['title']) ?>
                </div>
                
                <p class="date"><?= date('l, j M Y', strtotime($post['created_at'])) ?></p>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <p>By <?= htmlspecialchars($post['full_name']) ?></p>
                
                <?php if (!empty($categories)): ?>
                    <div class="category-tags">
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category=<?= $cat['category_id'] ?>" class="category-tag">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <img src="<?= !empty($post['image']) ? htmlspecialchars($post['image']) : 'images/default-post.jpg' ?>" alt="Blog Image">
                
                <div class="post-content">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
                
                <div class="like-section">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form id="like-form">
                            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                            <button type="submit" id="like-btn">
                                ❤️ <?= $liked ? 'Unlike' : 'Like' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <button disabled>❤️ Like</button>
                    <?php endif; ?>
                    <span id="like-count"><?= $likes ?></span> Likes
                </div>
                
                <section class="comments">
                    <h2>Comments</h2>
                    <div id="comment-list">
                        <?php if ($comments_result && $comments_result->num_rows > 0): ?>
                            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                                <div class="comment-item">
                                    <div class="comment-header">
                                        <strong><?= htmlspecialchars($comment['full_name']) ?></strong>
                                        <small class="date"><?= date('j M Y, H:i', strtotime($comment['created_at'])) ?></small>
                                    </div>
                                    <div class="comment-content">
                                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                    </div>
                                    <?php if (
                                        (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']) ||
                                        (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')
                                    ): ?>
                                        <div class="comment-buttons" style="margin-top: 20px;">
                                            <?php if ($_SESSION['user_id'] == $comment['user_id']): ?>
                                                <a href="edit_comment.php?id=<?= $comment['comment_id'] ?>&post=<?= $post['post_id'] ?>" class="edit-btn">Edit</a>
                                            <?php endif; ?>
                                            <a href="delete_comment.php?id=<?= $comment['comment_id'] ?>&post=<?= $post['post_id'] ?>" class="delete-btn">Delete</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form id="comment-form">
                            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                            <textarea id="comment-text" name="content" placeholder="Write a comment..."></textarea>
                            <button type="submit">Post Comment</button>
                        </form>
                    <?php else: ?>
                        <div class="auth-message">
                            You must <a href="login.php">log in</a> to comment.
                        </div>
                    <?php endif; ?>
                </section>
            </article>
        <?php else: ?>
            <div class="blog-post">
                <h1>Latest Articles</h1>
                
                <div class="home-posts">
                    <?php
                    $all_posts = $conn->query("
                        SELECT p.*, u.full_name 
                        FROM blog_posts p 
                        JOIN users u ON p.user_id = u.user_id 
                        ORDER BY p.created_at DESC 
                        LIMIT 5
                    ");
                    
                    $first = true;
                    while ($post = $all_posts->fetch_assoc()):
                    ?>
                        <div class="post-card <?= $first ? 'featured-post' : '' ?>">
                            <img src="<?= !empty($post['image']) ? htmlspecialchars($post['image']) : 'images/default-post.jpg' ?>" alt="Post Image">
                            <div class="post-card-content">
                                <p class="date"><?= date('l, j M Y', strtotime($post['created_at'])) ?></p>
                                <h3><a href="post.php?id=<?= $post['post_id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                                <p class="post-excerpt"><?= htmlspecialchars(substr($post['content'], 0, 200)) ?>...</p>
                                <a href="post.php?id=<?= $post['post_id'] ?>" class="read-more">Read more</a>
                            </div>
                        </div>
                    <?php
                        $first = false;
                    endwhile;
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
    // Like functionality
    document.getElementById("like-form")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        try {
            const response = await fetch("like.php", {
                method: "POST",
                body: formData
            });
            const result = await response.text();
            location.reload(); // Simple reload for now
        } catch (error) {
            console.error("Error:", error);
        }
    });

    // Comment functionality
    document.getElementById("comment-form")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        try {
            const response = await fetch("comment.php", {
                method: "POST",
                body: formData
            });
            const result = await response.text();
            // Reload to show the new comment
            location.reload();
        } catch (error) {
            console.error("Error:", error);
        }
    });
    </script>
</body>
</html>
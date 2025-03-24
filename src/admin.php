<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Blog360</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
        </ol>
    </nav>

    <h2 class="mb-4">🛠️ Admin Dashboard</h2>

    <h4>👥 Users</h4>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
            while ($u = $users->fetch_assoc()):
            ?>
            <tr>
                <td><?= $u['user_id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['role'] ?></td>
                <td><?= $u['is_active'] ? '✅ Active' : '❌ Disabled' ?></td>
                <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                        <a href="toggle_user.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-warning">
                            <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4 class="mt-5">📝 Posts</h4>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>User</th>
                <th>Status</th>
                <th>Posted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $posts = $conn->query("
                SELECT p.*, u.full_name 
                FROM blog_posts p 
                JOIN users u ON p.user_id = u.user_id 
                ORDER BY created_at DESC
            ");
            while ($p = $posts->fetch_assoc()):
            ?>
            <tr>
                <td><?= $p['post_id'] ?></td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= htmlspecialchars($p['full_name']) ?></td>
                <td><?= $p['is_removed'] ? '🗑️ Removed' : '✅ Active' ?></td>
                <td><?= $p['created_at'] ?></td>
                <td>
                    <a href="toggle_post.php?id=<?= $p['post_id'] ?>" class="btn btn-sm btn-danger">
                        <?= $p['is_removed'] ? 'Restore' : 'Remove' ?>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn btn-secondary mt-3">← Back to Home</a>
</div>
</body>
</html>

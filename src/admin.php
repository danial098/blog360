<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
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
    <nav aria-label="breadcrumb" class="mb-4 d-flex justify-content-between align-items-center">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
        </ol>
        <a href="admin_analytics.php" class="btn btn-sm btn-outline-info">📊 Analytics</a>
    </nav>

    <h2 class="mb-4">🛠️ Admin Dashboard</h2>

    <form method="GET" class="input-group mb-4">
        <input type="text" name="search" class="form-control" placeholder="Search users or posts..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-primary">Search</button>
    </form>

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
        $user_sql = "SELECT * FROM users";
        if ($search) {
            $safe = $conn->real_escape_string($search);
            $user_sql .= " WHERE username LIKE '%$safe%' OR full_name LIKE '%$safe%' OR email LIKE '%$safe%'";
        }
        $user_sql .= " ORDER BY created_at DESC";
        $users = $conn->query($user_sql);
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
        $post_sql = "
            SELECT p.*, u.full_name 
            FROM blog_posts p 
            JOIN users u ON p.user_id = u.user_id 
            WHERE 1=1
        ";
        if ($search) {
            $safe = $conn->real_escape_string($search);
            $post_sql .= " AND (p.title LIKE '%$safe%' OR p.content LIKE '%$safe%')";
        }
        $post_sql .= " ORDER BY p.created_at DESC";
        $posts = $conn->query($post_sql);

        while ($p = $posts->fetch_assoc()):
            ?>
            <tr>
                <td><?= $p['post_id'] ?></td>
                <td><a href="post.php?id=<?= $p['post_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($p['title']) ?></a></td>
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

<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$search_results = [];
if ($search_query !== '') {
    $safe = $conn->real_escape_string($search_query);


    $post_query = $conn->query("
        SELECT 'Post' as type, title AS label, created_at 
        FROM blog_posts 
        WHERE title LIKE '%$safe%' OR content LIKE '%$safe%'
    ");
    while ($row = $post_query->fetch_assoc()) {
        $search_results[] = $row;
    }


    $user_query = $conn->query("
        SELECT 'User' as type, full_name AS label, created_at 
        FROM users 
        WHERE full_name LIKE '%$safe%' OR email LIKE '%$safe%'
    ");
    while ($row = $user_query->fetch_assoc()) {
        $search_results[] = $row;
    }
}


$weekly_data = [];
$result = $conn->query("
    SELECT 'Post' AS type, DATE(created_at) AS date, COUNT(*) AS count
    FROM blog_posts GROUP BY DATE(created_at)
    UNION ALL
    SELECT 'Comment' AS type, DATE(created_at) AS date, COUNT(*) AS count
    FROM comments GROUP BY DATE(created_at)
");
while ($row = $result->fetch_assoc()) {
    $weekly_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Analytics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📊 Admin Analytics</h2>

    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search posts or users..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <?php if ($search_query && $search_results): ?>
        <div class="mb-4">
            <h5>Search Results for "<strong><?= htmlspecialchars($search_query) ?></strong>":</h5>
            <ul class="list-group">
                <?php foreach ($search_results as $result): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <strong><?= htmlspecialchars($result['label']) ?></strong>
                            <small class="text-muted">(<?= $result['type'] ?>, <?= $result['created_at'] ?>)</small>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($search_query): ?>
        <div class="alert alert-warning">No results found.</div>
    <?php endif; ?>

    <h4 class="mb-3">📈 Weekly Activity (Posts & Comments)</h4>
    <canvas id="activityChart" height="100"></canvas>

    <a href="admin.php" class="btn btn-secondary mt-4">← Back to Admin Dashboard</a>
</div>

<script>
    const ctx = document.getElementById('activityChart').getContext('2d');
    const rawData = <?= json_encode($weekly_data) ?>;

    const grouped = { Post: {}, Comment: {} };
    rawData.forEach(item => {
        const date = item.date;
        const type = item.type;
        if (!grouped[type][date]) grouped[type][date] = 0;
        grouped[type][date] += parseInt(item.count);
    });

    const labels = [...new Set(rawData.map(item => item.date))].sort();
    const postCounts = labels.map(date => grouped.Post[date] || 0);
    const commentCounts = labels.map(date => grouped.Comment[date] || 0);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Posts',
                    data: postCounts,
                    borderColor: 'blue',
                    fill: false
                },
                {
                    label: 'Comments',
                    data: commentCounts,
                    borderColor: 'green',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: { display: true, text: 'Date' }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Count' }
                }
            }
        }
    });
</script>
</body>
</html>

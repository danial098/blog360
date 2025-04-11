<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

$activity_types = [];
if (isset($_GET['activity'])) {
    $activity_types = $_GET['activity'];
} else {
    $activity_types = ['posts'];
}

$dates = [];
$activity_data = [];

$current = strtotime($start_date);
$end = strtotime($end_date);
while ($current <= $end) {
    $date_str = date('Y-m-d', $current);
    $dates[] = $date_str;
    $current = strtotime('+1 day', $current);
}

if (in_array('posts', $activity_types)) {
    $posts_data = [];
    $posts_sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM blog_posts 
                  WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                  GROUP BY DATE(created_at)";
    $posts_result = $conn->query($posts_sql);
    
    foreach ($dates as $date) {
        $posts_data[$date] = 0;
    }
    
    while ($row = $posts_result->fetch_assoc()) {
        $posts_data[$row['date']] = (int)$row['count'];
    }
    
    $activity_data['posts'] = array_values($posts_data);
}

if (in_array('comments', $activity_types)) {
    $comments_data = [];
    $comments_sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM comments 
                    WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                    GROUP BY DATE(created_at)";
    $comments_result = $conn->query($comments_sql);
    
    foreach ($dates as $date) {
        $comments_data[$date] = 0;
    }
    
    while ($row = $comments_result->fetch_assoc()) {
        $comments_data[$row['date']] = (int)$row['count'];
    }
    
    $activity_data['comments'] = array_values($comments_data);
}

if (in_array('likes', $activity_types)) {
    $likes_data = [];
    $likes_sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM likes 
                  WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                  GROUP BY DATE(created_at)";
    $likes_result = $conn->query($likes_sql);
    
    foreach ($dates as $date) {
        $likes_data[$date] = 0;
    }
    
    while ($row = $likes_result->fetch_assoc()) {
        $likes_data[$row['date']] = (int)$row['count'];
    }
    
    $activity_data['likes'] = array_values($likes_data);
}

$formatted_dates = array_map(function($date) {
    return date('M j', strtotime($date));
}, $dates);

$dates_json = json_encode($formatted_dates);
$activity_data_json = json_encode($activity_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard - Blog360</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <nav aria-label="breadcrumb" class="mb-4 d-flex justify-content-between align-items-center">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Analytics</li>
        </ol>
    </nav>

    <h2 class="mb-4">📊 Activity Analytics</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Activity Types</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="activity[]" value="posts" id="check_posts" 
                                <?= in_array('posts', $activity_types) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="check_posts">Posts</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="activity[]" value="comments" id="check_comments"
                                <?= in_array('comments', $activity_types) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="check_comments">Comments</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="activity[]" value="likes" id="check_likes"
                                <?= in_array('likes', $activity_types) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="check_likes">Likes</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <canvas id="activityChart" height="100"></canvas>
        </div>
    </div>

    <a href="admin.php" class="btn btn-secondary mt-3">← Back to Admin Dashboard</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    const dates = <?= $dates_json ?>;
    const activityData = <?= $activity_data_json ?>;
    
    const colors = {
        posts: 'rgba(54, 162, 235, 0.7)',
        comments: 'rgba(255, 99, 132, 0.7)',
        likes: 'rgba(75, 192, 192, 0.7)'
    };
    
    const datasets = [];
    const labels = {
        posts: 'Posts',
        comments: 'Comments',
        likes: 'Likes'
    };
    
    for (const [type, data] of Object.entries(activityData)) {
        datasets.push({
            label: labels[type],
            data: data,
            backgroundColor: colors[type],
            borderColor: colors[type].replace('0.7', '1'),
            borderWidth: 1
        });
    }
    
    // Create the chart
    const activityChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: datasets
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Activities'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Activity by Date',
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
});
</script>
</body>
</html> 
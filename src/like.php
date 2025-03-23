<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    http_response_code(403);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = (int) $_POST['post_id'];

// Check if already liked
$check = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
    echo "unliked";
} else {
    $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    echo "liked";
}

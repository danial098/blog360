<?php
session_start();
include "includes/db.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$comment_id = (int) $_GET['id'];
$post_id = (int) $_GET['post'];

$stmt = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$comment = $result->fetch_assoc();
$stmt->close();

if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] !== $comment['user_id']) {
    header("Location: index.php");
    exit();
}

$conn->query("DELETE FROM comments WHERE comment_id = $comment_id");

header("Location: post.php?id=$post_id");
exit();
?>

<?php
session_start();
include "includes/db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$comment_id = (int) $_GET['id'];
$post_id = (int) $_GET['post'];

$conn->query("DELETE FROM comments WHERE comment_id = $comment_id");

header("Location: post.php?id=$post_id");
exit();

<?php
session_start();
include "includes/db.php";

if ($_SESSION['role'] !== 'admin') exit;

$id = (int) $_GET['id'];
$conn->query("UPDATE blog_posts SET is_removed = IF(is_removed = 1, 0, 1) WHERE post_id = $id");
header("Location: admin.php");

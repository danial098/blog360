<?php
session_start();
include "includes/db.php";

if ($_SESSION['role'] !== 'admin') exit;

$id = (int) $_GET['id'];
$conn->query("UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE user_id = $id");
header("Location: admin.php");

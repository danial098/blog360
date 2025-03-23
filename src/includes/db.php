<?php
$conn = new mysqli("mysql", "root", "root", "BLOG");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
session_start();
include "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password_hash, role) VALUES (?, ?, ?, ?, 'registered')");
    $stmt->bind_param("ssss", $username, $full_name, $email, $password);

    if ($stmt->execute()) {
        echo "✅ Registration successful. <a href='login.php'>Login here</a>.";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>

<form method="POST">
    <h2>Register</h2>
    <input name="username" placeholder="Username" required><br>
    <input name="full_name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Register</button>
</form>

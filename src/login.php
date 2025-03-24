<?php
session_start();
include "includes/db.php";

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password_hash, role, full_name FROM users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $password_hash, $role, $full_name);
    $stmt->fetch();

    if (password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['role'] = $role;
        header("Location: index.php");
        exit();
    } else {
        $error = "❌ Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blog360</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0D0D1F;
            color: #FFFFFF;
        }
        .container {
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            background: #1A1A2E;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #2C2C44;
            color: white;
            box-sizing: border-box;
            margin-bottom: 15px;
        }
        button {
            background: #6200EA;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            font-size: 16px;
        }
        button:hover {
            background: #4527A0;
        }
        .error {
            color: #FF5252;
            margin-bottom: 20px;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #BB86FC;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .logo {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        header {
            background: #0D0D1F;
            padding: 20px;
            text-align: center;
        }
        header a {
            color: white;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php">Blog360</a>
    </header>

    <div class="container">
        <h1>Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
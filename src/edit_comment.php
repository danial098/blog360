<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$comment_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch comment
$stmt = $conn->prepare("SELECT content, post_id FROM comments WHERE comment_id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Unauthorized or comment not found.");
}
$comment = $result->fetch_assoc();
$stmt->close();

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_content = trim($_POST['content']);
    if (!empty($new_content)) {
        $update = $conn->prepare("UPDATE comments SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE comment_id = ?");
        $update->bind_param("si", $new_content, $comment_id);
        if ($update->execute()) {
            header("Location: post.php?id=" . $comment['post_id']);
            exit();
        } else {
            $message = "❌ Update failed.";
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Comment</title>
  <!-- Link your custom stylesheet that contains your provided CSS -->
  <link rel="stylesheet" href="styles.css">
  <!-- Additional styles for this page -->
  <style>
    /* Style textarea similar to the .input style in your CSS, but adjusted for a larger comment box */
    textarea {
      background: #ffffff;
      border-radius: 8px;
      border: 1px solid #d0d5dd;
      padding: 12px 16px;
      width: 100%;
      color: #090d1f;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      line-height: 1.5;
      resize: vertical;
    }
    /* Primary button style matching your accent color */
    .button-primary {
      background: #6941c6;
      color: #ffffff;
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .button-primary:hover {
      background: rgba(105, 65, 198, 0.5);
    }
    /* Secondary button style for cancel links */
    .button-secondary {
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .button-secondary:hover {
      background: rgba(105, 65, 198, 0.5);
    }
    /* A simple alert style for error messages */
    .alert {
      background: #ff4d4f;
      color: #ffffff;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    /* Container for the edit comment page */
    .edit-container {
      max-width: 600px;
      margin: 50px auto;
      padding: 0 32px;
    }
    .edit-header {
      color: #ffffff;
      font-family: "Inter-SemiBold", sans-serif;
      font-size: 24px;
      margin-bottom: 32px;
    }
  </style>
</head>
<body>
  <div class="edit-container">
      <h4 class="edit-header">Edit Your Comment</h4>
      <?php if ($message): ?>
          <div class="alert"><?= $message ?></div>
      <?php endif; ?>
      <form method="POST">
          <div class="mb-3">
              <textarea name="content" rows="4" required><?= htmlspecialchars($comment['content']) ?></textarea>
          </div>
          <div style="display: flex; gap: 16px;">
              <button type="submit" class="button-primary">Save Changes</button>
              <a href="post.php?id=<?= $comment['post_id'] ?>" class="button-secondary">Cancel</a>
          </div>
      </form>
  </div>
</body>
</html>

<?php
session_start();
include "includes/db.php";

// Fetch categories for the category filter
$categories = $conn->query("SELECT * FROM categories");

// Get filter inputs
$selected_category = isset($_GET['category']) ? (int) $_GET['category'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build SQL for blog posts
$sql = "
    SELECT p.*, u.full_name, COUNT(l.user_id) AS like_count
    FROM blog_posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN likes l ON l.post_id = p.post_id
";

// Add a join for category if a filter is selected
if ($selected_category) {
    $sql .= " JOIN post_categories pc ON p.post_id = pc.post_id ";
}

// Build conditions
$conditions = ["p.is_removed = 0"];
if ($selected_category) {
    $conditions[] = "pc.category_id = $selected_category";
}
if ($search_term !== '') {
    $safe = $conn->real_escape_string($search_term);
    $conditions[] = "(p.title LIKE '%$safe%' OR p.content LIKE '%$safe%')";
}
$sql .= " WHERE " . implode(" AND ", $conditions);
$sql .= " GROUP BY p.post_id ORDER BY p.created_at DESC";

// Execute the query
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Existing CSS files -->
  <link rel="stylesheet" href="./vars.css">
  <link rel="stylesheet" href="./style.css">
  <!-- New CSS file -->
  <link rel="stylesheet" href="./styles/index.css">
  <title>Blog360</title>
  <style>
   a,
   button,
   input,
   select,
   h1,
   h2,
   h3,
   h4,
   h5,
   * {
       box-sizing: border-box;
       margin: 0;
       padding: 0;
       border: none;
       text-decoration: none;
       background: none;
       -webkit-font-smoothing: antialiased;
   }
   menu, ol, ul {
       list-style-type: none;
       margin: 0;
       padding: 0;
   }
  </style>
</head>
<body>
  <header class="header">
    <div class="navbar">
      <a class="cosc-360" href="#">COSC 360</a>
      <nav class="menu">
        <a class="blog" href="#">
          <div class="frame-1000000803">
            <div class="blog2">Blog</div>
          </div>
        </a>
        <a class="about" href="#">
          <div class="about2">About</div>
        </a>
        <?php if (isset($_SESSION['username'])): ?>
          <a class="newsletter" href="logout.php">
            <div class="log-in">Log out</div>
          </a>
        <?php else: ?>
          <a class="newsletter" href="login.php">
            <div class="log-in">Log in</div>
          </a>
        <?php endif; ?>
        <div class="input">
          <form method="GET" action="index.php">
            <?php if ($selected_category): ?>
              <input type="hidden" name="category" value="<?= $selected_category ?>">
            <?php endif; ?>
            <input id="search-input" name="search" class="content6" type="search" placeholder="Search" value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit">Search</button>
          </form>
        </div>
      </nav>
    </div>
  </header>

  
  <div class="dark-mode">
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <h1 class="the-blog">THE BLOG</h1>
      </div>
    </section>

      <!-- Category Filter Section -->
  <section class="category-filter">
    <div class="container">
      <h4>📂 Filter by Category</h4>
      <a href="index.php" class="btn <?= !$selected_category ? 'active' : '' ?>">All</a>
      <?php while ($cat = $categories->fetch_assoc()): ?>
          <a href="index.php?category=<?= $cat['category_id'] ?>" class="btn <?= ($selected_category == $cat['category_id']) ? 'active' : '' ?>">
              <?= htmlspecialchars($cat['category_name']) ?>
          </a>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Hot Posts Section -->
  <section class="hot-posts">
    <div class="container">
      <h4>🔥 Hot Posts</h4>
      <?php
      $hot = $conn->query("
          SELECT p.post_id, p.title, COUNT(l.user_id) AS like_count 
          FROM blog_posts p 
          LEFT JOIN likes l ON l.post_id = p.post_id 
          WHERE p.is_removed = 0 
          GROUP BY p.post_id 
          ORDER BY like_count DESC 
          LIMIT 3
      ");
      while ($hp = $hot->fetch_assoc()):
      ?>
          <div class="hot-post">
              <a href="post.php?id=<?= $hp['post_id'] ?>" class="fw-bold">
                  <?= htmlspecialchars($hp['title']) ?>
              </a>
              <span>(❤️ <?= $hp['like_count'] ?>)</span>
          </div>
      <?php endwhile; ?>
    </div>
  </section>
    
    <main class="blog-page-header">
      <!-- Recent Blog Posts Section -->
      <section class="section">
        <div class="container2">
          <h2 class="heading">Recent blog posts</h2>
          <div class="content">
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <article class="blog-post-card">
                  <?php if (!empty($row['image'])): ?>
                    <img class="image" src="<?= htmlspecialchars($row['image']) ?>" alt="Post image" />
                  <?php endif; ?>
                  <div class="content2">
                    <div class="heading-and-text">
                      <time class="author" datetime="<?= htmlspecialchars($row['created_at']) ?>">
                        <?= date("l, j M Y", strtotime($row['created_at'])) ?>
                      </time>
                      <div class="heading-and-icon">
                        <h3 class="heading2">
                          <a href="post.php?id=<?= $row['post_id'] ?>">
                            <?= htmlspecialchars($row['title']) ?>
                          </a>
                        </h3>
                        <div class="icon-wrap">
                          <img class="arrow-up-right" src="images/arrow-up-right0.svg" alt="arrow" />
                        </div>
                      </div>
                      <p class="supporting-text">
                        <?= htmlspecialchars(substr($row['content'], 0, 150)) ?>...
                      </p>
                    </div>
                    <ul class="categories">
                      <!-- Optionally, display related categories -->
                    </ul>
                  </div>
                </article>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No posts found.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>
      
      <!-- All Blog Posts Section (structure placeholder) -->
      <section class="section2">
        <div class="container3">
          <div class="heading-and-content">
            <h2 class="heading">All blog posts</h2>
            <div class="content4">
              <div class="row">
                <!-- You can loop through another set of posts here if needed -->
                <p>All posts section coming soon.</p>
              </div>
              <div class="row">
                <nav class="pagination">
                  <a class="button" href="#">
                    <span class="button-base">
                      <img class="arrow-left" src="images/arrow-left0.svg" alt="Previous" />
                      <span class="text10">Previous</span>
                    </span>
                  </a>
                  <div class="pagination-numbers">
                    <a class="pagination-number-base" href="#">
                      <span class="content5">
                        <span class="number">1</span>
                      </span>
                    </a>
                    <!-- Add more pagination numbers as required -->
                  </div>
                  <a class="button" href="#">
                    <span class="button-base">
                      <span class="text10">Next</span>
                      <img class="arrow-right" src="images/arrow-right0.svg" alt="Next" />
                    </span>
                  </a>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
      <div class="container4">
        <nav class="menu">
          <a class="twitter" href="#">Twitter</a>
          <a class="linked-in" href="#">LinkedIn</a>
          <a class="email" href="#">Email</a>
          <a class="rss-feed" href="#">RSS feed</a>
          <a class="add-to-feedly" href="#">Add to Feedly</a>
        </nav>
      </div>
    </footer>
  </div>
</body>
</html>

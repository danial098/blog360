CREATE DATABASE IF NOT EXISTS BLOG;
USE BLOG;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('registered', 'admin') NOT NULL DEFAULT 'registered',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  profile_image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog Posts Table (adjusted to include an 'image' field)
CREATE TABLE IF NOT EXISTS blog_posts (
  post_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  is_removed TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
  comment_id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES blog_posts(post_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Likes Table
CREATE TABLE IF NOT EXISTS likes (
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(post_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Post Categories Table
CREATE TABLE IF NOT EXISTS post_categories (
  post_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (post_id, category_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(post_id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Insert sample data into users
INSERT INTO users (username, full_name, email, password_hash, role, profile_image)
VALUES
  ('admin1', 'Admin User', 'admin@example.com', '$2y$10$lj9cbtWMvcU5nMIqAOodPuHI6CuZykwerAQDYIttJ.KcoKrNktgOG', 'admin', '/images/admin1.png'),
  ('johndoe', 'John Doe', 'john@example.com', 'dummyhashjohn', 'registered', '/images/johndoe.png'),
  ('janedoe', 'Jane Doe', 'jane@example.com', 'dummyhashjane', 'registered', '/images/janedoe.png');

-- Insert sample data into blog_posts (including the image field)
INSERT INTO blog_posts (user_id, title, content, image, is_removed)
VALUES
  (2, 'My First Blog Post', 'This is the content of my first blog post.', '/images/post1.png', 0),
  (3, 'Traveling the World', 'Sharing my travel experiences around the globe.', '/images/post2.png', 0),
  (2, 'Tech Trends 2025', 'An overview of upcoming technology trends in 2025.', '/images/post3.png', 0);

-- Insert sample data into comments
INSERT INTO comments (post_id, user_id, content)
VALUES
  (1, 3, 'Great post, really enjoyed reading it!'),
  (1, 2, 'Thank you for your comment!'),
  (2, 2, 'Interesting insights on travel.'),
  (3, 3, 'Looking forward to more tech posts!');

-- Insert sample data into likes
INSERT INTO likes (post_id, user_id)
VALUES
  (1, 2),
  (1, 3),
  (2, 2),
  (2, 3),
  (3, 2);

-- Insert sample data into categories
INSERT INTO categories (category_name)
VALUES
  ('Lifestyle'),
  ('Travel'),
  ('Technology');

-- Insert sample data into post_categories
INSERT INTO post_categories (post_id, category_id)
VALUES
  (1, 1),
  (2, 2),
  (3, 3);

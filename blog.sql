-- Users table: holds registered and admin user details.
CREATE TABLE users (
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

-- Blog posts table: stores blog posts created by users.
CREATE TABLE blog_posts (
  post_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  is_removed TINYINT(1) NOT NULL DEFAULT 0,  -- Allows for soft deletion by admins
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Comments table: holds comments made on blog posts.
CREATE TABLE comments (
  comment_id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES blog_posts(post_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Likes table: ensures a user can like a post only once.
CREATE TABLE likes (
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(post_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Categories table: for organizing blog posts into topics.
CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Join table for a many-to-many relationship between blog posts and categories.
CREATE TABLE post_categories (
  post_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (post_id, category_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(post_id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Testing Initial Functionality With Dummy Data:

-- Insert dummy users (admin and registered users)
INSERT INTO users (username, full_name, email, password_hash, role, profile_image)
VALUES
  ('admin1', 'Admin User', 'admin@example.com', '$2y$10$lj9cbtWMvcU5nMIqAOodPuHI6CuZykwerAQDYIttJ.KcoKrNktgOG', 'admin', '/images/admin1.png'),
  ('johndoe', 'John Doe', 'john@example.com', '$2y$10$GoTZZyl/RSsQ1GrZ3z4pgesq9VNSg56TsJ2xnf5a7QuHm3HFn4YJi', 'registered', '/images/johndoe.png'),
  ('janedoe', 'Jane Doe', 'jane@example.com', 'dummyhashjane', 'registered', '/images/janedoe.png');

-- Insert dummy blog posts (each linked to a user)
INSERT INTO blog_posts (user_id, title, content, is_removed)
VALUES
  (2, 'My First Blog Post', 'This is the content of my first blog post.', 0),
  (3, 'Traveling the World', 'Sharing my travel experiences around the globe.', 0),
  (2, 'Tech Trends 2025', 'An overview of upcoming technology trends in 2025.', 0);

-- Insert dummy comments (linked to posts and users)
INSERT INTO comments (post_id, user_id, content)
VALUES
  (1, 3, 'Great post, really enjoyed reading it!'),
  (1, 2, 'Thank you for your comment!'),
  (2, 2, 'Interesting insights on travel.'),
  (3, 3, 'Looking forward to more tech posts!');

-- Insert dummy likes (each user can like a post only once)
INSERT INTO likes (post_id, user_id)
VALUES
  (1, 2),
  (1, 3),
  (2, 2),
  (2, 3),
  (3, 2);

-- Insert dummy categories for posts
INSERT INTO categories (category_name)
VALUES
  ('Lifestyle'),
  ('Travel'),
  ('Technology');

-- Insert dummy data into post_categories to associate posts with categories
INSERT INTO post_categories (post_id, category_id)
VALUES
  (1, 1), -- Post 1 is in the 'Lifestyle' category
  (2, 2), -- Post 2 is in the 'Travel' category
  (3, 3); -- Post 3 is in the 'Technology' category

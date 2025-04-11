<?php

use PHPUnit\Framework\TestCase;

class BlogPostTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli(
            getenv('DB_HOST') ?: 'mysql',
            getenv('DB_USERNAME') ?: 'root',
            getenv('DB_PASSWORD') ?: 'root',
            getenv('DB_DATABASE') ?: 'BLOG_TEST'
        );
        
        // Create test tables if they don't exist
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                profile_image VARCHAR(255),
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS blog_posts (
                post_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                image VARCHAR(255),
                is_removed TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
            )
        ");
        
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS categories (
                category_id INT AUTO_INCREMENT PRIMARY KEY,
                category_name VARCHAR(50) NOT NULL
            )
        ");
        
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS post_categories (
                post_id INT NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY (post_id, category_id),
                FOREIGN KEY (post_id) REFERENCES blog_posts(post_id),
                FOREIGN KEY (category_id) REFERENCES categories(category_id)
            )
        ");
        
        // Add test user and categories
        $this->conn->query("INSERT IGNORE INTO users (user_id, username, password_hash, full_name, email) 
                            VALUES (999, 'testuser', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Test User', 'test@example.com')");
        $this->conn->query("INSERT IGNORE INTO categories (category_id, category_name) VALUES (1, 'Test Category')");
        
        // Clean up test posts
        $this->conn->query("DELETE FROM post_categories WHERE post_id IN (SELECT post_id FROM blog_posts WHERE title = 'Test Post')");
        $this->conn->query("DELETE FROM blog_posts WHERE title = 'Test Post'");
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->conn->query("DELETE FROM post_categories WHERE post_id IN (SELECT post_id FROM blog_posts WHERE title = 'Test Post')");
        $this->conn->query("DELETE FROM blog_posts WHERE title = 'Test Post'");
        $this->conn->close();
    }

    public function testCreatePost()
    {
        $user_id = 999;
        $title = 'Test Post';
        $content = 'This is a test post content.';
        
        $stmt = $this->conn->prepare("
            INSERT INTO blog_posts (user_id, title, content) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $user_id, $title, $content);
        $result = $stmt->execute();
        $post_id = $this->conn->insert_id;
        $stmt->close();
        
        $this->assertTrue($result, 'Failed to create blog post');
        $this->assertGreaterThan(0, $post_id, 'No post ID returned');
        
        // Add categories
        $category_id = 1;
        $stmt = $this->conn->prepare("
            INSERT INTO post_categories (post_id, category_id) 
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $post_id, $category_id);
        $result = $stmt->execute();
        $stmt->close();
        
        $this->assertTrue($result, 'Failed to add category to post');
        
        // Fetch and verify post
        $stmt = $this->conn->prepare("SELECT * FROM blog_posts WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();
        
        $this->assertEquals($title, $post['title'], 'Post title does not match');
        $this->assertEquals($content, $post['content'], 'Post content does not match');
    }

    public function testPostCategories()
    {
        // Create a post
        $user_id = 999;
        $title = 'Test Post';
        $content = 'This is a test post content.';
        
        $stmt = $this->conn->prepare("
            INSERT INTO blog_posts (user_id, title, content) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();
        $post_id = $this->conn->insert_id;
        $stmt->close();
        
        // Add category
        $category_id = 1;
        $stmt = $this->conn->prepare("
            INSERT INTO post_categories (post_id, category_id) 
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $post_id, $category_id);
        $stmt->execute();
        $stmt->close();
        
        // Get posts by category
        $stmt = $this->conn->prepare("
            SELECT p.* FROM blog_posts p 
            JOIN post_categories pc ON p.post_id = pc.post_id 
            WHERE pc.category_id = ?
        ");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $found = false;
        
        while ($row = $result->fetch_assoc()) {
            if ($row['post_id'] == $post_id) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue($found, 'Post not found in category');
    }
} 
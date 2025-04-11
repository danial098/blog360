<?php

use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
    private $conn;
    private $user_id = 999;
    private $post_id;

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
            CREATE TABLE IF NOT EXISTS likes (
                user_id INT NOT NULL,
                post_id INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, post_id),
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (post_id) REFERENCES blog_posts(post_id)
            )
        ");
        
        // Add test user if not exists
        $this->conn->query("INSERT IGNORE INTO users (user_id, username, password_hash, full_name, email) 
                            VALUES (999, 'testuser', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Test User', 'test@example.com')");
        
        // Create test post
        $this->conn->query("INSERT INTO blog_posts (user_id, title, content) 
                           VALUES (999, 'Test Like Post', 'This is a test post for likes')");
        $this->post_id = $this->conn->insert_id;
        
        // Clean up any likes
        $this->conn->query("DELETE FROM likes WHERE user_id = 999");
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->conn->query("DELETE FROM likes WHERE post_id = {$this->post_id}");
        $this->conn->query("DELETE FROM blog_posts WHERE post_id = {$this->post_id}");
        $this->conn->close();
    }

    public function testLikePost()
    {
        // Test adding a like
        $stmt = $this->conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $this->user_id, $this->post_id);
        $result = $stmt->execute();
        $stmt->close();
        
        $this->assertTrue($result, 'Failed to like post');
        
        // Verify like exists
        $stmt = $this->conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $this->user_id, $this->post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $like = $result->fetch_assoc();
        $stmt->close();
        
        $this->assertNotNull($like, 'Like not found after creation');
    }

    public function testUnlikePost()
    {
        // Add a like first
        $stmt = $this->conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $this->user_id, $this->post_id);
        $stmt->execute();
        $stmt->close();
        
        // Now test unlike (delete)
        $stmt = $this->conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $this->user_id, $this->post_id);
        $result = $stmt->execute();
        $stmt->close();
        
        $this->assertTrue($result, 'Failed to unlike post');
        
        // Verify like is gone
        $stmt = $this->conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $this->user_id, $this->post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $like = $result->fetch_assoc();
        $stmt->close();
        
        $this->assertNull($like, 'Like still exists after deletion');
    }

    public function testLikeCount()
    {
        // Add 3 different likes
        $this->conn->query("INSERT IGNORE INTO users (user_id, username, password_hash, full_name, email) 
                           VALUES (1000, 'user1', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'User 1', 'user1@example.com'),
                                  (1001, 'user2', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'User 2', 'user2@example.com'),
                                  (1002, 'user3', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'User 3', 'user3@example.com')");
        
        $this->conn->query("INSERT INTO likes (user_id, post_id) VALUES 
                            (1000, {$this->post_id}),
                            (1001, {$this->post_id}),
                            (1002, {$this->post_id})");
        
        // Get like count
        $result = $this->conn->query("SELECT COUNT(*) as count FROM likes WHERE post_id = {$this->post_id}");
        $count = $result->fetch_assoc()['count'];
        
        $this->assertEquals(3, $count, 'Like count is not correct');
    }
} 
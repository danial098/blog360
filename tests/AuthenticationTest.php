<?php

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
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
        
        // Create test users table if it doesn't exist
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
        
        // Clear test data
        $this->conn->query("DELETE FROM users WHERE username = 'testuser'");
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE username = 'testuser'");
        $this->conn->close();
    }

    public function testUserRegistration()
    {
        // Mock registration process
        $username = 'testuser';
        $full_name = 'Test User';
        $email = 'test@example.com';
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password_hash, full_name, email) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $username, $password, $full_name, $email);
        $result = $stmt->execute();
        $stmt->close();
        
        $this->assertTrue($result, 'User registration failed');
        
        // Check if user exists
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        $this->assertNotNull($user, 'User not found after registration');
        $this->assertEquals($full_name, $user['full_name'], 'Full name does not match');
    }

    public function testUserLogin()
    {
        // Create a test user
        $username = 'testuser';
        $full_name = 'Test User';
        $email = 'test@example.com';
        $plaintext_password = 'password123';
        $password = password_hash($plaintext_password, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password_hash, full_name, email) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $username, $password, $full_name, $email);
        $stmt->execute();
        $stmt->close();
        
        // Test login logic
        $stmt = $this->conn->prepare(
            "SELECT user_id, password_hash, role, full_name FROM users WHERE username = ? AND is_active = 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id, $password_hash, $role, $returned_full_name);
        $found = $stmt->fetch();
        $stmt->close();
        
        $this->assertTrue($found, 'User not found during login');
        $this->assertTrue(
            password_verify($plaintext_password, $password_hash), 
            'Password verification failed'
        );
    }
} 
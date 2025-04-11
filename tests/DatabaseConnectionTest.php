<?php

use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        $this->conn->close();
    }

    public function testDatabaseConnection()
    {
        $this->assertEmpty($this->conn->connect_error, 'Database connection failed');
        $this->assertTrue($this->conn->ping(), 'Database ping failed');
    }
} 
# Blog System Test Documentation

This document provides an overview of the automated test suite for the blog system. These tests ensure the proper functioning of various components and features of the application.

---

## Table of Contents

1. [Test Overview](#test-overview)
2. [Test Details](#test-details)
   - [Database Connection Test](#21-database-connection-test)
   - [Authentication Tests](#22-authentication-tests)
   - [Blog Post Tests](#23-blog-post-tests)
   - [Like System Tests](#24-like-system-tests)

---

## 1. Test Overview

Below is an overview table detailing each test class, its purpose, and the number of tests included.

| **Test Class**             | **Purpose**                                           | **Tests** |
|----------------------------|-------------------------------------------------------|-----------|
| DatabaseConnectionTest     | Validates database connectivity                       | 1         |
| AuthenticationTest         | Tests user authentication functionality               | 2         |
| BlogPostTest               | Verifies blog post creation and category management   | 2         |
| LikeTest                   | Tests the post liking/unliking system                  | 3         |

---

## 2. Test Details

### 2.1 Database Connection Test
**File:** `tests/DatabaseConnectionTest.php`

#### Test Details Table:

| **Test Method**            | **Description**                                                                                    | **Assertions** |
|----------------------------|----------------------------------------------------------------------------------------------------|----------------|
| `testDatabaseConnection()` | Verifies that the application can connect to the test database and perform basic operations.     | 2              |

**Key Validations:**

- The database connection is established without errors.
- A successful ping request is made to the database.

---

### 2.2 Authentication Tests
**File:** `tests/AuthenticationTest.php`

#### Test Details Table:

| **Test Method**           | **Description**                                                        | **Assertions** |
|---------------------------|------------------------------------------------------------------------|----------------|
| `testUserRegistration()`  | Tests that a new user can be registered in the system.                | ~2             |
| `testUserLogin()`         | Verifies that users can authenticate with valid credentials.          | ~2             |

**Key Validations:**

- User registration functionality.
- Credential verification during login.
- Proper handling of user data in the database.

---

### 2.3 Blog Post Tests
**File:** `tests/BlogPostTest.php`

#### Test Details Table:

| **Test Method**       | **Description**                                               | **Assertions** |
|-----------------------|---------------------------------------------------------------|----------------|
| `testCreatePost()`    | Tests the creation of new blog posts.                         | ~3             |
| `testPostCategories()`| Verifies that posts can be assigned to categories.            | ~3             |

**Key Validations:**

- Blog posts can be created with all required information.
- Posts can be assigned to categories.
- The database properly stores post content and metadata.

---

### 2.4 Like System Tests
**File:** `tests/LikeTest.php`

#### Test Details Table:

| **Test Method**       | **Description**                                                          | **Assertions** |
|-----------------------|--------------------------------------------------------------------------|----------------|
| `testLikePost()`      | Tests that users can like posts.                                         | ~2             |
| `testUnlikePost()`    | Verifies that users can unlike posts they previously liked.              | ~2             |
| `testLikeCount()`     | Tests that the like count is accurately tracked.                       | ~3             |

**Key Validations:**

- Users can like posts.
- Users can remove their likes from posts.
- The system correctly tracks and displays the number of likes on posts.

---

This structured layout organizes the documentation into clear sections while highlighting each test class and its purpose. You can further enhance the formatting by applying Markdown-specific styling or converting the document into other formats if needed.

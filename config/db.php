<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Your MySQL username
define('DB_PASS', '954787');  // Your MySQL password
define('DB_NAME', 'task_manager');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
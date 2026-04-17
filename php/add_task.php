<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'] ?? 'pending';
    
    // Validation
    if (empty($title)) {
        $_SESSION['error'] = "Task title is required";
        header("Location: ../add_task.html");
        exit();
    }
    
    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        $status = 'pending';
    }
    
    // Insert task
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Task created successfully!";
        header("Location: ../dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to create task. Please try again.";
        header("Location: ../add_task.html");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>
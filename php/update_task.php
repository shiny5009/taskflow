<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $task_id = $_POST['task_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'] ?? 'pending';
    
    // Validation
    if (empty($title)) {
        $_SESSION['error'] = "Task title is required";
        header("Location: ../edit_task.php?id=" . $task_id);
        exit();
    }
    
    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        $status = 'pending';
    }
    
    // Update task (ensure user owns the task)
    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $title, $description, $status, $task_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['success'] = "Task updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update task or no changes made.";
    }
    
    header("Location: ../dashboard.php");
    exit();
    
    $stmt->close();
}

$conn->close();
?>
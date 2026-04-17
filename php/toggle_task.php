<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.html");
    exit();
}

$task_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get current status
$stmt = $conn->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $task = $result->fetch_assoc();
    $new_status = ($task['status'] === 'pending') ? 'completed' : 'pending';
    
    // Update status
    $update_stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $new_status, $task_id, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Task status updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update task status.";
    }
    
    $update_stmt->close();
} else {
    $_SESSION['error'] = "Task not found.";
}

$stmt->close();
$conn->close();

header("Location: ../dashboard.php");
exit();
?>
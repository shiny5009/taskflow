<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.html");
    exit();
}

$task_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Delete task (ensure user owns the task)
$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['success'] = "Task deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete task or task not found.";
}

header("Location: ../dashboard.php");
exit();

$stmt->close();
$conn->close();
?>
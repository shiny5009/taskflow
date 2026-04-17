<?php
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

// Get task ID
$task_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch task details
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Task not found";
    header("Location: dashboard.php");
    exit();
}

$task = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - TaskFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .page-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-card {
            max-width: 600px;
            width: 100%;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: white;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .form-actions .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <div class="page-container">
        <div class="form-card glass-card">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            
            <h2 class="title text-center">Edit Task</h2>
            <p class="subtitle text-center">Update your task details</p>

            <form action="php/update_task.php" method="POST">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                
                <div class="form-group">
                    <label for="title">Task Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="Enter task title" 
                           value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" 
                              placeholder="Enter task description (optional)" 
                              rows="6"><?php echo htmlspecialchars($task['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
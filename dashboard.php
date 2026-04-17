<?php
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all tasks for the user
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count statistics
$total_tasks = count($tasks);
$pending_tasks = count(array_filter($tasks, fn($t) => $t['status'] === 'pending'));
$completed_tasks = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaskFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-text h1 {
            font-size: 32px;
            color: white;
            margin-bottom: 5px;
        }

        .welcome-text p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-number {
            font-size: 42px;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .tasks-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: white;
        }

        .task-list {
            display: grid;
            gap: 15px;
        }

        .task-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .task-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .task-card.completed::before {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .task-card:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.12);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .task-title {
            font-size: 18px;
            font-weight: 600;
            color: white;
            flex: 1;
        }

        .task-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .task-status.pending {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }

        .task-status.completed {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .task-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .task-date {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 20px;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 72px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .header-actions .btn {
                width: 100%;
            }
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

    <div class="container">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
                <p>Here's what's happening with your tasks today</p>
            </div>
            <div class="header-actions">
                <a href="add_task.html" class="btn btn-success">+ New Task</a>
                <a href="php/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_tasks; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_tasks; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $completed_tasks; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div class="tasks-section">
            <div class="section-header">
                <h2 class="section-title">Your Tasks</h2>
            </div>

            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <div class="empty-text">No tasks yet! Start by creating your first task.</div>
                    <a href="add_task.html" class="btn btn-primary">Create Task</a>
                </div>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card <?php echo $task['status']; ?>">
                            <div class="task-header">
                                <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                <span class="task-status <?php echo $task['status']; ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($task['description'])): ?>
                                <div class="task-description">
                                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="task-footer">
                                <div class="task-date">
                                    Created: <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                </div>
                                <div class="task-actions">
                                    <?php if ($task['status'] === 'pending'): ?>
                                        <a href="php/toggle_task.php?id=<?php echo $task['id']; ?>" class="btn btn-success btn-sm">✓ Complete</a>
                                    <?php else: ?>
                                        <a href="php/toggle_task.php?id=<?php echo $task['id']; ?>" class="btn btn-secondary btn-sm">↺ Reopen</a>
                                    <?php endif; ?>
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="php/delete_task.php?id=<?php echo $task['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this task?')" 
                                       class="btn btn-danger btn-sm">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
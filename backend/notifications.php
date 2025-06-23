<?php
session_start();
include 'db.php';
include 'includes/navbar.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark all notifications as seen
$update_seen = $conn->prepare("UPDATE notifications SET is_seen = 1 WHERE user_id = ?");
$update_seen->bind_param("i", $user_id);
$update_seen->execute();
$update_seen->close();

// 🔔 Fetch notifications including optional link
$stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SkillSwap | Notifications</title>
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
        body {
            background: #fdf6fa;
            font-family: 'Poppins', sans-serif;
        }

        .notifications-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 1.5rem;
            background: #fff0f5;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .btn-link {
  display: inline-block;
  margin-top: 6px;
  padding: 5px 10px;
  background-color: #e91e63;
  color: white;
  font-size: 0.8rem;
  border-radius: 6px;
  text-decoration: none;
}

.btn-link:hover {
  background-color: #c2185b;
}

        .notification {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
            color: #444;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .notification:last-child {
            border-bottom: none;
        }

        .message-text {
            flex: 1;
            margin-right: 1rem;
        }

        .timestamp {
            font-size: 0.75rem;
            color: #888;
            white-space: nowrap;
            margin-right: 1rem;
        }

        .view-btn {
            background: #e91e63;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .view-btn:hover {
            background: #c2185b;
        }

        h2 {
            color: #e91e63;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="notifications-container">
    <h2>🔔 Your Notifications</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="notification">
                <div class="message-text">
                    <?= htmlspecialchars($row['message']) ?>
                </div>

                <div class="timestamp">
                    🕒 <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                </div>

                <a href="requests.php" class="view-btn">→ Go to Request</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:#777;">No notifications yet. Start connecting to get updates! 🌱</p>
    <?php endif; ?>
</div>


</body>
</html>

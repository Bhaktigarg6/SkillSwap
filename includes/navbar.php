<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';

// Count unseen notifications
$unseen_count = 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_seen = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $unseen_count = $data['count'];
}
?>

<!-- Header Navbar with Navigation -->
<header class="navbar">
    <div class="left">
        <img src="images/logo.png" alt="SkillSwap Logo" class="logo">
        <div class="site-title">SkillSwap</div>
    </div>

    <div class="right">
        <div class="nav-links">
            <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">🏠 Home</a>
            <a href="profile.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">👤 Profile</a>
            <a href="browse.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'browse.php' ? 'active' : '' ?>">🔍 Browse</a>
            <a href="requests.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'requests.php' ? 'active' : '' ?>">📨 Requests</a>
            <a href="notifications.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>" style="position: relative;">
                🔔 Notifications
                <?php if ($unseen_count > 0): ?>
                    <span style="
                        position: absolute;
                        top: 5px;
                        right: -10px;
                        width: 10px;
                        height: 10px;
                        background: red;
                        border-radius: 50%;
                        display: inline-block;
                    "></span>
                <?php endif; ?>
            </a>
            <a href="leaderboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : '' ?>">🏆 Leaderboard</a>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
</header>

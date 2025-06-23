<?php
session_start();

// ⛔ Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    include 'match_logic.php'; // move the matching logic here
    header('Content-Type: application/json');
    echo json_encode($matches);
    exit();
}

// 🔒 Prevent back-button access after logout (disable caching)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

include 'db.php';

$user_id = $_SESSION["user_id"];

// ✅ Fetch user data (name, pic, skills)
$stmt = $conn->prepare("SELECT name, profile_pic, teach_skills, learn_skills FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $full_name = $row['name'];
    $profile_pic = $row['profile_pic'] ?: 'images/default.png';
    $teach_skills = $row['teach_skills'] ?: 'Not set';
    $learn_skills = $row['learn_skills'] ?: 'Not set';
} else {
    $full_name = "User";
    $profile_pic = "images/default.png";
    $teach_skills = "Not set";
    $learn_skills = "Not set";
}
// 🔍 Suggested Matches
$matches = [];
$sent_stmt = $conn->prepare("SELECT receiver_id, status FROM requests WHERE sender_id = ?");
$sent_stmt->bind_param("i", $user_id);
$sent_stmt->execute();
$sent_result = $sent_stmt->get_result();

$sent_status = [];  // key = receiver_id, value = status (Pending/Accepted/Rejected)
while ($row = $sent_result->fetch_assoc()) {
    $sent_status[$row['receiver_id']] = $row['status'];
}
// Split current user's skills
$myTeach = array_map('trim', explode(',', strtolower($teach_skills)));
$myLearn = array_map('trim', explode(',', strtolower($learn_skills)));

// Get all other users
$all_stmt = $conn->prepare("SELECT id, name, profile_pic, teach_skills, learn_skills FROM users WHERE id != ?");
$all_stmt->bind_param("i", $user_id);
$all_stmt->execute();
$all_result = $all_stmt->get_result();

while ($row = $all_result->fetch_assoc()) {
    $theirTeach = array_map('trim', explode(',', strtolower($row['teach_skills'])));
    $theirLearn = array_map('trim', explode(',', strtolower($row['learn_skills'])));

    $matchFound = false;

    // 💥 If they want to learn what I teach
    foreach ($myTeach as $skill) {
        if (in_array($skill, $theirLearn)) {
            $matchFound = true;
            break;
        }
    }

    // 💥 Or they can teach what I want to learn
    if (!$matchFound) {
        foreach ($myLearn as $skill) {
            if (in_array($skill, $theirTeach)) {
                $matchFound = true;
                break;
            }
        }
    }

    if ($matchFound) {
        $matches[] = $row;
    }
}

// 📨 Request Activity Summary Counts
// 1. Sent Requests Count
$sent_count_stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE sender_id = ?");
$sent_count_stmt->bind_param("i", $user_id);
$sent_count_stmt->execute();
$sent_count_stmt->bind_result($sent_count);
$sent_count_stmt->fetch();
$sent_count_stmt->close();

// 2. Received Requests Count
$received_count_stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE receiver_id = ?");
$received_count_stmt->bind_param("i", $user_id);
$received_count_stmt->execute();
$received_count_stmt->bind_result($received_count);
$received_count_stmt->fetch();
$received_count_stmt->close();

// 3. Accepted Exchanges
$accepted_count_stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE (sender_id = ? OR receiver_id = ?) AND status = 'Accepted'");
$accepted_count_stmt->bind_param("ii", $user_id, $user_id);
$accepted_count_stmt->execute();
$accepted_count_stmt->bind_result($accepted_count);
$accepted_count_stmt->fetch();
$accepted_count_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SkillSwap | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
#chatbot-button {
    position: fixed !important;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background-color: #9c27b0;
    color: white;
    font-size: 30px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    user-select: none;
    transition: background-color 0.3s ease;
}

#chatbot-button:hover {
    background-color: #7b1fa2;
}
</style>


</head>
<?php
// Fetch unseen notification count
$notif_sql = "SELECT COUNT(*) AS unseen_count FROM notifications WHERE user_id = ? AND is_seen = 0";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $_SESSION['user_id']);
$notif_stmt->execute();
$notif_stmt->bind_result($unseen_count);
$notif_stmt->fetch();
$notif_stmt->close();
?>

<body class="dashboard-body">

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

        <!-- 👇 Add Leaderboard link here -->
        <a href="leaderboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : '' ?>">🏆 Leaderboard</a>

        <a href="logout.php" class="logout-btn">🚪 Logout</a>
    </div>
</header>


<!-- Dashboard Content -->
<div class="dashboard">

  <!-- Profile Welcome Card -->
<div class="profile-card" style="
    display: flex; 
    align-items: center; 
    gap: 1.5rem; 
    margin-bottom: 2rem; 
    padding: 2rem; 
    background: linear-gradient(135deg, #ffe0ec, #e0f7fa); 
    border-radius: 1.5rem; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
">
    <!-- Cute pastel blob shape in background -->
    <div style="
        position: absolute;
        top: -40px;
        right: -40px;
        width: 150px;
        height: 150px;
        background: #f8a5c2;
        opacity: 0.2;
        border-radius: 50%;
        z-index: 0;
    "></div>

    <!-- Profile Upload -->
    <form id="uploadForm" action="upload_profile_pic.php" method="POST" enctype="multipart/form-data" style="margin: 0; z-index: 1;">
        <!-- Hidden File Input -->
        <input type="file" name="profile_pic" id="profileInput" accept="image/*" style="display: none;" onchange="document.getElementById('uploadForm').submit();">

        <!-- Clickable Profile Pic OR Placeholder -->
    <!-- Clickable Profile Pic OR Placeholder -->
<label for="profileInput" style="cursor: pointer;">
    <?php if (!empty($profile_pic) && file_exists($profile_pic)): ?>
        <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile Picture"
            style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; aspect-ratio: 1 / 1; border: 2px solid #f8a5c2;">
    <?php else: ?>

      
<!-- Test Avatar (no PHP, just to confirm it's visible) -->
<div style="
    width: 100px; 
    height: 100px; 
    border-radius: 50%; 
    background: #ffd5ec; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 2.5rem; 
    border: 2px solid #f8a5c2;
    color: #fff;
">
    😎
</div>


    <?php endif; ?>
</label>

    </form>

    <!-- Welcome Text -->
    <div class="profile-info" style="z-index: 1;">
        <h2 style="margin: 0 0 0.5rem 0; font-size: 1.8rem;">Hey <?= htmlspecialchars($full_name); ?>! 😊</h2>
        <p style="margin: 0.2rem 0;">Ready to swap some skills today?</p>
        <p style="margin: 0.5rem 0 0 0;"><strong>Teaching:</strong> <?= htmlspecialchars($teach_skills); ?></p>
        <p style="margin: 0;"><strong>Learning:</strong> <?= htmlspecialchars($learn_skills); ?></p>
    </div>
</div>
    <!-- Remove Profile Pic Button -->
    <form action="remove_profile_pic.php" method="POST" style="margin-top: 0.5rem;">
        <button type="submit" name="remove_pic" style="
            padding: 6px 12px;
            background: #f76c6c;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
        ">🗑️ Remove Picture</button>
    </form>
<!-- Your Skills Section -->
<div class="skills-section" style="margin-bottom: 2rem;">
    <h2 style="color: #e91e63; margin-bottom: 1rem;">Your Skills</h2>

    <form action="update_skills.php" method="POST" id="skillsForm">
        <div class="cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <!-- Teach Skills Card -->
            <div class="card" style="background: #fff0f5; padding: 1.5rem; border-radius: 1rem;">
                <h3 style="color: #e91e63;">Skills I can teach</h3>
                <p id="teachSkillsText" style="color: #555;">
                    <?= !empty($teach_skills) && $teach_skills !== 'Not set'
                        ? htmlspecialchars($teach_skills)
                        : '<em style="color:#999;">Enter your Skills</em>'; ?>
                </p>
                <textarea name="teach_skills" id="teachSkillsInput" placeholder="Enter your Skills" style="display:none; width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid #ccc;"><?= htmlspecialchars($teach_skills !== 'Not set' ? $teach_skills : '') ?></textarea>
            </div>

            <!-- Learn Skills Card -->
            <div class="card" style="background: #fff0f5; padding: 1.5rem; border-radius: 1rem;">
                <h3 style="color: #e91e63;">Skills I want to learn</h3>
                <p id="learnSkillsText" style="color: #555;">
                    <?= !empty($learn_skills) && $learn_skills !== 'Not set'
                        ? htmlspecialchars($learn_skills)
                        : '<em style="color:#999;">Enter the skills you want to learn</em>'; ?>
                </p>
                <textarea name="learn_skills" id="learnSkillsInput" placeholder="Enter the skills you want to learn" style="display:none; width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid #ccc;"><?= htmlspecialchars($learn_skills !== 'Not set' ? $learn_skills : '') ?></textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: right; margin-top: 1rem;">
            <button type="button" onclick="toggleEdit()" id="editBtn" style="padding: 0.6rem 1.2rem; background: #f8a5c2; color: white; border-radius: 12px; border: none; cursor: pointer;">✏️ Add/Edit Skills</button>
            <button type="submit" id="saveBtn" style="display:none; padding: 0.6rem 1.2rem; background: #f8a5c2; color: white; border-radius: 12px; border: none; cursor: pointer;">💾 Save</button>
        </div>

        <!-- 📊 Request Activity Summary -->
<div class="activity-summary" style="margin-bottom: 2rem;">
    <h2 style="color: #e91e63; margin-bottom: 1rem;">Request Activity</h2>
    <div class="cards" style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <!-- Sent -->
        <div style="flex: 1; min-width: 180px; background: #e0f7fa; padding: 1.2rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #00796b;">📨 Sent</h3>
            <p style="font-size: 2rem; margin: 0.5rem 0; color: #00796b;"><?= $sent_count; ?></p>
        </div>

        <!-- Received -->
        <div style="flex: 1; min-width: 180px; background: #fff3e0; padding: 1.2rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #ef6c00;">📥 Received</h3>
            <p style="font-size: 2rem; margin: 0.5rem 0; color: #ef6c00;"><?= $received_count; ?></p>
        </div>

        <!-- Accepted -->
        <div style="flex: 1; min-width: 180px; background: #f3e5f5; padding: 1.2rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #8e24aa;">✅ Accepted</h3>
            <p style="font-size: 2rem; margin: 0.5rem 0; color: #8e24aa;"><?= $accepted_count; ?></p>
        </div>
    </div>
</div>

    </form>
    <?php if (!empty($profile_pic) && $profile_pic !== 'images/default.png' && file_exists($profile_pic)): ?>
<?php endif; ?>

</div>

    <!-- Dashboard Cards -->
    <div class="cards">
        <div class="card">
            <h3>Your Skills</h3>
            <p>View and manage the skills you can teach or want to learn.</p>
        </div>
        <div class="card">
            <h3>Find Matches</h3>
            <p>Explore potential swap partners based on your interests.</p>
        </div>
        <div class="card">
            <h3>Swap Requests</h3>
            <p>Check incoming/outgoing requests and their status.</p>
        </div>
        <div class="card">
            <h3>Stats & Progress</h3>
            <p>Track your learning journey and completed swaps.</p>
        </div>
    </div>
</div>

<!-- Suggested Matches -->
<div class="suggested-matches-section">
    <h2>Suggested Matches</h2>
    <div class="cards">
        <?php if (empty($matches)): ?>
            <p>No suggested matches found right now. Try updating your skills!</p>
        <?php else: ?>
            <?php foreach ($matches as $match): 
                $already_sent = array_key_exists($match['id'], $sent_status);
                $status = $already_sent ? $sent_status[$match['id']] : "";
            ?>
                <div class="card match-card">
                    <img src="<?= htmlspecialchars($match['profile_pic'] ?: 'images/default.png') ?>" alt="User Pic" class="match-pic">
                    <h3><?= htmlspecialchars($match['name']) ?></h3>
                    <p><strong>Can Teach:</strong> <?= htmlspecialchars($match['teach_skills']) ?></p>
                    <p><strong>Wants to Learn:</strong> <?= htmlspecialchars($match['learn_skills']) ?></p>

                    <button 
                        class="send-btn <?= $already_sent ? 'pending-btn' : '' ?>" 
                        data-user-id="<?= $match['id']; ?>" 
                        id="send-btn-<?= $match['id']; ?>" 
                        <?= $already_sent ? 'disabled' : '' ?>>
                        <?= $already_sent ? '⏳ ' . htmlspecialchars($status) : '📩 Send Request' ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ✅ Chatbot Floating Button -->
<div id="chatbot-button" title="Chat History">
    💬
</div>

<!-- ✅ Chat History Panel -->
<div id="chat-history-panel" style="
    display: none;
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 300px;
    height: 400px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    overflow-y: auto;
    z-index: 1000;
    padding: 1rem;
">
    <h3>🕒 Your Chat History</h3>
    <div id="chat-history-list">
        <p>Loading chats...</p>
    </div>
    <button id="close-chat-history" style="
        margin-top: 1rem;
        padding: 6px 12px;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    ">❌ Close</button>
</div>



<!-- Footer -->
<footer style="background-color: #fff0f5; padding: 1.2rem 0; text-align: center; margin-top: 3rem; border-top: 1px solid #f8a5c2;">
  <p style="margin: 0.2rem; color: #555;">© <?= date('Y') ?> SkillSwap | Built with 💡 by passionate learners</p>
  <div style="margin-top: 0.5rem;">
    <a href="about.php" style="margin: 0 1rem; color: #e91e63; text-decoration: none;">About</a>
    <a href="contact.php" style="margin: 0 1rem; color: #e91e63; text-decoration: none;">Contact</a>
    <a href="privacy.php" style="margin: 0 1rem; color: #e91e63; text-decoration: none;">Privacy</a>
  </div>
</footer>

<script>
    sessionStorage.setItem("user_id", <?= json_encode($_SESSION['user_id']) ?>);
</script>

<script src="dashboard.js"></script>

</body>
</html>

</html>

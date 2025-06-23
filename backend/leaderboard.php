<?php
session_start();
include 'db.php';
include 'includes/navbar.php'; 

// Fetch leaderboard data with user info
$sql = "
    SELECT l.*, u.name, u.profile_pic
    FROM leaderboard l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.xp DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🏆 SkillSwap | Leaderboard</title>
  <link rel="stylesheet" href="dashboard_style.css">
<style>
    body {
    font-family: 'Poppins', sans-serif;
    background: #f3f8ff;
}

/* 🧠 Leaderboard Heading */
.leaderboard-heading-wrapper {
    text-align: center;
    background: linear-gradient(to right, #fce4ec, #e1f5fe);
    padding: 2rem;
    margin: 2rem auto;
    border-radius: 1.5rem;
    max-width: 800px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.leaderboard-title {
    font-size: 2.2rem;
    font-weight: bold;
    color: #4a148c;
    margin-bottom: 0.5rem;
}

.leaderboard-subtitle {
    font-size: 1.1rem;
    font-weight: 500;
    color: #6b21a8;
    font-family: 'Poppins', sans-serif;
    line-height: 1.4;
}

/* 🔥 Top Users Box */
.top-users-box {
    background: #ffffff;
    padding: 1.5rem 2rem;
    border-radius: 1.5rem;
    margin: 2rem auto;
    max-width: 700px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    text-align: center;
}

.top-users-box h3 {
    color: #a21caf;
    font-size: 1.4rem;
    font-weight: 600;
    margin: 0;
}

/* 📊 Leaderboard Container */
.leaderboard-container {
    max-width: 900px;
    margin: 1rem auto;
    background: transparent;
    border-radius: 16px;
    padding: 0;
}

.leaderboard-entry {
    display: flex;
    align-items: center;
    background: #fff0f5;
    padding: 1rem;
    border-radius: 1rem;
    margin: 1rem auto;
    max-width: 600px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    gap: 1rem;
}

.leaderboard-entry img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f8a5c2;
}

.leaderboard-entry .info {
    flex-grow: 1;
}

.leaderboard-entry .info h4 {
    margin: 0;
    font-size: 1.2rem;
    color: #6b21a8;
}

.leaderboard-entry .info small {
    color: #777;
}

.xp-score {
    font-weight: bold;
    color: #e91e63;
}

.badge {
    display: inline-block;
    background: #f3e8ff;
    color: #6a1b9a;
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    border-radius: 999px;
    margin-top: 0.3rem;
}

.skill-master { background: #ff7675; color: #fff; }
.learner-pro { background: #74b9ff; color: #fff; }
.top-collab { background: #55efc4; color: #fff; }

/* 🏅 Badge Criteria Box */
.badge-info-box {
    max-width: 700px;
    margin: 3rem auto;
    background: #f9f0ff;
    padding: 2rem;
    border-radius: 1.5rem;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
}

.badge-info-box h3 {
    text-align: center;
    font-size: 1.6rem;
    margin-bottom: 1.5rem;
    color: #7b1fa2;
}

.badge-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.badge-table th, .badge-table td {
    padding: 1rem 1.2rem;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 0.95rem;
}

.badge-table th {
    background: #f3e8ff;
    color: #6a1b9a;
    font-weight: bold;
}

.badge-table td {
    color: #4a148c;
    background: #fff;
}

.badge-table tr:last-child td {
    border-bottom: none;
}

.note {
    margin-top: 1rem;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 500;
    color: #6b21a8;
    font-style: italic;
}
.review-box {
  background: #f9f1ff;
  margin-top: 0.5rem;
  padding: 0.7rem 1rem;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.review-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.review-header img {
  width: 35px;
  height: 35px;
  border-radius: 50%;
}

</style>
</head>
<body>
<div class="leaderboard-heading-wrapper">
  <h2 class="leaderboard-title">🏆 Leaderboard</h2>
  <p class="leaderboard-subtitle">
    Climb the ranks, earn badges & become a SkillSwap legend! ✨ Show off your swaps, ratings & XP like a true pro.
  </p>
</div>

<div class="leaderboard-container">
   <div class="top-users-box">
  <h3>🔥 Top Users This Week</h3>
</div>

    <?php
    $rank = 1;
    while ($row = $result->fetch_assoc()):
        $badge = '';
        if ($row['teach_count'] >= 5) $badge = '<span class="badge skill-master">🌟 Skill Master</span>';
        elseif ($row['learn_count'] >= 5) $badge = '<span class="badge learner-pro">🧠 Learner Pro</span>';
        elseif ($row['endorsements_received'] >= 3) $badge = '<span class="badge top-collab">💬 Top Collaborator</span>';
    ?>
        <div class="leaderboard-entry">
            <img src="<?= htmlspecialchars($row['profile_pic'] ?: 'images/default.png'); ?>" alt="Profile">
            <div class="info">
                <h4><?= $rank . '. ' . htmlspecialchars($row['name']); ?></h4>
                <small>XP: <span class="xp-score"><?= $row['xp']; ?></span></small><br>
<?= $badge ?>

<?php
$review_stmt = $conn->prepare("
    SELECT e.rating, e.feedback, e.created_at, u.name AS reviewer_name, u.profile_pic AS reviewer_pic
    FROM endorsements e
    JOIN users u ON e.sender_id = u.id
    WHERE e.receiver_id = ?
    ORDER BY e.created_at DESC
    LIMIT 2
");
$review_stmt->bind_param("i", $row['user_id']);
$review_stmt->execute();
$review_result = $review_stmt->get_result();

if ($review_result->num_rows > 0): ?>
  <div style="margin-top: 1rem;">
    <strong style="color:#6a1b9a;">💬 What people say:</strong>
    <?php while ($review = $review_result->fetch_assoc()): ?>
      <div class="review-box">
        <div class="review-header">
          <img src="<?= htmlspecialchars($review['reviewer_pic'] ?: 'images/default.png'); ?>">
          <div>
            <strong><?= htmlspecialchars($review['reviewer_name']); ?></strong><br>
            <small><?= date("M d, Y", strtotime($review['created_at'])) ?></small>
          </div>
        </div>
        <div style="margin-top: 0.5rem;">Rating: <?= str_repeat("⭐", $review['rating']); ?></div>
        <div style="font-style: italic;">"<?= htmlspecialchars($review['feedback']); ?>"</div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>


            </div>
        </div>
    <?php $rank++; endwhile; ?>
</div>

<?php
$review_stmt = $conn->prepare("
    SELECT e.rating, e.feedback, e.created_at, u.name, u.profile_pic
    FROM endorsements e
    JOIN users u ON e.sender_id = u.id
    WHERE e.receiver_id = ?
    ORDER BY e.created_at DESC
");
$review_stmt->bind_param("i", $user_id); // Replace with a specific user ID or loop
$review_stmt->execute();
$review_result = $review_stmt->get_result();
?>
<div style="max-width: 650px; margin: 3rem auto;">
  <h2 style="color: #e91e63;">🌟 Reviews Received</h2>

  <?php if ($review_result->num_rows > 0): ?>
    <?php while ($row = $review_result->fetch_assoc()): ?>
      <div style="background: #fff0f5; padding: 1.2rem; margin-bottom: 1.5rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="display:flex; align-items:center; gap:1rem;">
          <img src="<?= htmlspecialchars($row['profile_pic'] ?: 'images/default.png'); ?>" style="width:50px; height:50px; border-radius:50%;">
          <div>
            <strong><?= htmlspecialchars($row['name']); ?></strong><br>
            <small><?= date("M d, Y", strtotime($row['created_at'])) ?></small>
          </div>
        </div>
        <p style="margin-top:0.8rem;">Rating: <?= str_repeat("⭐", $row['rating']); ?></p>
        <p style="margin-top:0.3rem;">"<?= htmlspecialchars($row['feedback']); ?>"</p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="color:#999;">No reviews yet. Once someone gives you feedback, it’ll show here! 💬</p>
  <?php endif; ?>
</div>


<div class="badge-info-box">
    <h3>🔓 How to Unlock Badges</h3>
    <table class="badge-table">
        <thead>
            <tr>
                <th>🏅 Badge</th>
                <th>Criteria</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Skill Master</strong></td>
                <td>Teach 5+ users and maintain a 4⭐+ average rating</td>
            </tr>
            <tr>
                <td><strong>Learner Pro</strong></td>
                <td>Successfully learn 3+ new skills from others</td>
            </tr>
            <tr>
                <td><strong>Top Collaborator</strong></td>
                <td>Complete 8+ swaps and receive at least 5 reviews</td>
            </tr>
            <tr>
                <td><strong>Consistent Swapper</strong></td>
                <td>Stay active and swap continuously for 3 weeks</td>
            </tr>
        </tbody>
    </table>
    <p class="note">✨ Keep swapping, sharing and upgrading — your next badge is waiting! 🚀</p>
</div>



</body>
</html>

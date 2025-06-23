<?php
session_start();
include 'db.php';
include 'update_xp.php';
include 'includes/navbar.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT name, email, bio, teach_skills, learn_skills, profile_pic, location, skill_level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ Fetch reviews here (MOVE THIS UP)
$review_stmt = $conn->prepare("
    SELECT e.rating, e.feedback, e.created_at, u.name, u.profile_pic
    FROM endorsements e
    JOIN users u ON e.sender_id = u.id
    WHERE e.receiver_id = ?
    ORDER BY e.created_at DESC
");
$review_stmt->bind_param("i", $user_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();


$stmt = $conn->prepare("SELECT name, email, bio, teach_skills, learn_skills, profile_pic, location, skill_level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SkillSwap | Profile</title>
  <link rel="stylesheet" href="dashboard_style.css">
  <style>
  .profile-container {
    max-width: 650px;
    margin: 2rem auto;
    padding: 2.5rem;
    background: linear-gradient(135deg, #fdf0f5, #e0f7fa); /* pastel gradient */
    border-radius: 1.5rem;
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
}

.profile-container:hover {
    box-shadow: 0 12px 24px rgba(0,0,0,0.12);
}

input[readonly], textarea[readonly] {
    background-color: #f7f7f7;
    border: none;
    color: #555;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 0.5rem;
    color: #e91e63;
}

input, textarea {
    width: 100%;
    padding: 0.6rem;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
}

.btn {
    padding: 0.6rem 1.2rem;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    margin-right: 1rem;
    font-weight: 600;
}

.edit-btn {
    background-color: #f8a5c2;
    color: white;
}

.save-btn {
    background-color: #a0d468;
    color: white;
    display: none;
}

select {
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-family: 'Poppins', sans-serif;
  background-color: #fff;
  color: #333;
  appearance: auto !important;
  -webkit-appearance: auto !important;
  -moz-appearance: auto !important;
}
.page-heading {
    background: #f3e8ff;
    padding: 1.5rem 2rem;
    margin: 2rem auto;
    border-radius: 1.5rem;
    max-width: 800px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.page-heading h2 {
    font-size: 2rem;
    color: #6b21a8;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.page-heading .subtitle {
    font-size: 1rem;
    color: #4a148c;
    font-weight: 500;
    max-width: 600px;
}


  </style>
</head>
<body>
<div class="page-heading">
    <h2>👤 My Profile</h2>
    <p class="subtitle">
    Craft your SkillSwap journey — update your profile, showcase your vibes, and collect reviews like trophies 🏆✨
</p>

</div>

<div class="profile-container">

    <?php
// Show XP if available
if (isset($_SESSION['xp_updated_msg'])) {
    echo '<div style="
        background: #e0f7fa;
        color: #00796b;
        border: 2px solid #4db6ac;
        padding: 0.8rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        margin-bottom: 1rem;
        text-align: center;
    ">' . $_SESSION['xp_updated_msg'] . '</div>';

    // Clear it after showing once
    unset($_SESSION['xp_updated_msg']);
}
?>

    <?php
        $profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'images/default.png';
    ?>
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile Picture"
                style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #f8a5c2;">
        </div>

    <form action="update_profile.php" method="POST" id="profileForm">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea name="bio" id="bio" rows="3" readonly><?= htmlspecialchars($user['bio']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="teach_skills">Skills I can teach</label>
            <textarea name="teach_skills" id="teach_skills" rows="2" readonly><?= htmlspecialchars($user['teach_skills']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="learn_skills">Skills I want to learn</label>
            <textarea name="learn_skills" id="learn_skills" rows="2" readonly><?= htmlspecialchars($user['learn_skills']) ?></textarea>
        </div>

<div class="form-group">
  <label for="location">🌍 Location</label>
  <select name="location" id="location" readonly>
    <option value="">Select your location</option>
    <option value="Delhi" <?= $user['location'] === 'Delhi' ? 'selected' : '' ?>>Delhi</option>
    <option value="Mumbai" <?= $user['location'] === 'Mumbai' ? 'selected' : '' ?>>Mumbai</option>
    <option value="Jaipur" <?= $user['location'] === 'Jaipur' ? 'selected' : '' ?>>Jaipur</option>
    <option value="Kolkata" <?= $user['location'] === 'Kolkata' ? 'selected' : '' ?>>Kolkata</option>
    <option value="Other" <?= $user['location'] === 'Other' ? 'selected' : '' ?>>Other</option>
  </select>
</div>


    <div class="form-group">
        <label for="skill_level">🧠 Skill Level</label>
       <select name="skill_level" id="skill_level" readonly>
            <option value="">Select level</option>
            <option value="Beginner" <?= $user['skill_level'] === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
            <option value="Intermediate" <?= $user['skill_level'] === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
            <option value="Expert" <?= $user['skill_level'] === 'Expert' ? 'selected' : '' ?>>Expert</option>
        </select>
    </div>

    <button type="button" class="btn edit-btn" id="editBtn">✏️ Edit Profile</button>
    <button type="submit" class="btn save-btn" id="saveBtn">💾 Save Changes</button>

</form>
</div>

</div>

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


<script>
  const editBtn = document.getElementById("editBtn");
  const saveBtn = document.getElementById("saveBtn");
  const formInputs = document.querySelectorAll("#profileForm input, #profileForm textarea, #profileForm select");

  editBtn.addEventListener("click", () => {
    formInputs.forEach(input => {
      input.removeAttribute("readonly");
      input.removeAttribute("disabled"); // This will re-enable dropdowns
    });
    editBtn.style.display = "none";
    saveBtn.style.display = "inline-block";
  });
</script>

</body>
</html>

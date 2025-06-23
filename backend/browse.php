<?php
session_start();
include 'db.php';
include 'includes/navbar.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sortTrending = isset($_GET['trending']) && $_GET['trending'] == '1';

if ($sortTrending) {
    $sql = "SELECT id, name, bio, teach_skills, learn_skills, profile_pic, location, skill_level
            FROM users WHERE id != ? ORDER BY teach_skills";
} else {
    $sql = "SELECT id, name, bio, teach_skills, learn_skills, profile_pic, location, skill_level
            FROM users WHERE id != ?";
}

// Fetch all other users
// $sql = "SELECT id, name, bio, teach_skills, learn_skills, profile_pic, location, skill_level FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Get already sent requests
$sent_sql = "SELECT receiver_id FROM requests WHERE sender_id = ? AND status = 'Pending'";
$sent_stmt = $conn->prepare($sent_sql);
$sent_stmt->bind_param("i", $user_id);
$sent_stmt->execute();
$sent_result = $sent_stmt->get_result();

$sent_ids = [];
while ($row = $sent_result->fetch_assoc()) {
    $sent_ids[] = $row['receiver_id'];
}

$skill_counts = [];

$all_stmt = $conn->prepare("SELECT teach_skills FROM users WHERE teach_skills IS NOT NULL AND teach_skills != ''");
$all_stmt->execute();
$all_result = $all_stmt->get_result();

while ($row = $all_result->fetch_assoc()) {
    $skills = explode(',', $row['teach_skills']);
    foreach ($skills as $skill) {
        $cleanSkill = trim(strtolower($skill));
        if (!isset($skill_counts[$cleanSkill])) {
            $skill_counts[$cleanSkill] = 1;
        } else {
            $skill_counts[$cleanSkill]++;
        }
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SkillSwap | Browse</title>
  <link rel="stylesheet" href="dashboard_style.css">
  <style>
    .browse-container {
        max-width: 1100px;
        margin: 3rem auto;
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }
    .user-card {
        background: #fff0f5;
        padding: 1.5rem;
        border-radius: 1.2rem;
        box-shadow: 0 6px 12px rgba(0,0,0,0.06);
        text-align: center;
    }
    .user-card img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 2px solid #f8a5c2;
        object-fit: cover;
        margin-bottom: 0.8rem;
    }
    .user-card h3 {
        margin: 0.5rem 0 0.3rem;
        color: #e91e63;
    }
    .user-card p {
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 0.6rem;
    }
    .skills {
        font-size: 0.85rem;
        margin-top: 0.4rem;
        color: #444;
    }
    .filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    justify-content: center;
    margin: 2rem auto;
    max-width: 950px;
}

    .filter-bar input,
    .filter-bar select {
        width: 180px;                    /* smaller width */
        padding: 0.4rem 0.9rem;          /* compact padding */
        border-radius: 20px;             /* fully rounded */
        border: 1px solid #f8a5c2;       /* soft pink border */
        background-color: #fff0f5;       /* pastel pink bg */
        font-family: 'Poppins', sans-serif;
        font-size: 0.85rem;
        color: #333;
        box-shadow: 0 2px 5px rgba(248, 165, 194, 0.1);
        transition: all 0.2s ease-in-out;
        outline: none;
    }

    .filter-bar input::placeholder {
        color: #b76e79;
        font-style: italic;
    }

    .filter-bar input:focus,
    .filter-bar select:focus {
        background-color: #ffe5f0;
        border-color: #e28cb1;
        box-shadow: 0 0 0 3px rgba(248, 165, 194, 0.25);
    }

  </style>
</head>
<body>


<div style="max-width: 800px; margin: 2rem auto; background: #fff0f5; padding: 1.5rem 2rem; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.06); text-align: center;">
    <h2 style="margin: 0; color: #e91e63;">🌟 Find someone who levels you up!</h2>
    <p style="margin-top: 0.5rem; color: #555;">Every expert was once a beginner. Your next growth leap could start with just one message. 🔁</p>
</div>

<!-- 🔍 Search + Filter Bar -->
<div class="filter-bar" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
    <!-- Search input -->
    <input type="text" id="searchInput" placeholder="Search by skill..." 
        style="flex: 2; padding: 0.6rem 1rem; border-radius: 10px; border: 1px solid #ccc;">

    <!-- Toggle: Can Teach / Want to Learn -->
    <select id="teachLearnFilter" style="flex: 1; padding: 0.6rem 1rem; border-radius: 10px; border: 1px solid #ccc;">
        <option value="">Teach / Learn</option>
        <option value="teach">Can Teach</option>
        <option value="learn">Wants to Learn</option>
    </select>

    <!-- Skill Level -->
    <select id="skillLevelFilter" style="flex: 1; padding: 0.6rem 1rem; border-radius: 10px; border: 1px solid #ccc;">
        <option value="">Skill Level</option>
        <option value="Beginner">Beginner</option>
        <option value="Intermediate">Intermediate</option>
        <option value="Expert">Expert</option>
    </select>

    <!-- Location (optional field in DB) -->
    <select id="locationFilter" style="flex: 1; padding: 0.6rem 1rem; border-radius: 10px; border: 1px solid #ccc;">
        <option value="">Location</option>
        <option value="Delhi">Delhi</option>
        <option value="Mumbai">Mumbai</option>
        <option value="Jaipur">Jaipur</option>
        <!-- add from DB later -->
    </select>
</div>

<div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1rem;">
  <input type="checkbox" id="trendingToggle" style="accent-color: #f8a5c2; cursor: pointer;">
  <label for="trendingToggle" style="font-size: 0.9rem; color: #e91e63; cursor: pointer;">Sort by Trending Skills</label>
</div>

<div style="background: #fff0f5; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
    <h3 style="margin-bottom: 0.8rem; color: #e91e63;">🔥 Trending Skills</h3>
    <?php foreach ($skill_counts as $skill => $count): ?>
        <span style="display: inline-block; background: #fce4ec; padding: 0.4rem 0.8rem; margin: 0.2rem; border-radius: 999px; font-size: 0.85rem; color: #333;">
            <?= ucfirst($skill) ?> – <?= $count ?> users
        </span>
    <?php endforeach; ?>
</div>


<div class="browse-container">
    <?php while ($user = $result->fetch_assoc()): ?>
        <div class="user-card"
             data-teach="<?= strtolower($user['teach_skills']) ?>"
             data-learn="<?= strtolower($user['learn_skills']) ?>"
             data-location="<?= strtolower($user['location'] ?? '') ?>"
             data-level="<?= strtolower($user['skill_level'] ?? '') ?>">
             
            <img src="<?= htmlspecialchars($user['profile_pic']) ?: 'images/default.png' ?>" alt="Profile Pic">
            <h3><?= htmlspecialchars($user['name']) ?></h3>
            <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
            <div class="skills"><strong>Can Teach:</strong> <?= htmlspecialchars($user['teach_skills']) ?></div>
            <div class="skills"><strong>Wants to Learn:</strong> <?= htmlspecialchars($user['learn_skills']) ?></div>
            <div class="skills"><strong>Skill Level:</strong> <?= $user['skill_level'] ? ucfirst(htmlspecialchars($user['skill_level'])) : 'Not specified' ?></div>
            <div class="skills"><strong>Location:</strong> <?= $user['location'] ? ucfirst(htmlspecialchars($user['location'])) : 'Not specified' ?></div>

            <button 
                class="send-btn" 
                data-user-id="<?= $user['id'] ?>" 
                id="send-btn-<?= $user['id'] ?>"
                <?= in_array($user['id'], $sent_ids) ? 'disabled class="send-btn pending-btn"' : '' ?>>
                <?= in_array($user['id'], $sent_ids) ? '⏳ Pending' : '📩 Send Request' ?>
            </button>
        </div>
    <?php endwhile; ?>
</div>


<div style="max-width: 800px; margin: 3rem auto 2rem; background: #fff0f5; padding: 1.5rem 2rem; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.06); text-align: center;">
    <h3 style="margin: 0; color: #e91e63;">🚀 Don’t just scroll… Send that request!</h3>
    <p style="margin-top: 0.5rem; color: #555;">The skills you’re searching for are one click away. Let’s make the first move 💬</p>
</div>

<script src="dashboard.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const teachLearnFilter = document.getElementById("teachLearnFilter");
    const skillLevelFilter = document.getElementById("skillLevelFilter");
    const locationFilter = document.getElementById("locationFilter");

    const cards = document.querySelectorAll(".user-card");

    function applyFilters() {
        const searchVal = searchInput.value.toLowerCase().trim();
        const teachLearn = teachLearnFilter.value;
        const level = skillLevelFilter.value.toLowerCase();
        const location = locationFilter.value.toLowerCase();

        cards.forEach(card => {
            const teachSkills = card.dataset.teach;
            const learnSkills = card.dataset.learn;
            const userLocation = card.dataset.location;
            const userLevel = card.dataset.level;

            let matches = true;

            // 💡 Search Skill Matching
            if (searchVal) {
                if (!teachSkills.includes(searchVal) && !learnSkills.includes(searchVal)) {
                    matches = false;
                }
            }

            // 👩‍🏫 Teach/Learn Toggle
            if (teachLearn === "teach" && !teachSkills.includes(searchVal)) {
                if (searchVal) matches = false;
            }
            if (teachLearn === "learn" && !learnSkills.includes(searchVal)) {
                if (searchVal) matches = false;
            }

            // 🌍 Location filter
            if (location && userLocation !== location) {
                matches = false;
            }

            // 🧠 Skill level
            if (level && userLevel !== level) {
                matches = false;
            }

            // Show or hide card
            card.style.display = matches ? "block" : "none";
        });
    }

    // 🧲 Attach events
    [searchInput, teachLearnFilter, skillLevelFilter, locationFilter].forEach(el => {
        el.addEventListener("input", applyFilters);
        el.addEventListener("change", applyFilters);
    });
});
document.getElementById('trendingToggle').addEventListener('change', function () {
    const trending = this.checked ? 1 : 0;
    const url = new URL(window.location.href);
    url.searchParams.set('trending', trending);
    window.location.href = url.toString();
});

</script>
<script>
window.addEventListener("DOMContentLoaded", () => {
    const url = new URL(window.location.href);
    const trending = url.searchParams.get("trending");
    if (trending === "1") {
        document.getElementById("trendingToggle").checked = true;
    }
});
</script>


</body>
</html>

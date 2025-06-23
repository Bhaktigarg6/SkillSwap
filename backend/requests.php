<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include 'includes/navbar.php'; 

$user_id = $_SESSION['user_id'];

// Fetch sent requests
$sent_sql = "
SELECT r.*, u.name, u.profile_pic
FROM requests r
JOIN users u ON r.receiver_id = u.id
WHERE r.sender_id = ?
ORDER BY r.created_at DESC
";
$sent_stmt = $conn->prepare($sent_sql);
$sent_stmt->bind_param("i", $user_id);
$sent_stmt->execute();
$sent_requests = $sent_stmt->get_result();

// Fetch received requests
$recv_sql = "
SELECT r.*, u.name, u.profile_pic
FROM requests r
JOIN users u ON r.sender_id = u.id
WHERE r.receiver_id = ?
ORDER BY r.created_at DESC
";
$recv_stmt = $conn->prepare($recv_sql);
$recv_stmt->bind_param("i", $user_id);
$recv_stmt->execute();
$received_requests = $recv_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SkillSwap | My Requests</title>
    <link rel="stylesheet" href="dashboard_style.css">
    <style>
  .request-section {
    background: #fff0f5;
    padding: 2rem;
    border-radius: 1.2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    margin: 2rem;
}

.request-card {
    background: #fff;
    padding: 1rem;
    border-radius: 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

.request-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.request-info img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f8a5c2;
}

.status {
    font-weight: 600;
    color: #e91e63;
}

.action-btns button {
    margin-left: 0.5rem;
    padding: 0.4rem 1rem;
    border-radius: 10px;
    border: none;
    cursor: pointer;
}

.withdraw-btn {
    padding: 0.5rem 1.2rem;
    background-color: #ffe0e6;
    color: #d62839;
    border: none;
    border-radius: 12px;
    font-weight: 500;
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.withdraw-btn:hover {
    background-color: #f8a5c2;
    color: white;
    transform: translateY(-2px);
}

.accept-btn, .reject-btn {
    padding: 0.5rem 1rem;
    margin: 0.25rem;
    border-radius: 10px;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    color: white;
}

.accept-btn {
    background: #a0d468;
}

.reject-btn {
    background: #ed5565;
}

.accept-btn:hover {
    background: #68c78b;
}

.reject-btn:hover {
    background: #f672a7;
}

/* Schedule Swap Section */
.schedule-container {
    background: #f9f0ff;
    padding: 1.5rem;
    margin-top: 2rem;
    border-radius: 1.5rem;
    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.schedule-container h3 {
    margin-bottom: 1rem;
    color: #9c27b0;
    font-weight: 600;
    font-size: 1.2rem;
    text-align: center;
}

.schedule-form label {
    font-weight: 500;
    color: #555;
    display: block;
    margin-bottom: 0.5rem;
}

.schedule-form input[type="datetime-local"] {
    width: 100%;
    padding: 0.6rem;
    border-radius: 0.8rem;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    margin-bottom: 1rem;
    background: #fff;
    color: #333;
}

.schedule-btn {
    background-color: #b388ff;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 12px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease;
}

.schedule-btn:hover {
    background-color: #9575cd;
}

.request-details {
    background: #fff8fd;
    padding: 1rem 1.2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-top: 1rem;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
}

/* Skill Row Layout */
.skill-details-wrapper {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.skill-row {
    display: flex;
    align-items: center;
    margin: 0.4rem auto;
    padding: 0.4rem 1rem;
    background-color: #f5e8ff;
    border-radius: 999px;
    width: 85%;
    max-width: 600px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.04);
}

.label {
    flex: 1;
    text-align: right;  /* 👈 This keeps the key to the right side */
    padding-right: 12px;
    font-weight: 600;
    color: #7c3aed;
    white-space: nowrap;
    font-size: 0.95rem;
}

.value {
    flex: 2;
    text-align: left;
    font-weight: 500;
    background-color: #ede7f6;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.95rem;
    color: #4a148c;
}


/* Status color variations */
.status.accepted {
    color: #28a745;
}
.status.rejected {
    color: #d62828;
}
.status.pending {
    color: #f0ad4e;
}

/* Name styling */
.user-name {
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
    font-weight: bold;
    color: #4a148c;
    text-align: center;
}
.chat-box {
    background: #fdf4ff;
    border: 2px solid #e9d5ff;
    border-radius: 1.2rem;
    padding: 1rem 1.5rem;
    margin-top: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    font-family: 'Poppins', sans-serif;
}

.chat-box h4 {
    font-size: 1.1rem;
    color: #7c3aed;
    margin-bottom: 1rem;
    text-align: center;
}

.chat-msg {
    padding: 8px 14px;
    margin: 4px 0;
    border-radius: 18px;
    display: inline-block;
    max-width: 80%;
    font-size: 0.9rem;
}

.right-msg {
    background-color: #d0f0fd;
    align-self: flex-end;
    text-align: right;
}

.left-msg {
    background-color: #f1e5ff;
    align-self: flex-start;
}

.chat-messages {
    background: #ffffff;
    border: 1px solid #e0d7ec;
    border-radius: 1rem;
    max-height: 200px;
    min-height: 0px; /* <-- updated */
    overflow-y: auto;
    padding: 0;
    margin-bottom: 1rem;
    font-size: 0.95rem;
    color: #333;
    display: none; /* hide if empty */
}

.chat-form {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    align-items: center;
}

.chat-form input[type="text"] {
    flex: 1;
    padding: 0.7rem 1rem;
    border: 1px solid #d1c4e9;
    border-radius: 999px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    background: #f6f0ff;
    color: #4a148c;
}

.chat-form button {
    background: #b388ff;
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.chat-form button:hover {
    background: #a06dff;
}
.request-subtitle-wrapper {
    text-align: center;
    background: #f3e8ff;
    padding: 1rem 2rem;
    margin: 1.5rem auto 2rem;
    border-radius: 1rem;
    max-width: 750px;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
}

.request-subtitle-wrapper .subtitle {
    color: #6b21a8;
    font-size: 1.05rem;
    font-weight: 500;
    margin: 0;
    font-family: 'Poppins', sans-serif;
}
.withdraw-wrapper,
.cancel-swap-wrapper {
    margin-top: 1.5rem;
    text-align: center;
}

    </style>
</head>
<body>

<div class="request-subtitle-wrapper">
    <p class="subtitle">
        Get to know learners you've recently connected with — respond to requests and grow together 🔗
    </p>
</div>

<div class="requests-container">
    
    <!-- Sent Requests Section -->
    <div class="request-section">
        <h2>📤 Sent Requests</h2>
        <div class="cards">
        <?php
$sent_stmt = $conn->prepare("
    SELECT r.id AS request_id, r.status, r.is_completed, r.receiver_id, r.sender_id, r.scheduled_at,
           u.name, u.profile_pic, u.teach_skills, u.learn_skills 
    FROM requests r
    JOIN users u ON r.receiver_id = u.id
    JOIN (
        SELECT MAX(id) as latest_id
        FROM requests
        WHERE sender_id = ?
        GROUP BY receiver_id
    ) latest ON r.id = latest.latest_id
    ORDER BY r.created_at DESC
");

$sent_stmt->bind_param("i", $user_id);
$sent_stmt->execute();
$sent_result = $sent_stmt->get_result();

        $hasAccepted = false;
        if ($sent_result->num_rows > 0):
            while ($row = $sent_result->fetch_assoc()):
                $status = $row['status'];
                if ($status === 'Accepted') $hasAccepted = true;
                $statusColor = $status === 'Accepted' ? 'green' :
                               ($status === 'Rejected' ? 'red' :
                               ($status === 'Completed' ? 'blue' : 'orange'));
        ?>
      <div class="card match-card" id="req-<?= $row['request_id']; ?>">
    <img src="<?= htmlspecialchars($row['profile_pic']); ?>" class="match-pic">

    <div class="request-info-section">
        <div class="user-name"><?= htmlspecialchars($row['name']); ?></div>
<div class="skill-details-wrapper">
    <div class="skill-row">
        <span class="label">Can Teach:</span>
        <span class="value"><?= htmlspecialchars($row['teach_skills']); ?></span>
    </div>

    <div class="skill-row">
        <span class="label">Wants to Learn:</span>
        <span class="value"><?= htmlspecialchars($row['learn_skills']); ?></span>
    </div>

    <div class="skill-row">
        <span class="label">Status:</span>
        <span class="value status <?= strtolower($status); ?>">
            <?= $status ?>
        </span>
    </div>

    <?php if ($status === 'Accepted' && $row['is_completed'] == 0): ?>
        <?php if (!empty($row['scheduled_at'])): ?>
            <div class="skill-row">
                <span class="label">📆 Scheduled:</span>
                <span class="value"><?= date('M d, Y h:i A', strtotime($row['scheduled_at'])) ?></span>
            </div>
        <?php else: ?>
            <div class="schedule-container">
                <h3>📅 Schedule Swap Session</h3>
                <form class="schedule-form" data-request-id="<?= $row['request_id']; ?>">
                    <label for="scheduled_at_<?= $row['request_id']; ?>">Choose a Date & Time</label>
                    <input 
                        type="datetime-local" 
                        name="scheduled_at" 
                        id="scheduled_at_<?= $row['request_id']; ?>" 
                        min="<?= date('Y-m-d\TH:i') ?>" 
                        required
                    >
                    <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
                    <button type="submit" class="schedule-btn">📌 Schedule</button>
                    <div class="schedule-alert" id="alert-<?= $row['request_id']; ?>" style="display:none;"></div>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- 💬 Chat Box -->
<?php if ($row['status'] === 'Accepted'): ?>
<div class="chat-box" data-request-id="<?= $row['request_id']; ?>">
    <h4>💬 Chat with <?= htmlspecialchars($row['name']); ?></h4>
    <div class="chat-messages" id="chat-<?= $row['request_id']; ?>"></div>

    <form class="chat-form" data-request-id="<?= $row['request_id']; ?>">
        <input type="text" name="message" placeholder="Type your message..." required>
        <input type="hidden" name="receiver_id" value="<?= $row['receiver_id']; ?>">
        <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
        <button type="submit">Send</button>
    </form>
</div>
<?php endif; ?>

<?php if ($row['is_completed'] == 1): ?>
    <div class="skill-row">
        <span class="label">✔️ Status:</span>
        <span class="value" style="color: green; font-weight: bold;">Completed</span>
    </div>


            <?php
            $checkReview = $conn->prepare("SELECT id FROM endorsements WHERE request_id = ? AND sender_id = ?");
            $checkReview->bind_param("ii", $row['request_id'], $user_id);
            $checkReview->execute();
            $checkReview->store_result();
            ?>

            <?php if ($checkReview->num_rows === 0): ?>
                <button class="review-btn" 
                        data-request-id="<?= $row['request_id']; ?>" 
                        data-receiver-id="<?= $row['receiver_id']; ?>">
                    ⭐ Rate & Review
                </button>
            <?php else: ?>
                <p style="color: #555;">✅ Already Reviewed</p>
            <?php endif; ?>
        <?php endif; ?>

<?php if ($status === 'Pending'): ?>
    <div class="withdraw-wrapper">
        <form class="withdraw-form" data-request-id="<?= $row['request_id']; ?>">
            <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
            <button type="submit" class="withdraw-btn">❌ Withdraw</button>
        </form>
    </div>
<?php elseif ($status === 'Accepted'): ?>
    <div class="cancel-swap-wrapper">
        <form class="withdraw-form" data-request-id="<?= $row['request_id']; ?>">
            <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
        </form>
    </div>
<?php endif; ?>


    </div>
</div>

        <?php endwhile; else: ?>
            <p>No sent requests found.</p>
        <?php endif; ?>
        </div>
    </div>

   <!-- Received Requests Section -->
<div class="request-section">
    <h2>📥 Received Requests</h2>
    <div class="cards">
    <?php
    // Prepared statement fetches scheduled_at as well
$recv_stmt = $conn->prepare("
    SELECT r.id AS request_id, r.status, r.scheduled_at,
           u.name, u.profile_pic, u.teach_skills, u.learn_skills
    FROM requests r
    JOIN users u ON r.sender_id = u.id
    JOIN (
        SELECT MAX(id) as latest_id
        FROM requests
        WHERE receiver_id = ?
        GROUP BY sender_id
    ) latest ON r.id = latest.latest_id
    ORDER BY r.created_at DESC
");
$recv_stmt->bind_param("i", $user_id);
$recv_stmt->execute();
$recv_result = $recv_stmt->get_result();


    if ($recv_result->num_rows > 0):
        while ($row = $recv_result->fetch_assoc()):
    ?>
<div class="card match-card" id="recv-<?= $row['request_id']; ?>">
    <img src="<?= htmlspecialchars($row['profile_pic']); ?>" class="match-pic">
    <div class="request-info-section">
        <h3 class="user-name"><?= htmlspecialchars($row['name']); ?></h3>
        
        <div class="skill-row">
            <span class="label">📚 Can Teach:</span>
            <span class="value"><?= htmlspecialchars($row['teach_skills']) ?></span>
        </div>

        <div class="skill-row">
            <span class="label">🎯 Wants to Learn:</span>
            <span class="value"><?= htmlspecialchars($row['learn_skills']) ?></span>
        </div>

        <div class="skill-row">
            <span class="label">🔄 Status:</span>
            <span class="value status <?= strtolower($row['status']); ?>">
                <?= htmlspecialchars($row['status']) ?>
            </span>
        </div>

        <?php if ($row['status'] === 'Accepted' && !empty($row['scheduled_at'])): ?>
        <div class="skill-row">
            <span class="label">📅 Scheduled:</span>
            <span class="value">
                <?= date('M d, Y h:i A', strtotime($row['scheduled_at'])) ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if ($row['status'] === 'Pending'): ?>
        <form class="status-form" data-request-id="<?= $row['request_id']; ?>" style="margin-top: 1rem;">
            <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
            <button type="submit" name="action" value="Accepted" class="accept-btn">✅ Accept</button>
            <button type="submit" name="action" value="Rejected" class="reject-btn">❌ Reject</button>
        </form>
        <?php endif; ?>
    </div>
</div>


    <?php
        endwhile;
    else:
    ?>
        <p>No incoming requests.</p>
    <?php
    endif;
    ?>
    </div>
</div>
<?php if (!isset($hasAccepted)) $hasAccepted = false; ?>
<?php if (!$hasAccepted): ?>
<div class="schedule-container">
    <h3>📅 Schedule Swap Session</h3>
    <p style="color: #999; font-size: 1rem;">
        You’ll be able to schedule a session once your request is accepted. 📩
    </p>
</div>
<?php endif; ?>

<!-- rating modal -->
<div id="ratingModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; padding:2rem; border-radius:1rem; box-shadow:0 8px 20px rgba(0,0,0,0.2); z-index:999;">
    <h3 style="margin-top:0;">⭐ Leave a Review</h3>
    <form id="reviewForm">
        <input type="hidden" name="receiver_id" id="receiver_id">
        <input type="hidden" name="request_id" id="request_id">

        <!-- Star Rating -->
        <div style="margin:1rem 0;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="rating" value="<?= $i ?>" required>
                    <?= str_repeat("⭐", $i) ?>
                </label><br>
            <?php endfor; ?>
        </div>

        <!-- Feedback -->
        <textarea name="feedback" placeholder="Write your experience..." rows="3" style="width:100%; padding:0.5rem;"></textarea>

        <button type="submit" style="margin-top:1rem; background:#f8a5c2; border:none; padding:0.5rem 1.5rem; border-radius:10px; color:white; cursor:pointer;">Submit</button>
        <button type="button" onclick="document.getElementById('ratingModal').style.display='none'" style="margin-left:1rem;">Cancel</button>
    </form>
</div>

<script src="requests.js"></script>

</body>
</html>

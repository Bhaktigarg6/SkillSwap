<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['request_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = intval($_GET['request_id']);

// 🧠 First, get who you're talking to
$findPartner = $conn->prepare("SELECT sender_id, receiver_id FROM requests WHERE id = ?");
$findPartner->bind_param("i", $request_id);
$findPartner->execute();
$findPartner->bind_result($sid, $rid);
$findPartner->fetch();
$findPartner->close();

if ($sid != $user_id && $rid != $user_id) {
    die("Access Denied 🚫");
}


$receiver_id = ($sid == $user_id) ? $rid : $sid;

// 🧠 Now fetch messages
$sql = "
    SELECT m.*, us.name AS sender_name, ur.name AS receiver_name
    FROM messages m
    JOIN users us ON m.sender_id = us.id
    JOIN users ur ON m.receiver_id = ur.id
    WHERE m.request_id = ?
    ORDER BY m.sent_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <style>
        body {
            font-family: Poppins, sans-serif;
            padding: 2rem;
            background: #fdf6f8;
        }

        .chat-box {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 12px;
            max-width: 75%;
            word-wrap: break-word;
        }

        .message.you {
            background: #d1e7dd;
            margin-left: auto;
            text-align: right;
        }

        .message.other {
            background: #f0f0f0;
            margin-right: auto;
            text-align: left;
        }

        .msg-header {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.4rem;
        }

        .timestamp {
            font-size: 0.75rem;
            color: #999;
        }

        .msg-body {
            font-size: 0.95rem;
            color: #333;
        }
    </style>
</head>
<body>

<div class="chat-box">
    <h2>💬 Chat</h2>
    <?php if ($result->num_rows === 0): ?>
    <p style="text-align:center; color:#999;">No messages yet. Start chatting now 💬</p>
    <?php endif; ?>


    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $isSentByUser = ($row['sender_id'] == $user_id);
            $messageClass = $isSentByUser ? 'you' : 'other';
            $label = $isSentByUser
                ? "🟢 You (to " . htmlspecialchars($row['receiver_name']) . ")"
                : "🔵 " . htmlspecialchars($row['sender_name']) . " (to You)";
        ?>
        <div class="message <?= $messageClass ?>">
            <div class="msg-header">
                <strong><?= $label ?></strong>
                <span class="timestamp"><?= date('M d, Y h:i A', strtotime($row['sent_at'])) ?></span>
            </div>
            <div class="msg-body">
                <?= htmlspecialchars($row['message']) ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- 💬 Reply box -->
<form id="messageForm" style="margin-top: 2rem;">
    <textarea id="messageInput" rows="3" placeholder="Type your message..." style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid #ccc;"></textarea>
    <input type="hidden" id="receiverId" value="<?= $receiver_id ?>">
    <button type="submit" style="margin-top:0.5rem; padding:0.6rem 1rem; border:none; background:#e91e63; color:white; border-radius:8px;">Send</button>
</form>

<script>
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const message = document.getElementById('messageInput').value.trim();
    const requestId = <?= $request_id ?>;
    const receiverId = document.getElementById('receiverId').value;

    if (message === "") return;

    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `request_id=${requestId}&receiver_id=${receiverId}&message=${encodeURIComponent(message)}`
    })
    .then(res => res.text())
    .then(data => {
        if (data === 'success') {
            window.location.reload(); // show new message
        } else {
            alert("❌ Failed to send message: " + data);
        }
    });
});
</script>
<script>
document.getElementById('messageInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('messageForm').dispatchEvent(new Event('submit'));
    }
});
</script>
</body>
</html>

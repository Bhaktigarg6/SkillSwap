<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔒 Basic Validation
if (
    !isset($_SESSION['user_id']) ||
    !isset($_POST['request_id']) ||
    !isset($_POST['message'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing data!"]);
    exit;
}

$sender_id = $_SESSION['user_id'];
$request_id = intval($_POST['request_id']);
$message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

if ($message === "") {
    echo json_encode(["status" => "error", "message" => "Message empty!"]);
    exit;
}

// 🧠 Get sender & receiver IDs
$stmt = $conn->prepare("SELECT sender_id, receiver_id, status FROM requests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid request ID!"]);
    exit;
}

$data = $res->fetch_assoc();
$actual_sender = $data['sender_id'];
$actual_receiver = $data['receiver_id'];
$request_status = $data['status'];

// ✅ Allow only if request is Accepted
if ($request_status !== 'Accepted') {
    echo json_encode(["status" => "error", "message" => "Chat allowed only after request is accepted."]);
    exit;
}

// 📩 Decide receiver based on current user
$receiver_id = ($actual_sender == $sender_id) ? $actual_receiver : $actual_sender;

// 💬 Insert the message
$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, request_id, message, sent_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiis", $sender_id, $receiver_id, $request_id, $message);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Failed to send message."]);
    exit;
}

// 🔔 Insert a notification
$notifMsg = "💬 You received a new message!";
$notif = $conn->prepare("
    INSERT INTO notifications (user_id, message, is_read, is_seen, created_at)
    VALUES (?, ?, 0, 0, NOW())
");
$notif->bind_param("is", $receiver_id, $notifMsg);
$notif->execute();

// ✅ Respond
echo json_encode([
    "status" => "success",
    "message" => $message,
    "timestamp" => date("M d, h:i A")
]);

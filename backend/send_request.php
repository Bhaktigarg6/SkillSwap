<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id'])) {
    echo json_encode(['status' => 'error']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);

// Check if request already exists
$stmt = $conn->prepare("SELECT * FROM requests WHERE sender_id = ? AND receiver_id = ? AND status = 'Pending'");
$stmt->bind_param("ii", $sender_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'already_sent']);
    exit();
}

// Insert new request
$stmt = $conn->prepare("INSERT INTO requests (sender_id, receiver_id, status) VALUES (?, ?, 'Pending')");
$stmt->bind_param("ii", $sender_id, $receiver_id);

if ($stmt->execute()) {

    // ✅ Add notification for receiver
    $notify_msg = "📩 You received a new skill swap request!";
    $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notify_stmt->bind_param("is", $receiver_id, $notify_msg);
    $notify_stmt->execute();

    echo json_encode(['status' => 'success', 'receiver_id' => $receiver_id]);
} else {
    echo json_encode(['status' => 'error']);
}
?>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['request_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user or request ID"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = intval($_GET['request_id']);

// Get messages for the given request
$stmt = $conn->prepare("
    SELECT m.*, u.name, u.profile_pic 
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.request_id = ?
    ORDER BY m.sent_at ASC
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "id" => $row['id'],
        "sender_id" => $row['sender_id'],
        "receiver_id" => $row['receiver_id'],
        "message" => htmlspecialchars($row['message']),
        "sent_at" => date('M d, h:i A', strtotime($row['sent_at'])),
        "sender_name" => $row['name'],
        "profile_pic" => $row['profile_pic']
    ];
}

header('Content-Type: application/json');
echo json_encode($messages);

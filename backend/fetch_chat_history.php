<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
    r.id AS request_id, 
    u.name AS user_name, 
    m1.message AS last_message, 
    m1.sender_id,
    m1.sent_at AS last_message_time
FROM requests r
JOIN users u ON (
    u.id = CASE 
        WHEN r.sender_id = ? THEN r.receiver_id 
        ELSE r.sender_id 
    END
)
JOIN (
    SELECT m.*
    FROM messages m
    INNER JOIN (
        SELECT request_id, MAX(id) AS max_id
        FROM messages
        GROUP BY request_id
    ) grouped_m ON m.id = grouped_m.max_id
) m1 ON m1.request_id = r.id
WHERE r.sender_id = ? OR r.receiver_id = ?
ORDER BY m1.sent_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$chats = [];
while ($row = $result->fetch_assoc()) {
    $chats[] = [
        'request_id' => $row['request_id'],
        'user_name' => $row['user_name'],
        'sender_id' => $row['sender_id'],
        'last_message' => $row['last_message'],
        'last_message_time' => date('M d, Y h:i A', strtotime($row['last_message_time']))
    ];
}

header('Content-Type: application/json');
echo json_encode($chats);

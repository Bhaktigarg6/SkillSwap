<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT receiver_id FROM requests WHERE sender_id = ? AND status = 'Pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$sent = [];
while ($row = $result->fetch_assoc()) {
    $sent[] = (string)$row['receiver_id'];  // cast to string for JS match
}

echo json_encode($sent);

<?php
session_start();
include 'db.php';

if (!isset($_POST['request_id']) || !isset($_SESSION['user_id'])) {
    echo "Invalid access";
    exit;
}

$request_id = intval($_POST['request_id']);
$user_id = $_SESSION['user_id'];

// You can optionally check if the logged in user is the sender
$stmt = $conn->prepare("DELETE FROM requests WHERE id = ? AND sender_id = ?");
$stmt->bind_param("ii", $request_id, $user_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

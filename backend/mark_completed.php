<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$request_id = intval($_POST['request_id']);
$user_id = $_SESSION['user_id'];

// Optional: Only allow sender/receiver to mark as done
$stmt = $conn->prepare("UPDATE requests SET is_completed = 1 WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
$stmt->bind_param("iii", $request_id, $user_id, $user_id);

if ($stmt->execute()) {
    header("Location: requests.php?done=1");
} else {
    echo "Something went wrong.";
}
?>

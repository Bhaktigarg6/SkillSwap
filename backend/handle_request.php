<?php
session_start();
include 'db.php';
include 'update_xp.php';


if (!isset($_SESSION['user_id'])) {
    echo 'unauthorized';
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$request_id || !in_array($action, ['Accepted', 'Rejected'])) {
    echo 'invalid';
    exit;
}

// Check if the logged-in user is the receiver of the request
$stmt = $conn->prepare("SELECT sender_id, receiver_id FROM requests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo 'not_found';
    exit;
}

$row = $result->fetch_assoc();
if ($row['receiver_id'] != $user_id) {
    echo 'forbidden';
    exit;
}

$sender_id = $row['sender_id'];

// Update the request status
$update = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
$update->bind_param("si", $action, $request_id);
if ($update->execute()) {
    
    // Add a notification for the sender
    $message = "Your request was $action by the user.";
    $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif->bind_param("is", $sender_id, $message);
    $notif->execute();

    echo 'success';
} else {
    echo 'error';
}
?>

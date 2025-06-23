<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo 'unauthorized';
    exit;
}

$sender_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'] ?? 0;

// Check if the request exists and belongs to current user
$check = $conn->prepare("SELECT id FROM requests WHERE id = ? AND sender_id = ?");
$check->bind_param("ii", $request_id, $sender_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Delete the request
    $delete = $conn->prepare("DELETE FROM requests WHERE id = ?");
    $delete->bind_param("i", $request_id);
    if ($delete->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'not_found';
}
?>

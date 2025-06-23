<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔐 Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'] ?? null;
$scheduled_at = $_POST['scheduled_at'] ?? null;

// 🧪 Validate input
if (!$request_id || !$scheduled_at) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit();
}

// 🧠 Validate request ownership
$check = $conn->prepare("SELECT sender_id, receiver_id FROM requests WHERE id = ?");
$check->bind_param("i", $request_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid request ID"]);
    exit();
}

$request = $result->fetch_assoc();

if ($request['sender_id'] != $user_id && $request['receiver_id'] != $user_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// 📅 Update scheduled date
$update = $conn->prepare("UPDATE requests SET scheduled_at = ? WHERE id = ?");
$update->bind_param("si", $scheduled_at, $request_id);

if ($update->execute()) {
    echo json_encode(["status" => "success", "message" => "Session scheduled"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB error: " . $conn->error]);
}

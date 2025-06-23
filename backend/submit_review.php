<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized access.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        echo "Invalid rating.";
        exit();
    }

    // Prevent duplicate reviews for the same request
    $check = $conn->prepare("SELECT id FROM endorsements WHERE sender_id = ? AND request_id = ?");
    $check->bind_param("ii", $sender_id, $request_id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        echo "You have already reviewed this request.";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO endorsements (sender_id, receiver_id, request_id, rating, feedback) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $sender_id, $receiver_id, $request_id, $rating, $feedback);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Failed to submit review.";
    }
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
?>

<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $request_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "fail";
    }
}
?>

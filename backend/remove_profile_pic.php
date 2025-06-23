<?php
session_start();
include 'db.php'; // ✅ adjust if your connection file is named differently

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Get the existing profile picture path
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && !empty($row['profile_pic']) && file_exists($row['profile_pic'])) {
        unlink($row['profile_pic']); // ❌ delete the file
    }

    // Update DB to set default
    $default_pic = 'images/default.png';
    $update_stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    $update_stmt->bind_param("si", $default_pic, $user_id);
    $update_stmt->execute();

    // Update session too if you're storing it there
    $_SESSION['profile_pic'] = $default_pic;

    header("Location: dashboard.php");
    exit();
} else {
    echo "You must be logged in to do this.";
}
?>

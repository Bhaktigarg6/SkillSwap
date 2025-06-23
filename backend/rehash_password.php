<?php
include 'db.php';

$newPlainPassword = 'bhaktigarg@1602';
$hashedPassword = password_hash($newPlainPassword, PASSWORD_BCRYPT);

// Update password for user with id 11
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = 11");
$stmt->bind_param("s", $hashedPassword);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Password updated successfully.";
} else {
    echo "⚠️ Failed to update password.";
}
?>

<?php
include 'db.php';

// Define user credentials
$name = "Demo User";
$email = "demo@skillswap.com";
$raw_password = "demo1234"; // Try this password
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    echo "✅ User created successfully!";
} else {
    echo "❌ Error: " . $conn->error;
}
?>

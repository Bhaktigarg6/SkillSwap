<?php
session_start();

// ✅ Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION["user_id"];

// ✅ Check if a file was uploaded
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['profile_pic']['tmp_name'];
    $file_name = basename($_FILES['profile_pic']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // ✅ Only allow certain file types
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowed_ext)) {
        die("❌ Invalid file type.");
    }

    // ✅ Save to /images directory
    $new_filename = 'images/profile_' . $user_id . '.' . $file_ext;
    if (move_uploaded_file($file_tmp, $new_filename)) {

        // ✅ Update DB with new image path
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $new_filename, $user_id);
        $stmt->execute();

        // ✅ Redirect back to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "❌ Failed to move uploaded file.";
    }
} else {
    echo "❌ No file uploaded or upload error.";
}
?>

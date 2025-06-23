<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $teach_skills = trim($_POST['teach_skills']);
    $learn_skills = trim($_POST['learn_skills']);
    
    // 🔥 New fields added
    $location = trim($_POST['location']) ?? null;
    $skill_level = trim($_POST['skill_level']) ?? null;

    $stmt = $conn->prepare("UPDATE users 
        SET name = ?, email = ?, bio = ?, teach_skills = ?, learn_skills = ?, location = ?, skill_level = ? 
        WHERE id = ?");
    $stmt->bind_param("sssssssi", $name, $email, $bio, $teach_skills, $learn_skills, $location, $skill_level, $user_id);

    if ($stmt->execute()) {
        header("Location: profile.php?success=1");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }
}
?>

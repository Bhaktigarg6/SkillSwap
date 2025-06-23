<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$teach = trim($_POST["teach_skills"]);
$learn = trim($_POST["learn_skills"]);

$stmt = $conn->prepare("UPDATE users SET teach_skills = ?, learn_skills = ? WHERE id = ?");
$stmt->bind_param("ssi", $teach, $learn, $user_id);
$stmt->execute();

header("Location: dashboard.php");
exit();
?>

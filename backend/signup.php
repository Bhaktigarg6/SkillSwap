<?php
session_start();
include 'db.php';

$error = "";
$success = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $error = "Email ID already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                // Redirect to login page after signup
                header("Location: login.php?signup=success");
                exit();
            } else {
                $error = "Signup failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up | SkillSwap</title>
    <link rel="stylesheet" href="/backend/skillexchange/style.css">
    <style>
/* 🌸 Embedded signup form styles (pastel, rounded, clean) */
.signup-container {
    background-color: #F8C8DC;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
    margin: auto;
    margin-top: 100px;
    font-family: 'Poppins', sans-serif;
}

.signup-container h2 {
    text-align: center;
    color: #e91e63;
    margin-bottom: 20px;
}

.signup-container label {
    display: block;
    margin-bottom: 6px;
    color: #444;
    font-weight: 500;
}

.signup-container input[type="text"],
.signup-container input[type="email"],
.signup-container input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #fff0f5;
    font-size: 16px;
}

.signup-container input:focus {
    outline: none;
    border-color: #B8E2DC;
}

.signup-container button {
    width: 100%;
    padding: 12px;
    background-color: #B8E2DC;
    color: #333;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.signup-container button:hover {
    background-color: #A0DAD4;
}

.signup-container .alert {
    background-color: #ffdddd;
    color: #a94442;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
    font-size: 14px;
}
</style>

    <script>
        // Simple frontend validation
        function validateForm() {
            const pwd = document.forms["signupForm"]["password"].value;
            const cpwd = document.forms["signupForm"]["confirm_password"].value;

            if (pwd !== cpwd) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="signup-container">
    <h2>Sign Up for SkillSwap</h2>

    <?php if ($error): ?>
        <div class="alert"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert" style="background-color: #d4edda; color: #155724;"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" name="signupForm" onsubmit="return validateForm()">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Sign Up</button>
    </form>

    <p style="text-align:center; margin-top: 15px;">
        Already have an account? <a href="login.php">Login</a><br>
    </p>
</div>

</body>
</html>

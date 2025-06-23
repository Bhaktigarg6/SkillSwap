<?php
session_start();
include 'db.php';

$error = "";

// Turn this ON to debug login issues
$debug = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"]; // ❌ Don't trim passwords

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();

        if ($debug) {
            echo "<pre>";
            echo "Typed Password: [{$password}]\n";
            echo "Stored Hash: [{$row['password']}]\n";
            echo "Verification: " . (password_verify($password, $row["password"]) ? "✅ PASS" : "❌ FAIL");
            echo "</pre>";
            exit();
        }

        if (password_verify($password, $row["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $row["id"];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "⚠️ User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | SkillSwap</title>
    <link rel="stylesheet" href="/backend/skillexchange/style.css">
    <style>
        .signup-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.95rem;
        }
        .signup-link a {
            color: #6b21a8;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to SkillSwap</h2>

        <?php if (!empty($error)): ?>
            <div class="alert" style="color: red;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <div class="signup-link">
            🚀 New here? <a href="signup.php">Create an account</a>
        </div>
    </div>
</body>
</html>

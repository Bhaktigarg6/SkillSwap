<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}
$adminName = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - SkillSwap</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #fff4f8;
    }
    .navbar {
      background-color: #ffd6e8;
      padding: 1rem;
      display: flex;
      justify-content: space-between;
    }
    .navbar h2 {
      margin: 0;
      color: #d63384;
    }
    .container {
      padding: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    .card {
      background-color: white;
      border-radius: 2rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 1.5rem;
    }
    .card h3 {
      margin-top: 0;
      color: #6a1b9a;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      padding: 0.5rem;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }
    .ban-btn {
      padding: 0.3rem 0.8rem;
      border-radius: 1rem;
      border: none;
      background-color: #ff4d4d;
      color: white;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <div class="navbar">
    <h2>Admin Panel</h2>
    <span>Welcome, <?php echo htmlspecialchars($adminName); ?> 👑</span>
  </div>

  <div class="container">
    <div class="card">
      <h3>📋 All Registered Users</h3>
      <table>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php
        // Assuming DB connection is already made
        include("config.php");
        $sql = "SELECT id, username, email, status FROM users";
        $result = mysqli_query($conn, $sql);

        while($row = mysqli_fetch_assoc($result)) {
          echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['username']}</td>
                  <td>{$row['email']}</td>
                  <td>" . ($row['status'] === 'banned' ? '🚫 Banned' : '✅ Active') . "</td>
                  <td>
                    <form method='POST' action='toggle_user_status.php'>
                      <input type='hidden' name='user_id' value='{$row['id']}'>
                      <input type='hidden' name='current_status' value='{$row['status']}'>
                      <button class='ban-btn' type='submit'>" . 
                      ($row['status'] === 'banned' ? 'Unban' : 'Ban') . 
                      "</button>
                    </form>
                  </td>
                </tr>";
        }
        ?>
      </table>
    </div>

    <div class="card">
      <h3>🚩 Reported Issues</h3>
      <p>Coming soon: Display of user-generated flags</p>
    </div>

    <div class="card">
      <h3>📈 Platform Stats</h3>
      <ul>
        <li>Total Users: <?php
          $countQuery = "SELECT COUNT(*) as total FROM users";
          $countResult = mysqli_query($conn, $countQuery);
          $data = mysqli_fetch_assoc($countResult);
          echo $data['total'];
        ?></li>
        <li>Total Swap Requests: Coming Soon</li>
      </ul>
    </div>
  </div>

</body>
</html>

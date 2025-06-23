<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

$sql = "SELECT * FROM leaderboard";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $teach = $row['teach_count'];
    $learn = $row['learn_count'];
    $swap = $row['total_swaps'];
    $endorse_given = $row['endorsements_given'];
    $endorse_received = $row['endorsements_received'];

    $xp = ($teach * 10) + ($learn * 8) + ($swap * 12) + ($endorse_received * 6) + ($endorse_given * 3);

    $update = $conn->prepare("UPDATE leaderboard SET xp = ? WHERE user_id = ?");
    $update->bind_param("ii", $xp, $user_id);
    $update->execute();

    // Show only current user their XP (for profile)
    if ($_SESSION['user_id'] == $user_id) {
        $_SESSION['xp_updated_msg'] = "Updated XP: " . $xp . " 🎯";
    }
}
?>

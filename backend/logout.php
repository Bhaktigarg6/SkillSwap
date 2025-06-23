<?php
session_start();
session_unset();
session_destroy();

// 🚫 Disable caching for the logout redirection
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// ✅ Redirect to login
header("Location: login.php");
exit();
?>

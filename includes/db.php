<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "skillExchange"; // Make sure this matches your DB name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

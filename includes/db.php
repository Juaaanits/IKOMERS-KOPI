<?php
// Allow containerized and local configs via env vars with sensible defaults.
$host = getenv('DB_HOST') ?: "127.0.0.1";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "NewPassHere";
$dbname = getenv('DB_NAME') ?: "ikomers_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

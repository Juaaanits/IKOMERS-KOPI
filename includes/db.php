<?php
// db_connect.php
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP default has no password
$dbname = "ikomers_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

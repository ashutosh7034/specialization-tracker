<?php
// config/db.php
// Basic MySQLi connection file for XAMPP/WAMP (procedural PHP)

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "specialization_tracker";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
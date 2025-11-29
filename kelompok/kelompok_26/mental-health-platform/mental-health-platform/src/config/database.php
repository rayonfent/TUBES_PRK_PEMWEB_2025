<?php
// src/config/database.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // ganti jika perlu
$DB_NAME = 'mental_health_platform';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
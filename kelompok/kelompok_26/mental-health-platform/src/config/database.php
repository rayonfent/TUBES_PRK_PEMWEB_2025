<?php
// Database connection — Astral Psychologist

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "mental_health_platform";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check error
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
<?php
// Small helper script to create a local admin user (for development/testing).
// USAGE: run from `src` folder: php scripts/seed_admin.php

require_once __DIR__ . '/../config/database.php';

$name = 'Admin Astral';
$email = 'admin@astral.us';
$password = 'admin';
$role = 'admin';

// Check if admin user already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows) {
    echo "Admin user already exists.\n";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,NOW())");
$stmt->bind_param('ssss', $name, $email, $hash, $role);
if ($stmt->execute()) {
    echo "Admin user created successfully. Email: $email Password: $password\n";
} else {
    echo "Failed to create admin user: " . $stmt->error . "\n";
}

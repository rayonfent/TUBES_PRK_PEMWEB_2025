<?php
require_once 'config/database.php';
if (!isset($conn)) {
    echo "Koneksi DB gagal.\n";
    exit(1);
}
// Cek apakah kolom sudah ada
$check = $conn->prepare("SHOW COLUMNS FROM konselor LIKE 'specialization'");
if ($check && $check->execute()) {
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
        echo "Kolom specialization sudah ada.\n";
        exit(0);
    }
}

$sql = "ALTER TABLE konselor ADD COLUMN specialization VARCHAR(255) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Kolom specialization ditambahkan.\n";
} else {
    echo "Gagal menambahkan kolom: {$conn->error}\n";
}

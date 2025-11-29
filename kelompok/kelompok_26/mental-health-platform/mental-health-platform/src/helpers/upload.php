<?php
// src/helpers/upload.php
function upload_image($file, $dest_folder = __DIR__ . '/../assets/img/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    if (!is_dir($dest_folder)) mkdir($dest_folder, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('img_') . '.' . $ext;
    $target = rtrim($dest_folder, '/') . '/' . $name;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        // return path relative to src/
        return 'assets/img/' . $name;
    }
    return null;
}
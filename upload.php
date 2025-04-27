<?php
session_start();
$uploadBase = __DIR__ . '/uploads';

if (isset($_SESSION['user'], $_FILES['file'])) {
    $user = $_SESSION['user'];
    $uploadDir = "$uploadBase/$user";
    $file = $_FILES['file'];
    $dest = $uploadDir . '/' . basename($file['name']);

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $_SESSION['message'] = "Upload successful.";
    } else {
        $_SESSION['message'] = "Upload failed.";
    }
}

header("Location: index.php");
exit;

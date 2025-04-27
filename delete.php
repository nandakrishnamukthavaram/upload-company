<?php
session_start();
$uploadBase = __DIR__ . '/uploads';

if (isset($_SESSION['user'], $_GET['file'])) {
    $user = $_SESSION['user'];
    $filename = basename($_GET['file']);
    $path = "$uploadBase/$user/$filename";

    if (file_exists($path)) {
        unlink($path);
    }
}

header("Location: index.php");
exit;

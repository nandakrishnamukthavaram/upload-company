<?php
session_start();

$uploadBase = __DIR__ . '/uploads';

if (!isset($_SESSION['user'], $_GET['file'])) {
    http_response_code(403);
    exit('Access denied');
}

$username = $_SESSION['user'];
$filename = basename($_GET['file']);  // Prevent directory traversal
$filepath = "$uploadBase/$username/$filename";

if (file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
} else {
    http_response_code(404);
    exit('File not found');
}

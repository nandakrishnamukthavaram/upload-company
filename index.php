<?php
session_start();
require_once 'auth.php';

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

include 'inc/header.php';

if (!isset($_SESSION['user'])) {
    include 'inc/login_form.php';
    include 'inc/signup_form.php';
} else {
    include 'inc/upload_form.php';
    include 'inc/file_list.php';
}

include 'inc/footer.php';
?>

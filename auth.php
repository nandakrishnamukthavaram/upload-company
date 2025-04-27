<?php
$uploadBase = __DIR__ . '/uploads';
$userFile = __DIR__ . '/users.json';

if (!file_exists($uploadBase)) mkdir($uploadBase);
if (!file_exists($userFile)) file_put_contents($userFile, json_encode([]));

$users = json_decode(file_get_contents($userFile), true);

// Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (!isset($users[$username])) {
        $users[$username] = password_hash($password, PASSWORD_DEFAULT);
        file_put_contents($userFile, json_encode($users));
        mkdir("$uploadBase/$username", 0777, true);
        $_SESSION['user'] = $username;
        $_SESSION['message'] = "Signup successful!";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['message'] = "Username already exists.";
    }
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['user'] = $username;
        $_SESSION['message'] = "Login successful!";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['message'] = "Invalid credentials.";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

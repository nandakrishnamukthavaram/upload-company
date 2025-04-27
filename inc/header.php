<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Company</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h1>📁 Upload Company</h1>
<?php if (isset($_SESSION['user'])): ?>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong> |
    <a href="?logout">Logout</a></p>
<?php endif; ?>
<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php
session_start();

$uploadBase = __DIR__ . '/uploads';
$userFile = __DIR__ . '/users.json';

// Create uploads folder if not exists
if (!file_exists($uploadBase)) mkdir($uploadBase);
if (!file_exists($userFile)) file_put_contents($userFile, json_encode([]));

// Load users
$users = json_decode(file_get_contents($userFile), true);

// Sign up
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (!isset($users[$username])) {
        $users[$username] = password_hash($password, PASSWORD_DEFAULT);
        file_put_contents($userFile, json_encode($users));
        mkdir("$uploadBase/$username", 0777, true);
        
        // Create view_existing.php for the user
        $viewPage = "$uploadBase/$username/view_existing.php";
        file_put_contents($viewPage, generateViewExistingPage($username));

        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $message = "Username already taken.";
    }
}

// Login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $message = "Invalid credentials.";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// File upload
if (isset($_POST['upload_file']) && isset($_SESSION['user'], $_FILES['file'])) {
    $username = $_SESSION['user'];
    $uploadDir = "$uploadBase/$username";
    $file = $_FILES['file'];
    $destination = $uploadDir . '/' . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $message = "File uploaded successfully!";
    } else {
        $message = "File upload failed!";
    }
}

// Function to generate view_existing.php content
function generateViewExistingPage($username) {
    return <<<PHP
<?php
session_start();
if (!isset(\$_SESSION['user']) || \$_SESSION['user'] !== '$username') {
    header("Location: ../../index.php");
    exit;
}
\$dir = __DIR__;
\$files = array_diff(scandir(\$dir), ['.', '..', 'view_existing.php']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Files</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="body-container">
    <div class="return-container">
        <a href="../../index.php?upload" class="button return-button">⬅ Return to Upload</a>
    </div>

    <h1 class="main-title">Your Files</h1>
    <div class="grid">
        <?php foreach (\$files as \$file): 
            \$ext = strtolower(pathinfo(\$file, PATHINFO_EXTENSION));
            \$isImage = in_array(\$ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        ?>
            <div class="tile">
                <div class="preview">
                    <?php if (\$isImage): ?>
                        <img src="<?= htmlspecialchars(\$file) ?>" alt="">
                    <?php else: ?>
                        <div class="icon"><?= strtoupper(\$ext) ?></div>
                    <?php endif; ?>
                </div>
                <div class="filename"><?= htmlspecialchars(\$file) ?></div>
                <div class="actions">
                    <a href="<?= htmlspecialchars(\$file) ?>" target="_blank" class="action-link">👁 View</a>
                    <a href="<?= htmlspecialchars(\$file) ?>" download class="action-link">⬇ Download</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
PHP;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Company</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="body-container">

<h1 class="main-title">Upload Company</h1>

<?php if (isset($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!isset($_SESSION['user'])): ?>
    <?php if (isset($_GET['signup'])): ?>
        <!-- Sign Up Form -->
        <form method="post" class="form">
            <h2 class="section-title">Sign Up</h2>
            <input name="username" type="text" class="input" placeholder="Username" required>
            <input name="password" type="password" class="input" placeholder="Password" required>
            <button type="submit" name="signup" class="button">Sign Up</button>
            <!-- Link back to Login -->
            <p class="signup-prompt">Already a member? <a href="index.php" class="signup-link">Login here</a></p>
        </form>

    <?php else: ?>
        <!-- Login Form -->
        <form method="post" class="form">
            <h2 class="section-title">Login</h2>
            <input name="username" type="text" class="input" placeholder="Username" required>
            <input name="password" type="password" class="input" placeholder="Password" required>
            <button type="submit" name="login" class="button">Login</button>
            <!-- Sign Up Link -->
            <p class="signup-prompt">New user? <a href="?signup" class="signup-link">Sign up here</a></p>
        </form>

    <?php endif; ?>
<?php elseif (isset($_SESSION['user'])): ?>
    <!-- Welcome Message and Menu (Already Logged In) -->
    <p class="welcome-message">
        Welcome, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>!
        <a href="?logout" class="logout-link">Logout</a>
    </p>

    <div class="menu">
        <a href="?upload" class="menu-link">Upload Files</a>
        <a href="uploads/<?= urlencode($_SESSION['user']) ?>/view_existing.php" class="menu-link">View Existing Files</a>
    </div>

    <?php if (isset($_GET['upload'])): ?>
        <form method="post" enctype="multipart/form-data" class="form">
            <h2 class="section-title">Upload Files</h2>
            <input type="file" name="file" class="input" required>
            <button type="submit" name="upload_file" class="button">Upload</button>
        </form>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>

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
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }
        .tile {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            background: #f9f9f9;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.1);
        }
        .tile img {
            max-height: 100px;
            max-width: 100%;
            object-fit: contain;
        }
        .filename {
            font-size: 0.85rem;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>Your Files</h1>
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
                        <div><?= strtoupper(\$ext) ?></div>
                    <?php endif; ?>
                </div>
                <div class="filename"><?= htmlspecialchars(\$file) ?></div>
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
    <title>Mini Drive - Auth</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; }
        h1, h2 { color: #333; }
        form { margin: 1rem 0; }
        .file-item { margin-bottom: 0.5rem; }
        .message { color: green; }
        .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.tile {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 0.5rem;
    text-align: center;
    background: #f9f9f9;
    box-shadow: 2px 2px 6px rgba(0,0,0,0.1);
}

.tile .preview {
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    overflow: hidden;
}

.tile img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.tile .icon {
    font-size: 1.2rem;
    font-weight: bold;
    color: #555;
    background: #e0e0e0;
    padding: 1rem;
    border-radius: 6px;
}

.filename {
    font-size: 0.85rem;
    word-wrap: break-word;
    margin-bottom: 0.3rem;
}

.actions a {
    font-size: 0.8rem;
    color: #007BFF;
    text-decoration: none;
}

    </style>
</head>
<body>

<h1>📁 Mini Drive</h1>

<?php if (isset($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!isset($_SESSION['user'])): ?>
    <h2>Login</h2>
    <form method="post">
        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <h2>Sign Up</h2>
    <form method="post">
        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>
        <button type="submit" name="signup">Sign Up</button>
    </form>
<?php else: ?>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>! <a href="?logout">Logout</a></p>

    <div class="menu">
        <a href="?upload">Upload Files</a>
        <a href="uploads/<?= urlencode($_SESSION['user']) ?>/view_existing.php" target="_blank">View Existing Files</a>
    </div>

    <?php if (isset($_GET['upload'])): ?>
        <h2>Upload Files</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" name="upload_file">Upload</button>
        </form>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>

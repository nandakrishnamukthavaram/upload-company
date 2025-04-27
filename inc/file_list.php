<?php
$uploadBase = __DIR__ . '/../uploads';
$userDir = "$uploadBase/" . $_SESSION['user'];
$files = file_exists($userDir) ? array_diff(scandir($userDir), ['.', '..']) : [];
?>

<h2>Your Files</h2>
<?php if (empty($files)): ?>
    <p>No files yet.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($files as $file): 
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $fileUrl = "download.php?file=" . urlencode($file);
        ?>
            <div class="tile">
                <div class="preview">
                    <?php if ($isImage): ?>
                        <img src="<?= $fileUrl ?>" alt="">
                    <?php else: ?>
                        <div class="icon"><?= strtoupper($ext) ?></div>
                    <?php endif; ?>
                </div>
                <div class="filename"><?= htmlspecialchars($file) ?></div>
                <div class="actions">
                    <a href="<?= $fileUrl ?>">Download</a> |
                    <a href="delete.php?file=<?= urlencode($file) ?>" onclick="return confirm('Delete this file?');" style="color:red;">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

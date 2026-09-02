<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filepath']) && isset($_POST['content'])) {
    $filepath = $_POST['filepath'];
    $content = base64_decode($_POST['content']);
    
    // Security: only allow specific files
    $allowed = ['application/views/admin/auth/login-4.php', 'skin/hrsale_assets/css/hrsale/xin_login_3.css'];
    if (!in_array($filepath, $allowed)) {
        die("File not allowed");
    }
    
    if (file_put_contents($filepath, $content)) {
        echo "Written: $filepath (" . strlen($content) . " bytes)";
    } else {
        echo "Failed to write: $filepath";
    }
} else {
?>
<!DOCTYPE html>
<html>
<head><title>File Writer</title></head>
<body>
<h2>HRM File Writer</h2>
<form method="POST">
    <select name="filepath">
        <option value="application/views/admin/auth/login-4.php">login-4.php</option>
        <option value="skin/hrsale_assets/css/hrsale/xin_login_3.css">xin_login_3.css</option>
    </select><br><br>
    <textarea name="content" rows="20" cols="80" placeholder="Paste base64-encoded content here"></textarea><br><br>
    <button type="submit">Write File</button>
</form>
</body>
</html>
<?php } ?>

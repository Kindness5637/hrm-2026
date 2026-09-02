<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

$cwd = getcwd(); // should be /htdocs/
echo "Working dir: $cwd<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zipfile'])) {
    $tmp = $_FILES['zipfile']['tmp_name'];
    $size = $_FILES['zipfile']['size'];
    echo "File size: $size bytes<br>";
    
    // Save to current directory (htdocs)
    $zippath = $cwd . '/app.zip';
    
    if (move_uploaded_file($tmp, $zippath)) {
        echo "Saved to $zippath<br>";
        echo "Extracting...<br>";
        
        $output = [];
        $rc = 0;
        // Extract to current directory
        exec("unzip -o " . escapeshellarg($zippath) . " 2>&1", $output, $rc);
        echo "unzip code: $rc<br>";
        if ($output) echo "<pre>" . implode("\n", array_slice($output, -10)) . "</pre>";
        
        // Move from hrm/ subfolder
        if (is_dir('hrm')) {
            $items = array_diff(scandir('hrm'), ['.', '..']);
            $count = 0;
            foreach ($items as $item) {
                if (rename("hrm/$item", $item)) $count++;
            }
            rmdir('hrm');
            echo "Moved $count items to root.<br>";
        }
        
        @unlink($zippath);
        @unlink($cwd . '/upload.php');
        echo "<b>DONE! Visit http://hrm-2026.xo.je/</b>";
    } else {
        echo "Failed to save file";
    }
} else {
?>
<!DOCTYPE html>
<html><body>
<h2>Upload HRM Zip</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="zipfile" accept=".zip" required><br><br>
    <button type="submit">Upload & Extract</button>
</form>
</body></html>
<?php } ?>

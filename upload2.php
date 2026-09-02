<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

$tmp_dir = '/tmp/hrm_parts/';
if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (isset($_FILES['partfile'])) {
        $name = $_FILES['partfile']['name'];
        $dest = $tmp_dir . $name;
        if (move_uploaded_file($_FILES['partfile']['tmp_name'], $dest)) {
            echo "Uploaded: $name (" . filesize($dest) . " bytes)<br>";
        } else {
            echo "Failed to upload: $name<br>";
        }
    }
    
    // Check if all parts uploaded and merge
    $parts = glob($tmp_dir . 'hrm-part-*');
    echo "Parts uploaded: " . count($parts) . "/6<br>";
    
    if (count($parts) >= 6) {
        echo "All parts received! Merging...<br>";
        sort($parts);
        $merged = '/tmp/hrm-app.tar.gz';
        $out = fopen($merged, 'w');
        foreach ($parts as $p) {
            $in = fopen($p, 'r');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }
        fclose($out);
        echo "Merged! Size: " . filesize($merged) . "<br>";
        
        echo "Extracting...<br>";
        $output = [];
        $rc = 0;
        exec("tar xzf $merged -C /htdocs/ 2>&1", $output, $rc);
        echo "tar return: $rc<br>";
        if ($output) echo "<pre>" . implode("\n", array_slice($output, 0, 10)) . "</pre>";
        
        // Move from hrm/ subfolder to root
        if (is_dir('/htdocs/hrm')) {
            exec("cp -rn /htdocs/hrm/* /htdocs/ 2>&1");
            exec("rm -rf /htdocs/hrm");
            echo "Moved files to root.<br>";
        }
        
        // Cleanup
        exec("rm -rf $tmp_dir $merged");
        @unlink('/htdocs/upload2.php');
        
        echo "<b>Done! Visit http://hrm-2026.xo.je/</b>";
    }
} else {
?>
<!DOCTYPE html>
<html>
<body>
<h2>Upload HRM Parts (one at a time)</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="partfile" required><br><br>
    <button type="submit">Upload Part</button>
</form>
<p>Upload parts in order: aa, ab, ac, ad, ae, af</p>
</body>
</html>
<?php } ?>

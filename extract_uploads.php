<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('uploads.zip')) {
    die("uploads.zip not found");
}

echo "Extracting uploads...<br>";
$output = [];
$rc = 0;
exec("unzip -o uploads.zip 2>&1", $output, $rc);
echo "Code: $rc<br>";

@unlink('uploads.zip');
@unlink('extract_uploads.php');

echo "<b>Done! Refresh the site.</b>";

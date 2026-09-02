<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP version: " . phpversion() . "<br>";
echo "CWD: " . getcwd() . "<br>";

// List files in current directory
$files = array_filter(scandir('.'));
echo "Files here: " . implode(', ', $files) . "<br><br>";

// Check parent directory
if (is_dir('..')) {
    $parent_files = array_filter(scandir('..'));
    echo "Files in parent: " . implode(', ', $parent_files) . "<br><br>";
}

// Try multiple zip locations
$zip_paths = [
    'hrm-upload.zip',
    '../hrm-upload.zip',
    '/htdocs/hrm-upload.zip',
    '/tmp/hrm-upload.zip',
];

$zip_file = null;
foreach ($zip_paths as $path) {
    if (file_exists($path)) {
        echo "Found zip at: $path (size: " . filesize($path) . ")<br>";
        $zip_file = $path;
        break;
    }
}

if (!$zip_file) {
    echo "<b>ZIP NOT FOUND!</b><br>";
    echo "Please upload hrm-upload.zip to the htmldocs folder using File Manager.<br>";
    die();
}

echo "Opening $zip_file ...<br>";

// Try unzip command first (most reliable)
echo "Attempting shell unzip...<br>";
$output = [];
$return_code = 0;
exec("unzip -o " . escapeshellarg($zip_file) . " -d /htdocs/ 2>&1", $output, $return_code);
echo "unzip return code: $return_code<br>";
if ($output) echo "<pre>" . implode("\n", array_slice($output, 0, 30)) . "...</pre>";

// Move from hrm/ subfolder to root
if (is_dir('/htdocs/hrm')) {
    echo "<br>Moving files from /htdocs/hrm/ to /htdocs/...<br>";
    exec("cp -r /htdocs/hrm/* /htdocs/ 2>&1", $out2, $rc2);
    exec("rm -rf /htdocs/hrm 2>&1", $out3, $rc3);
    echo "Done moving.<br>";
}

// Clean up
@unlink($zip_file);
@unlink('/htdocs/extract.php');
@unlink('/htdocs/extract2.php');

echo "<br><b>Extraction complete! Visit http://hrm-2026.xo.je/</b>";

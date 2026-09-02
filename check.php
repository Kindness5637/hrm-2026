<?php
echo "CWD: " . getcwd() . "<br>";
$files = array_filter(scandir('.'));
echo "Files: " . implode(', ', $files) . "<br><br>";

// Check if specific files exist
$check = ['index.php', 'hrm-upload.zip', '.htaccess', 'robots.txt', 'composer.json'];
foreach ($check as $f) {
    echo "$f: " . (file_exists($f) ? 'YES' : 'NO') . "<br>";
}

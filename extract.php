<?php
// Check ZipArchive availability
echo "ZipArchive: " . (class_exists('ZipArchive') ? 'YES' : 'NO') . "<br>";

if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    $res = $zip->open('hrm-upload.zip');
    echo "Open result: $res<br>";
    
    if ($res === TRUE) {
        echo "Extracting...<br>";
        $zip->extractTo('/htdocs/');
        $zip->close();
        echo "Extracted!<br>";
        
        if (is_dir('/htdocs/hrm')) {
            $items = array_diff(scandir('/htdocs/hrm'), ['.', '..']);
            foreach ($items as $item) {
                rename("/htdocs/hrm/$item", "/htdocs/$item");
            }
            rmdir('/htdocs/hrm');
            echo "Moved files to root.<br>";
        }
        
        @unlink('/htdocs/hrm-upload.zip');
        @unlink('/htdocs/extract.php');
        echo "<b>Done! Visit http://hrm-2026.xo.je/</b>";
    } else {
        echo "Error opening zip. Code: $res<br>";
        // Try shell unzip as fallback
        echo "Trying shell unzip...<br>";
        $output = shell_exec('unzip -o hrm-upload.zip -d /htdocs/ 2>&1');
        echo "<pre>$output</pre>";
        if (is_dir('/htdocs/hrm')) {
            shell_exec('shopt -s dotglob && mv /htdocs/hrm/* /htdocs/ 2>&1');
            rmdir('/htdocs/hrm');
        }
        @unlink('/htdocs/hrm-upload.zip');
        @unlink('/htdocs/extract.php');
        echo "<b>Done via shell! Visit http://hrm-2026.xo.je/</b>";
    }
} else {
    echo "ZipArchive not available, trying shell unzip...<br>";
    $output = shell_exec('unzip -o hrm-upload.zip -d /htdocs/ 2>&1');
    echo "<pre>$output</pre>";
    if (is_dir('/htdocs/hrm')) {
        shell_exec('shopt -s dotglob && mv /htdocs/hrm/* /htdocs/ 2>&1');
        rmdir('/htdocs/hrm');
    }
    @unlink('/htdocs/hrm-upload.zip');
    @unlink('/htdocs/extract.php');
    echo "<b>Done via shell! Visit http://hrm-2026.xo.je/</b>";
}

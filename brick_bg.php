<?php
if (!function_exists('imagecreatetruecolor')) {
    // GD not available — serve a simple SVG brick pattern
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=86400');
    echo '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
  <defs>
    <pattern id="brick" x="0" y="0" width="84" height="34" patternUnits="userSpaceOnUse">
      <rect width="84" height="34" fill="#d2d2d2"/>
      <rect x="0" y="0" width="80" height="30" fill="#ebebeb" rx="1"/>
      <rect x="42" y="17" width="80" height="30" fill="#ebebeb" rx="1"/>
    </pattern>
  </defs>
  <rect width="100%" height="100%" fill="url(#brick)"/>
</svg>';
    exit;
}

$width  = 1200;
$height = 800;
$brickW = 80;
$brickH = 30;
$mortar = 4;

$img = imagecreatetruecolor($width, $height);
$brickColor  = imagecolorallocate($img, 235, 235, 235);
$mortarColor = imagecolorallocate($img, 200, 200, 200);
$shadowColor = imagecolorallocate($img, 215, 215, 215);
$highlight   = imagecolorallocate($img, 245, 245, 245);

imagefilledrectangle($img, 0, 0, $width, $height, $mortarColor);

$rows = ceil($height / ($brickH + $mortar));
$cols = ceil($width  / ($brickW + $mortar));

for ($row = 0; $row < $rows; $row++) {
    $offset = ($row % 2 == 0) ? 0 : ($brickW + $mortar) / 2;
    for ($col = -1; $col < $cols + 1; $col++) {
        $x = $col * ($brickW + $mortar) + $offset;
        $y = $row * ($brickH + $mortar);
        imagefilledrectangle($img, $x, $y, $x + $brickW - 1, $y + $brickH - 1, $brickColor);
        imageline($img, $x, $y, $x + $brickW - 1, $y, $shadowColor);
        imageline($img, $x, $y, $x, $y + $brickH - 1, $shadowColor);
        imageline($img, $x + 1, $y + 1, $x + $brickW - 2, $y + 1, $highlight);
    }
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
imagepng($img);
imagedestroy($img);

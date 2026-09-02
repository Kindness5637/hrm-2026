<?php
// Brick wall pattern generator for login background
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

$width  = 1200;
$height = 800;
$brickW = 80;
$brickH = 30;
$mortar = 4;

$img = imagecreatetruecolor($width, $height);

// Colors
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

imagepng($img);
imagedestroy($img);

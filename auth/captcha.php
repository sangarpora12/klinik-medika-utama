<?php
// auth/captcha.php – Menghasilkan gambar CAPTCHA matematika sederhana
require_once __DIR__ . '/../includes/config.php';
startSecureSession();

$a = random_int(1, 9);
$b = random_int(1, 9);
$_SESSION[CAPTCHA_SESSION_KEY] = $a + $b;

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$w = 140; $h = 44;
$img = imagecreatetruecolor($w, $h);

// Warna
$bg     = imagecolorallocate($img, 240, 248, 255);
$noise  = imagecolorallocate($img, 180, 200, 220);
$txtClr = imagecolorallocate($img, 25,  55,  100);

imagefill($img, 0, 0, $bg);

// Noise: garis & titik
for ($i = 0; $i < 6; $i++) {
    imageline($img, random_int(0,$w), random_int(0,$h),
                    random_int(0,$w), random_int(0,$h), $noise);
}
for ($i = 0; $i < 60; $i++) {
    imagesetpixel($img, random_int(0,$w), random_int(0,$h), $noise);
}

$text = "$a + $b = ?";
$font = 5;
$tw   = imagefontwidth($font) * strlen($text);
$th   = imagefontheight($font);
$x    = (int)(($w - $tw) / 2);
$y    = (int)(($h - $th) / 2);

// Sedikit distorsi karakter
foreach (str_split($text) as $idx => $char) {
    $cy = $y + random_int(-3, 3);
    imagechar($img, $font, $x + $idx * imagefontwidth($font), $cy, $char, $txtClr);
}

imagepng($img);
imagedestroy($img);

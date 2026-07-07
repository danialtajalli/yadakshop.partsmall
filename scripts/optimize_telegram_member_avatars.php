<?php

declare(strict_types=1);

$dir = dirname(__DIR__).'/public/img/telegram-members';
$size = 256;
$quality = 82;

foreach (glob($dir.'/member-*.jpg') as $file) {
    $bytes = file_get_contents($file, false, null, 0, 8);

    if ($bytes === false) {
        fwrite(STDERR, "Failed to read {$file}\n");

        continue;
    }

    $img = match (true) {
        str_starts_with($bytes, "\x89PNG\r\n\x1a\n") => imagecreatefrompng($file),
        str_starts_with($bytes, "\xFF\xD8\xFF") => imagecreatefromjpeg($file),
        default => false,
    };

    if ($img === false) {
        fwrite(STDERR, "Unsupported image format: {$file}\n");

        continue;
    }

    $width = imagesx($img);
    $height = imagesy($img);
    $resized = imagecreatetruecolor($size, $size);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $size, $size, $width, $height);
    imagejpeg($resized, $file, $quality);
    imagedestroy($img);
    imagedestroy($resized);

    echo basename($file).' '.filesize($file)."\n";
}

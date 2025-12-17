<?php

// Resize water vending pin image
$sourcePath = __DIR__ . '/public/images/map-pins/water_vending_pin.png';
$destinationPath = __DIR__ . '/public/images/map-pins/water_vending_pin_small.png';

// Check if source exists
if (!file_exists($sourcePath)) {
    die("Source image not found at: $sourcePath\n");
}

// Get original dimensions
list($width, $height) = getimagesize($sourcePath);
echo "Original size: {$width}x{$height}\n";

// Create image from source
$source = imagecreatefrompng($sourcePath);

// Create new image with 24x24
$newWidth = 24;
$newHeight = 24;
$destination = imagecreatetruecolor($newWidth, $newHeight);

// Preserve transparency
imagealphablending($destination, false);
imagesavealpha($destination, true);
$transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);

// Resize
imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

// Save
imagepng($destination, $destinationPath);

// Free memory
imagedestroy($source);
imagedestroy($destination);

echo "Resized image saved to: $destinationPath\n";
echo "New size: {$newWidth}x{$newHeight}\n";

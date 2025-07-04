<?php
// Your base64-encoded PNG image string
$base64Image = 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8HnozAAAA0lEQVRIDbXBAQEAAAABIP6PzgpV4XARi4G9Z2+T/sFwpU6U7WGb+zyf1yI30CV9xOlQxxLlQA5k6e+XfUgbZZMZBauA+gL5wKnk/9s0pwNdLE+VRVhOD2bsOn3Fq8D/JFfBhfl23hDEg2c2FplY0FX6p+ZG0ihm3ZQWfpw==';

// Decode the base64 string to binary data
$imageData = base64_decode($base64Image, true);

// Validate base64 string
if ($imageData === false) {
    die('Invalid base64 string');
}

// Save to a temporary file
$tempFile = tempnam('/', 'test1.jpeg');
file_put_contents($tempFile, $imageData);

// Load the image from the temporary file
$img = imagecreatefromjpeg('/test.jpeg');  // Use appropriate function based on your image format

if (!$img) {
    die('Error loading image');
}

// Get the width and height of the image
$width = imagesx($img);
$height = imagesy($img);

// Initialize an empty array for the RGB values
$rgbValues = [];

// Loop through each pixel to extract RGB values
for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        // Get the RGBA values for the pixel
        $rgba = imagecolorat($img, $x, $y);
        
        // Extract red, green, blue, and alpha components
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;
        
        // Normalize the values to the range [0, 1]
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        // Store the RGB values as an array
        $rgbValues[$y][$x] = [$r, $g, $b];
    }
}

// Calculate the "k" channel (1 - max(R, G, B))
$kChannel = [];
for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        // Get the RGB values for the current pixel
        list($r, $g, $b) = $rgbValues[$y][$x];
        
        // Calculate the max of R, G, B
        $maxValue = max($r, $g, $b);
        
        // Calculate the k channel
        $kChannel[$y][$x] = 1 - $maxValue;
    }
}

// At this point, the $kChannel array contains the values for the k channel
// You can now perform further operations on this channel as needed

// For demonstration, print out the k channel values
echo '<pre>';
print_r($kChannel);
echo '</pre>';

// Clean up the temporary file
unlink($tempFile);
imagedestroy($img);
?>

<?php
if (!isset($_GET['path'])) {
    die('<b>Error:</b> Missing \'path\' parameter.');
}

$path = $_GET['path'];

// Normalize path
$path = urldecode($path);

// Return the image
header('Content-Type: image/png');
echo file_get_contents('/var/www/html/images/' . $path);
?>
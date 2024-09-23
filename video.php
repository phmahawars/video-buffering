<?php
// Path to your video file
$videoPath = 'video.mp4';

// Check if the file exists
if (!file_exists($videoPath)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Get the file size
$filesize = filesize($videoPath);
$length = $filesize;

// Set headers
header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);

// Handle range requests
$range = 0;
$end = $filesize - 1;

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = intval(substr($_SERVER['HTTP_RANGE'], 6));
    if ($range > $end) {
        header("HTTP/1.1 416 Requested Range Not Satisfiable");
        exit;
    }
    header("HTTP/1.1 206 Partial Content");
    header("Content-Range: bytes $range-$end");
    $length = $end - $range + 1;
    fseek(fopen($videoPath, 'rb'), $range);
} else {
    header("HTTP/1.1 200 OK");
}

header("Content-Length: " . $length);
readfile($videoPath);
exit;
?>

<?php
// Path to your video file
$videoFile = 'video/earth_in_4k_by_onehome.mp4';

// Check if the file exists
if (!file_exists($videoFile)) {
    header("HTTP/1.1 404 Not Found");
    exit("Video file not found.");
}

// Get the file size and MIME type
$fileSize = filesize($videoFile);
$mimeType = mime_content_type($videoFile);

// Set headers for video stream
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Accept-Ranges: bytes');
header('Cache-Control: no-cache');
header('Pragma: no-cache');

// Handle HTTP Range Requests for partial content delivery
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    list(, $range) = explode('=', $range, 2);
    $range = explode('-', $range);
    $start = intval($range[0]);
    $end = isset($range[1]) && is_numeric($range[1]) ? intval($range[1]) : $fileSize - 1;
    
    header('HTTP/1.1 206 Partial Content');
    header("Content-Range: bytes $start-$end/$fileSize");
    header('Content-Length: ' . ($end - $start + 1));
} else {
    $start = 0;
    $end = $fileSize - 1;
}

// Open the video file
$fp = fopen($videoFile, 'rb');
if ($fp === false) {
    header("HTTP/1.1 500 Internal Server Error");
    exit("Could not open video file.");
}

// Seek to the requested byte position
fseek($fp, $start);

// Stream the file in larger chunks
$bufferSize = 1024 * 64; // 32KB buffer size for faster loading
while (!feof($fp) && ($start <= $end)) {
    $bytesToRead = min($bufferSize, $end - $start + 1);
    echo fread($fp, $bytesToRead);
    
    // Flush the output buffer immediately
    ob_flush();
    flush();

    // Move the pointer forward
    $start += $bytesToRead;

    // Introduce a slight delay to help with buffering on slow connections
    // usleep(100000); // 0.1 seconds (100ms)
}

fclose($fp);
?>

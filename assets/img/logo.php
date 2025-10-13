<?php
// Serve the existing PNG logo through PHP so pages can reference logo.php as the favicon.
// This returns the PNG bytes with appropriate headers and a small cache-control.
$path = __DIR__ . '/logo.png';
if (!file_exists($path)) {
    http_response_code(404);
    exit;
}
header('Content-Type: image/png');
header('Cache-Control: public, max-age=3600');
readfile($path);

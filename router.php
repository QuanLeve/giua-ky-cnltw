<?php
declare(strict_types=1);
$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if (preg_match('~^/(?:var|seed\.php)(?:/|$)~i', $path)
    || preg_match('~^/uploads/.*\.(?:php[0-9]?|phtml|phar|cgi|pl|shtml)$~i', $path)) {
    http_response_code(403);
    exit('Truy cập bị từ chối.');
}
return false;

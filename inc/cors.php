<?php
// REST API / CORS
if (!defined('ABSPATH')) exit;

function add_cors_headers()
{
    $http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
    if (
        $http_origin == "http://localhost:3000" ||
        $http_origin == "https://localhost:3000" ||
        strpos($http_origin, 'stegetfore.nu') !== false
    ) {
        header("Access-Control-Allow-Origin: $http_origin");
    } else {
        header("Access-Control-Allow-Origin: *");
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, Origin, X-Requested-With, Accept");
        header("Access-Control-Max-Age: 86400");
        exit(0);
    }
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    header("Access-Control-Allow-Credentials: true");
}
add_action('rest_api_init', function () {
    remove_action('send_headers', 'add_cors_headers');
    add_cors_headers();
}, 15);
add_action('send_headers', 'add_cors_headers');

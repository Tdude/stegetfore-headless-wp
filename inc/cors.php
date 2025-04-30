<?php
// REST API / CORS
if (!defined('ABSPATH')) exit;

function add_cors_headers()
{
    // Allow requests from the requesting origin (required for Authorization header)
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, Origin, X-Requested-With, Accept");
        header("Access-Control-Max-Age: 86400");
        header("Access-Control-Allow-Credentials: true");
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

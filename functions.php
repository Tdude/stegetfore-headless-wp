<?php
/**
 * functions.php
 *
 */

if (!defined('ABSPATH')) exit;

// Theme Setup
function headless_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    // Debug - Remove in production
    error_log('Theme setup function running');
}
add_action('after_setup_theme', 'headless_theme_setup');

// Enable CORS for REST API
add_action('init', function() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");

    // Debug - Remove in production
    error_log('CORS headers set');
});

// Load theme components - with error checking
$required_files = [
    '/inc/post-types/portfolio.php',
    '/inc/meta-fields/register-meta.php',
    '/inc/rest/endpoints.php',
    '/inc/admin/theme-options.php'
];

foreach ($required_files as $file) {
    $file_path = get_template_directory() . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        error_log('Loaded: ' . $file); // Debug - Remove in production
    } else {
        error_log('Missing required file: ' . $file); // Debug - Remove in production
    }
}

// Test REST endpoint
add_action('rest_api_init', function() {
    register_rest_route('headless-theme/v1', '/test', [
        'methods' => 'GET',
        'callback' => function() {
            return [
                'status' => 'success',
                'message' => 'Headless theme REST API is working!'
            ];
        },
        'permission_callback' => '__return_true'
    ]);
});
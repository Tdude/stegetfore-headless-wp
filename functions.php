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
    add_theme_support('menus');

    // Debug - Remove in production
    error_log('Theme setup function running');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'steget'),
        'footer' => __('Footer Menu', 'steget')
    ]);
}
add_action('after_setup_theme', 'headless_theme_setup');


// These can be nuked
function debug_menu_locations() {
    error_log('Registered nav menus: ' . print_r(get_registered_nav_menus(), true));
    error_log('Current nav menu locations: ' . print_r(get_nav_menu_locations(), true));
}
add_action('init', 'debug_menu_locations');

add_filter('show_admin_bar', '__return_true');

// Debug filter to check if menus are being hidden
add_filter('current_theme_supports-menus', function($supports) {
    error_log('Theme supports menus: ' . ($supports ? 'yes' : 'no'));
    return $supports;
});



// CORS header for REST API
function add_cors_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

    // Debug headers - remove in production (in wp-config.pp)
    error_log('CORS headers added');
    error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
}
add_action('init', 'add_cors_headers');

// Debug REST API requests - remove in production
add_action('rest_api_init', function() {
    error_log('REST API request received');
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
    register_rest_route('steget/v1', '/test', [
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

// Need a menu
add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary' => __('Primary Menu', 'stegetfore-headless-wp')
    ]);
});
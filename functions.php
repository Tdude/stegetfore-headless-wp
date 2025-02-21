<?php
/**
 * functions.php
 */

if (!defined('ABSPATH')) exit;

// Theme Setup
function headless_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('menus');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'steget'),
        'footer' => __('Footer Menu', 'steget')
    ]);
}
add_action('after_setup_theme', 'headless_theme_setup');

// CORS header for REST API
function add_cors_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
}
add_action('init', 'add_cors_headers');

// Load theme components
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
    }
}

add_filter('show_admin_bar', '__return_true');
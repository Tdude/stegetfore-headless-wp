<?php
/**
 * functions.php
 *
 * */
if (!defined('ABSPATH')) exit;

// Theme Setup
function headless_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('editor-styles');
    
    // If headless mode is enabled, disable unnecessary features
    if (get_option('headless_mode_enabled')) {
        remove_action('wp_head', 'wp_print_styles');
        remove_action('wp_head', 'wp_print_scripts');
        // Add more optimizations
    }
}
add_action('after_setup_theme', 'headless_theme_setup');

// Load theme components
require_once get_template_directory() . '/inc/post-types/portfolio.php';
require_once get_template_directory() . '/inc/meta-fields/register-meta.php';
require_once get_template_directory() . '/inc/rest/endpoints.php';
require_once get_template_directory() . '/inc/admin/theme-options.php';

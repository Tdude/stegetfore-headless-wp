<?php
// Theme Setup
if (!defined('ABSPATH')) exit;

function headless_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('menus');
    register_nav_menus([
        'primary' => __('Primary Menu', 'steget'),
        'footer' => __('Footer Menu', 'steget')
    ]);
}
add_action('after_setup_theme', 'headless_theme_setup');

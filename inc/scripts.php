<?php
// Scripts & Styles
if (!defined('ABSPATH')) exit;

function headless_theme_dequeue_plugin_styles() {
    // ...
}
add_action('wp_enqueue_scripts', 'headless_theme_dequeue_plugin_styles', 20);

function enqueue_evaluation_scripts() {
    // ...
}
add_action('wp_enqueue_scripts', 'enqueue_evaluation_scripts');

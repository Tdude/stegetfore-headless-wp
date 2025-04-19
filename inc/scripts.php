<?php
// Scripts & Styles
if (!defined('ABSPATH')) exit;

function headless_theme_dequeue_plugin_styles()
{
    wp_dequeue_style('plugin-style-handle');
}
add_action('wp_enqueue_scripts', 'headless_theme_dequeue_plugin_styles', 20);

function enqueue_evaluation_scripts()
{
    wp_enqueue_script('evaluation-form', get_template_directory_uri() . '/js/evaluation-form.js', [], '1.0', true);
    wp_localize_script('evaluation-form', 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
        'root' => esc_url_raw(rest_url())
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_evaluation_scripts');

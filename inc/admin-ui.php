<?php
// Admin UI
if (!defined('ABSPATH')) exit;

function enqueue_global_admin_scripts() {
    // ...
}
add_action('admin_enqueue_scripts', 'enqueue_global_admin_scripts');
add_action('admin_enqueue_scripts', function($hook_suffix) {
    global $post_type;
    // ...
});

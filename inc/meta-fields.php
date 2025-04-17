<?php
// Meta Fields
if (!defined('ABSPATH')) exit;

function prevent_slash_buildup() {
    // ...
}
add_action('init', 'prevent_slash_buildup');
require_once get_template_directory() . '/inc/meta-fields/content-display-meta.php';

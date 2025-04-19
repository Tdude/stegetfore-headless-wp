<?php
// Meta Fields
if (!defined('ABSPATH')) exit;

function prevent_slash_buildup()
{
    // Remove the existing filter that adds slashes to JSON in post meta
    remove_filter('update_post_metadata', 'wp_slash');
    // Add our own filter that properly handles JSON
    add_filter('update_post_metadata', function ($check, $object_id, $meta_key, $meta_value) {
        $json_meta_keys = [
            'module_buttons',
            'module_selling_points',
            'module_stats',
            'module_testimonials',
            'module_faq_items',
            'module_tabbed_content',
            'selling_points',
            'page_modules'
        ];
        if (in_array($meta_key, $json_meta_keys)) {
            // If it's an array or object, encode as JSON
            if (is_array($meta_value) || is_object($meta_value)) {
                return json_encode($meta_value, JSON_UNESCAPED_UNICODE);
            }
            // If it's a string, check if it's valid JSON
            if (is_string($meta_value)) {
                json_decode($meta_value);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Already JSON, do nothing
                    return $check;
                } else {
                    // Not JSON, just save as is
                    return $meta_value;
                }
            }
        }
        return $check;
    }, 10, 4);
}
add_action('init', 'prevent_slash_buildup');
require_once get_template_directory() . '/inc/meta-fields/content-display-meta.php';

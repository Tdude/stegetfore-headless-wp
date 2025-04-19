<?php
// Admin UI
if (!defined('ABSPATH')) exit;

function enqueue_global_admin_scripts() {
    wp_enqueue_script(
        'steget-global-admin',
        get_template_directory_uri() . '/inc/js/admin.js',
        ['jquery'],
        '1.0.1',
        true
    );
}
add_action('admin_enqueue_scripts', 'enqueue_global_admin_scripts');

add_action('admin_enqueue_scripts', function($hook_suffix) {
    global $post_type;
    if ($post_type === 'module') {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
});

// Debug logging for JSON parse errors in the admin (for footnotes field etc)
add_action('admin_footer', function() {
    if (is_admin()) {
        ?>
        <script>
        // Capture and log any JSON parsing errors
        const originalJSONParse = JSON.parse;
        JSON.parse = function(text) {
            try {
                return originalJSONParse(text);
            } catch (e) {
                console.error('JSON Parse Error for:', text.substring(0, 100));
                console.error(e);
                throw e;
            }
        };
        </script>
        <?php
    }
});

// Keep UTF-8 handling for better robustness with Swedish characters
add_filter('wp_json_encode_options', function($options) {
    return $options | JSON_UNESCAPED_UNICODE;
});

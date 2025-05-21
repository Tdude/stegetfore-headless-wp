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
    
    // Load admin CSS
    wp_enqueue_style(
        'steget-global-admin-css',
        get_template_directory_uri() . '/inc/css/admin.css',
        [],
        '1.0.1'
    );
    
    // Always load color picker and central admin JS for all post edit screens
    $screen = get_current_screen();
    if ($screen && in_array($screen->base, ['post', 'page'])) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script(
            'steget-admin-color-picker',
            get_template_directory_uri() . '/inc/js/admin-color-picker.js',
            ['jquery', 'wp-color-picker'],
            filemtime(get_template_directory() . '/inc/js/admin-color-picker.js'),
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'enqueue_global_admin_scripts');

add_action('admin_enqueue_scripts', function($hook) {
    // Only on post/page/module edit screens
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_script(
            'selling-points-admin',
            get_template_directory_uri() . '/inc/js/selling-points-admin.js',
            ['jquery'],
            filemtime(get_template_directory() . '/inc/js/selling-points-admin.js'),
            true
        );
        wp_localize_script('selling-points-admin', 'stegetSellingPointsAdmin', [
            'labels' => [
                'selling_point' => __('Selling Point', 'steget'),
                'title' => __('Title', 'steget'),
                'description' => __('Description', 'steget'),
                'icon' => __('Icon', 'steget'),
                'color' => __('Color', 'steget'),
                'remove' => __('Remove', 'steget'),
            ]
        ]);
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
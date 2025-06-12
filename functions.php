<?php
// DEBUG
add_action('save_post', function($post_id, $post, $update) {
    file_put_contents('/tmp/test.log', '[steget-admin] save_post fired for post_id ' . $post_id . ' post_type: ' . $post->post_type . ' update: ' . var_export($update, true) . PHP_EOL, FILE_APPEND);
}, 10, 3);
// DEBUG
add_action('save_post_module', function($post_id) {
    file_put_contents('/tmp/test.log', '[steget-admin] save_post_module fired for post_id ' . $post_id . PHP_EOL, FILE_APPEND);
    if (isset($_POST['module_template'])) {
        file_put_contents('/tmp/test.log', '[steget-admin] module_template in POST: ' . $_POST['module_template'] . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('/tmp/test.log', '[steget-admin] module_template NOT in POST' . PHP_EOL, FILE_APPEND);
    }
}, 10);


// Main functions.php - only bootstraps and includes
if (!defined('ABSPATH')) exit;

// Theme Setup
require_once get_template_directory() . '/inc/theme-setup.php';

// REST API / CORS
require_once get_template_directory() . '/inc/cors.php';

// Next.js Integration
require_once get_template_directory() . '/inc/nextjs-revalidation.php';

// Scripts & Styles
require_once get_template_directory() . '/inc/scripts.php';

// Image Handling
require_once get_template_directory() . '/inc/image-filters.php';

// Meta Fields
require_once get_template_directory() . '/inc/meta-fields.php';

// Admin UI
require_once get_template_directory() . '/inc/admin-ui.php';

// Contact Form 7 Integration - only load if WPCF7 is active
if (defined('WPCF7_VERSION')) {
    // Load the endpoints (which handles custom REST API endpoints)
    require_once get_template_directory() . '/inc/rest/wpcf7-endpoints.php';
    // Load the integration (which handles response modification, shortcodes and encoding)
    require_once get_template_directory() . '/inc/rest/wpcf7-integration.php';
}

// Load other theme components (post types, meta fields, REST endpoints, admin options, etc.)
$required_files = [
    '/inc/post-types/evaluation.php',
    '/inc/post-types/modules.php',
    '/inc/meta-fields/register-meta.php',
    '/inc/rest/endpoints.php',
    '/inc/admin/theme-options.php',
    '/inc/admin/meta-cleanup.php'
];

foreach ($required_files as $file) {
    $file_path = get_template_directory() . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("Could not find file: $file");
    }
}

// --- Robustly save module_template on every publish/update ---
add_action('save_post_module', function($post_id) {
    file_put_contents('/tmp/test.log', '[steget-admin] save_post_module fired for post_id ' . $post_id . PHP_EOL, FILE_APPEND);

    // If module_template is in POST, save it
    if (isset($_POST['module_template'])) {
        update_post_meta($post_id, 'module_template', $_POST['module_template']);
        file_put_contents('/tmp/test.log', '[steget-admin] module_template in POST: ' . $_POST['module_template'] . PHP_EOL, FILE_APPEND);
    } else {
        // Fallback: if not set, set a default if not already present
        $current = get_post_meta($post_id, 'module_template', true);
        if (!$current) {
            $default = 'sharing'; // or whatever your default should be
            update_post_meta($post_id, 'module_template', $default);
            file_put_contents('/tmp/test.log', '[steget-admin] module_template defaulted to: ' . $default . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents('/tmp/test.log', '[steget-admin] module_template already set: ' . $current . PHP_EOL, FILE_APPEND);
        }
    }
}, 10);

// --- AJAX handler to save module template selection from admin.js ---
add_action('wp_ajax_steget_save_module_template', function() {
    error_log('[steget-admin] steget_save_module_template called');
    if (!current_user_can('edit_posts')) {
        error_log('[steget-admin] Permission denied');
        wp_send_json_error('Permission denied');
    }
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $template = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : '';
    if (!$post_id || !$template) {
        error_log('[steget-admin] Missing post_id or template');
        wp_send_json_error('Missing post_id or template');
    }
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-post_' . $post_id)) {
        error_log('[steget-admin] Invalid nonce for post_id ' . $post_id);
        wp_send_json_error('Invalid nonce');
    }
    $result = update_post_meta($post_id, 'module_template', $template);
    error_log('[steget-admin] update_post_meta result: ' . var_export($result, true) . ' for post_id ' . $post_id . ' template ' . $template);
    wp_send_json_success('Template saved');
});

// --- Add custom content to the WordPress admin footer ---
function my_custom_admin_footer_content() {
    echo '<p style=\"text-align: center;\">&copy; ' . date('Y') . ' Tryggve är en BOLD STATEMENT produktion. Kodad av <a href="https://github.com/Tdude">Tibor Berki</a>.</p>';
}
add_action( 'admin_footer_text', 'my_custom_admin_footer_content' );

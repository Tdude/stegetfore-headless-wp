<?php
/**
 * functions.php
 */

if (!defined('ABSPATH')) exit;

// Theme Setup
function headless_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('menus');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'steget'),
        'footer' => __('Footer Menu', 'steget')
    ]);
}
add_action('after_setup_theme', 'headless_theme_setup');

// CORS header for REST API
function add_cors_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
}
add_action('send_headers', 'add_cors_headers');

// Load theme components
$required_files = [
    '/inc/post-types/portfolio.php',
    '/inc/post-types/evaluation.php',
    '/inc/meta-fields/register-meta.php',
    '/inc/rest/endpoints.php',
    '/inc/admin/theme-options.php'
];

foreach ($required_files as $file) {
    $file_path = get_template_directory() . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

add_filter('show_admin_bar', '__return_true');


// Caching
// This could live in its own plugin
function trigger_nextjs_revalidation($post_id) {
    // Skip if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Get the post type
    $post_type = get_post_type($post_id);

    // Get the post slug
    $slug = get_post_field('post_name', $post_id);

    // Determine the path to revalidate
    $path = '/';  // Always revalidate home page
    if ($post_type === 'post') {
        $path = "/posts/$slug";
    } elseif ($post_type === 'page') {
        $path = "/$slug";
    }

    // Your Next.js app URL and secret token
    $nextjs_url = get_option('nextjs_url', 'https://stegetfore.nu');
    $secret_token = get_option('nextjs_token', '');

    // Send revalidation request
    wp_remote_post($nextjs_url . '/api/revalidate', array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode(array(
            'path' => $path,
            'token' => $secret_token
        ))
    ));
}

// Trigger on post save/update
add_action('save_post', 'trigger_nextjs_revalidation');

// Trigger on menu update
add_action('wp_update_nav_menu', function($menu_id) {
    trigger_nextjs_revalidation(null);  // Revalidate home page on menu changes
});

// Add settings page for Next.js URL and token
function nextjs_settings_init() {
    register_setting('general', 'nextjs_url');
    register_setting('general', 'nextjs_token');

    add_settings_section(
        'nextjs_settings_section',
        'Next.js Settings',
        null,
        'general'
    );

    add_settings_field(
        'nextjs_url',
        'Next.js App URL',
        function() {
            $value = get_option('nextjs_url');
            echo "<input type='text' name='nextjs_url' value='$value' class='regular-text'>";
        },
        'general',
        'nextjs_settings_section'
    );

    add_settings_field(
        'nextjs_token',
        'Revalidation Token',
        function() {
            $value = get_option('nextjs_token');
            echo "<input type='text' name='nextjs_token' value='$value' class='regular-text'>";
        },
        'general',
        'nextjs_settings_section'
    );
}
add_action('admin_init', 'nextjs_settings_init');

// If we need to remove styles from plugins
function headless_theme_dequeue_plugin_styles() {
    wp_dequeue_style('plugin-style-handle');
}
add_action('wp_enqueue_scripts', 'headless_theme_dequeue_plugin_styles', 20);

// For inc/post-types/evaluation.php
function enqueue_evaluation_scripts() {
    wp_enqueue_script('evaluation-form', get_template_directory_uri() . '/js/evaluation-form.js', [], '1.0', true);
    wp_localize_script('evaluation-form', 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
        'root' => esc_url_raw(rest_url())
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_evaluation_scripts');
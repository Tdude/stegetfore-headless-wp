<?php
/**
 * functions.php
 */

if (!defined('ABSPATH'))
    exit;

/**
 * DIAGNOSTIC - CAREFUL: Temporarily test email on EVERY load
 * */
/*
function test_wp_mail() {
    $to = 'your-email@example.com'; // Your email address
    $subject = 'Test Email from WordPress';
    $message = 'This is a test email from your WordPress site';
    $headers = 'From: WordPress <wordpress@yoursite.com>' . "\r\n";

    $result = wp_mail($to, $subject, $message, $headers);
    error_log('Mail test result: ' . ($result ? 'Success' : 'Failed'));
}
add_action('init', 'test_wp_mail');
*/

// DIAGNOSTIC: See all endpoints TEST!
/*
function list_all_registered_routes() {
    $routes = rest_get_server()->get_routes();
    error_log('=== REGISTERED REST ROUTES ===');
    foreach ($routes as $route => $route_details) {
        error_log($route);
    }
    error_log('=== END ROUTES ===');
}
add_action('rest_api_init', 'list_all_registered_routes', 999);
*/

// Theme Setup
function headless_theme_setup()
{
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
// Update this function in your functions.php
function add_cors_headers()
{
    // Add Access-Control-Allow-Origin header
    $http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';

    // You may want to limit this to specific origins in production
    // For development, allow localhost
    if (
        $http_origin == "http://localhost:3000" ||
        $http_origin == "https://localhost:3000" ||
        strpos($http_origin, 'stegetfore.nu') !== false
    ) {
        header("Access-Control-Allow-Origin: $http_origin");
    } else {
        header("Access-Control-Allow-Origin: *");
    }

    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Max-Age: 86400"); // Cache preflight for 24 hours
        exit(0);
    }

    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header("Access-Control-Allow-Credentials: true");
}

// Make sure this function is called for REST requests
add_action('rest_api_init', function () {
    // Remove the previous send_headers action if it exists
    remove_action('send_headers', 'add_cors_headers');

    // Call our CORS function before processing REST requests
    add_cors_headers();
}, 15);

// Keep the original hook for non-REST requests
add_action('send_headers', 'add_cors_headers');

// Load theme components
$required_files = [
    //'/inc/post-types/portfolio.php',
    '/inc/post-types/evaluation.php',
    '/inc/post-types/modules.php',
    '/inc/meta-fields/register-meta.php',
    '/inc/rest/endpoints.php',
    '/inc/rest/wpcf7-endpoints.php',
    '/inc/rest/module-endpoints.php',

    // Feature files
    //'/inc/features/hero.php',
    //'/inc/features/hero-api.php',
    //'/inc/features/selling-points.php',
    //'/inc/features/selling-points-api.php',
    //'/inc/features/cta.php',
    //'/inc/features/cta-api.php',
    '/inc/features/posts-api.php',
    '/inc/admin/theme-options.php',
    '/inc/admin/meta-cleanup.php'
];

/**
 * Load n log included files
 */

foreach ($required_files as $file) {
    $file_path = get_template_directory() . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        // error_log("Loaded file: $file");
    } else {
        error_log("Could not find file: $file");
    }
}



// Caching. This could live in its own plugin
function trigger_nextjs_revalidation($post_id)
{
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
add_action('wp_update_nav_menu', function ($menu_id) {
    trigger_nextjs_revalidation(null);  // Revalidate home page on menu changes
});

// Add settings page for Next.js URL and token
function nextjs_settings_init()
{
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
        function () {
            $value = get_option('nextjs_url');
            echo "<input type='text' name='nextjs_url' value='$value' class='regular-text'>";
        },
        'general',
        'nextjs_settings_section'
    );

    add_settings_field(
        'nextjs_token',
        'Revalidation Token',
        function () {
            $value = get_option('nextjs_token');
            echo "<input type='text' name='nextjs_token' value='$value' class='regular-text'>";
        },
        'general',
        'nextjs_settings_section'
    );
}
add_action('admin_init', 'nextjs_settings_init');

// If we need to remove styles from plugins
function headless_theme_dequeue_plugin_styles()
{
    wp_dequeue_style('plugin-style-handle');
}
add_action('wp_enqueue_scripts', 'headless_theme_dequeue_plugin_styles', 20);

// For inc/post-types/evaluation.php
function enqueue_evaluation_scripts()
{
    wp_enqueue_script('evaluation-form', get_template_directory_uri() . '/js/evaluation-form.js', [], '1.0', true);
    wp_localize_script('evaluation-form', 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
        'root' => esc_url_raw(rest_url())
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_evaluation_scripts');


/**
 * Included Contact Form 7 custom endpoints to functions.php
 */
if (defined('WPCF7_VERSION')) {
    require_once get_template_directory() . '/inc/rest/wpcf7-endpoints.php';
}
/*
add_action('init', function() {
    error_log('WPCF7_VERSION defined: ' . (defined('WPCF7_VERSION') ? 'YES - ' . WPCF7_VERSION : 'NO'));
});
*/

/**
 * This is the simplified approach for CF7 integration
 */
add_filter('rest_prepare_page', function ($response, $post, $request) {
    $data = $response->get_data();

    // Process only if we have content
    if (isset($data['content']['rendered'])) {
        // Check for CF7 shortcode
        if (has_shortcode($post->post_content, 'contact-form-7') || strpos($data['content']['rendered'], 'class="wpcf7') !== false) {
            // Extract form ID
            $form_id = null;

            // Try to get from data attribute or shortcode
            if (preg_match('/data-wpcf7-id="(\d+)"/', $data['content']['rendered'], $matches)) {
                $form_id = $matches[1];
            } elseif (preg_match('/\[contact-form-7\s+id="(\d+)"/', $post->post_content, $matches)) {
                $form_id = $matches[1];
            }

            // If form ID found, add it to the response
            if ($form_id) {
                // Add it as a direct property in the API response
                $data['cf7_form_id'] = $form_id;

                // Remove the CF7 HTML from the content
                $data['content']['rendered'] = preg_replace('/<div[^>]*class="[^"]*wpcf7[^"]*"[^>]*>[\s\S]*?<\/div>(?:\s*<div[^>]*class="[^"]*wpcf7-response-output[^"]*"[^>]*>[\s\S]*?<\/div>)?/s', '', $data['content']['rendered']);

                // Also remove any shortcodes
                $data['content']['rendered'] = str_replace('[contact-form-7 id="' . $form_id . '"', '', $data['content']['rendered']);
            }

            $response->set_data($data);
        }
    }

    return $response;
}, 10, 3);

// Register form ID in REST API for easier access
add_action('rest_api_init', function () {
    register_rest_field('page', 'cf7_form_id', array(
        'get_callback' => function ($post) {
            $content = get_post_field('post_content', $post['id']);

            // Check for CF7 shortcode
            if (has_shortcode($content, 'contact-form-7')) {
                $pattern = '/\[contact-form-7\s+id="(\d+)"/';
                if (preg_match($pattern, $content, $matches)) {
                    return $matches[1];
                }
            }

            return null;
        },
        'schema' => array(
            'type' => 'string',
            'description' => 'Contact Form 7 ID if present on the page',
        ),
    ));
});

// Add support for our custom headless CF7 shortcode
add_shortcode('headless-cf7', function ($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);

    if (empty($atts['id'])) {
        return '';
    }

    // Just return a marker div that React can use to inject the form
    return sprintf(
        '<div id="headless-cf7-%s" class="headless-cf7-placeholder" data-form-id="%s"></div>',
        esc_attr($atts['id']),
        esc_attr($atts['id'])
    );
});

// Ensure proper encoding for API responses
add_action('init', function () {
    // Set internal encoding to UTF-8
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
    }
});

// Ensure UTF-8 headers for REST API
add_action('rest_api_init', function () {
    // Add UTF-8 header to all REST responses
    add_filter('rest_pre_serve_request', function ($served, $result) {
        header('Content-Type: application/json; charset=utf-8');
        return $served;
    }, 10, 2);
});


/**
 * Prevent future slash buildup in meta fields
 */
function prevent_slash_buildup()
{
    // Remove the existing filter that adds slashes to JSON in post meta
    remove_filter('update_post_metadata', 'wp_slash');

    // Add our own filter that properly handles JSON
    add_filter('update_post_metadata', function ($check, $object_id, $meta_key, $meta_value) {
        // Only apply special handling to specific meta keys that store JSON
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

        // For these JSON fields, use our special handling
        if (
            in_array($meta_key, $json_meta_keys) && is_string($meta_value) &&
            (strpos($meta_value, '[') === 0 || strpos($meta_value, '{') === 0)
        ) {
            // It's already JSON, so don't add more slashes
            return $check;
        }

        // For all other fields, let WordPress handle it normally
        return $check;
    }, 10, 4);
}
add_action('init', 'prevent_slash_buildup');
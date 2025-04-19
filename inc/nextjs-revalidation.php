<?php
// Next.js Integration (revalidation, settings)
if (!defined('ABSPATH')) exit;

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
add_action('save_post', 'trigger_nextjs_revalidation');
add_action('wp_update_nav_menu', function ($menu_id) {
    trigger_nextjs_revalidation(null);  // Revalidate home page on menu changes
});

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

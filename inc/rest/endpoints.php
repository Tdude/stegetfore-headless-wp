<?php
/*
 * inc/rest/endpoints.php
 *
 * */
function register_custom_endpoints() {
    register_rest_route('stegetfore-headless-wp/v1', '/settings', [
        'methods' => 'GET',
        'callback' => 'get_theme_settings',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('stegetfore-headless-wp/v1', '/menu/(?P<location>[a-zA-Z0-9_-]+)', [
        'methods' => 'GET',
        'callback' => 'get_menu_by_location',
        'permission_callback' => '__return_true'
    ]);
}
add_action('rest_api_init', 'register_custom_endpoints');

function get_theme_settings() {
    return [
        'site_title' => get_bloginfo('name'),
        'site_description' => get_bloginfo('description'),
        'logo' => get_custom_logo(),
        'menu_locations' => get_nav_menu_locations()
    ];
}

function get_menu_by_location($request) {
    $location = $request['location'];
    $locations = get_nav_menu_locations();

    if (!isset($locations[$location])) {
        return new WP_Error('no_menu', 'No menu in this location');
    }

    $menu = wp_get_nav_menu_object($locations[$location]);
    $menu_items = wp_get_nav_menu_items($menu->term_id);

    return rest_ensure_response($menu_items);
}



add_action('rest_api_init', function() {
    // Test endpoint
    register_rest_route('stegetfore-headless-wp/v1', '/test', [
        'methods' => 'GET',
        'callback' => function() {
            return [
                'status' => 'success',
                'message' => 'API is working',
                'timestamp' => current_time('mysql')
            ];
        },
        'permission_callback' => '__return_true'
    ]);

    // Site info endpoint
    register_rest_route('stegetfore-headless-wp/v1', '/site-info', [
        'methods' => 'GET',
        'callback' => function() {
            return [
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => get_bloginfo('url'),
                'admin_email' => get_bloginfo('admin_email'),
                'language' => get_bloginfo('language')
            ];
        },
        'permission_callback' => '__return_true'
    ]);

    // Extended posts endpoint
    register_rest_route('stegetfore-headless-wp/v1', '/posts-extended', [
        'methods' => 'GET',
        'callback' => function($request) {
            $posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => 10,
                'post_status' => 'publish'
            ]);

            return array_map(function($post) {
                $featured_image = get_the_post_thumbnail_url($post->ID, 'full');

                return [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'content' => $post->post_content,
                    'excerpt' => $post->post_excerpt,
                    'slug' => $post->post_name,
                    'featured_image' => $featured_image,
                    'date' => $post->post_date,
                    'modified' => $post->post_modified
                ];
            }, $posts);
        },
        'permission_callback' => '__return_true'
    ]);
});

// Add featured image to REST API
add_action('rest_api_init', function() {
    register_rest_field('post', 'featured_image_url', [
        'get_callback' => function($post) {
            if (has_post_thumbnail($post['id'])) {
                $img = wp_get_attachment_image_src(
                    get_post_thumbnail_id($post['id']),
                    'full'
                );
                return $img[0];
            }
            return null;
        }
    ]);
});
<?php
/**
 * inc/rest/endpoints.php
 */

add_action('rest_api_init', function() {
    // Test endpoint
    register_rest_route('steget/v1', '/test', [
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
    register_rest_route('steget/v1', '/site-info', [
        'methods' => 'GET',
        'callback' => function() {
            return [
                'name' => get_option('blogname'),
                'description' => get_bloginfo('description'),
                'url' => get_bloginfo('url'),
                'admin_email' => get_bloginfo('admin_email'),
                'language' => get_bloginfo('language')
            ];
        },
        'permission_callback' => '__return_true'
    ]);

    // Extended posts endpoint
    register_rest_route('steget/v1', '/posts-extended', [
        'methods' => 'GET',
        'callback' => function($request) {
            $posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => 12,
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

    // Menu endpoint
    register_rest_route('steget/v1', '/menu/(?P<location>[a-zA-Z0-9_-]+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            $location = $request['location'];
            $locations = get_nav_menu_locations();

            if (!isset($locations[$location])) {
                return new WP_Error(
                    'no_menu',
                    'Kunde inte hitta en meny på platsen: ' . $location,
                    ['status' => 404]
                );
            }

            $menu_id = $locations[$location];
            $menu_items = wp_get_nav_menu_items($menu_id);

            if (!$menu_items) {
                return [];
            }

            return array_map(function($item) {
                $url = parse_url($item->url, PHP_URL_PATH);
                $slug = trim($url ?? '', '/');

                return [
                    'ID' => $item->ID,
                    'title' => $item->title,
                    'url' => $item->url,
                    'slug' => $slug ?: '/',
                    'target' => $item->target,
                    'order' => $item->menu_order,
                ];
            }, $menu_items);
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



// Startpage endpoints for fewer requests
function register_homepage_data_endpoint() {
    register_rest_route('startpage/v1', '/homepage-data', [
        'methods' => 'GET',
        'callback' => 'get_homepage_data',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_homepage_data_endpoint');

function get_homepage_data() {
    $homepage_id = get_option('page_on_front');

    // Get all homepage meta in one go
    $hero_data = [
        'title' => get_post_meta($homepage_id, 'hero_title', true),
        'intro' => get_post_meta($homepage_id, 'hero_intro', true),
        'image' => get_post_meta($homepage_id, 'hero_image_id', true) ?
                  wp_get_attachment_image_src(get_post_meta($homepage_id, 'hero_image_id', true), 'full') : null,
        'buttons' => json_decode(get_post_meta($homepage_id, 'hero_cta_buttons', true), true) ?: [],
    ];

    // Featured posts
    $featured_posts = get_posts([
        'post_type' => 'post',
        'meta_key' => 'is_featured',
        'meta_value' => '1',
        'posts_per_page' => 6,
    ]);

    $posts_data = [];
    foreach ($featured_posts as $post) {
        $posts_data[] = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'excerpt' => get_the_excerpt($post),
            'link' => get_permalink($post),
            'image' => get_the_post_thumbnail_url($post, 'medium'),
            'date' => get_the_date('c', $post),
        ];
    }

    // Similarly get other section data

    return [
        'hero' => $hero_data,
        'featured_posts' => $posts_data,
        // Other sections
    ];
}
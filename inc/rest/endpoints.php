<?php
/**
 * inc/rest/endpoints.php
 * NEEDS CLEANING UP!!!
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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


    // Menu endpoint with hierarchical structure
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

            // First, let's prepare all items with their basic info
            $all_items = [];
            foreach ($menu_items as $item) {
                $url = parse_url($item->url, PHP_URL_PATH);
                $slug = trim($url ?? '', '/');

                $all_items[$item->ID] = [
                    'ID' => $item->ID,
                    'title' => $item->title,
                    'url' => $item->url,
                    'slug' => $slug ?: '/',
                    'target' => $item->target,
                    'order' => $item->menu_order,
                    'parent' => $item->menu_item_parent,
                    'children' => []
                ];
            }

            // Now build the tree
            $menu_tree = [];
            foreach ($all_items as $id => $item) {
                // If it's a top-level item
                if (empty($item['parent']) || $item['parent'] == 0) {
                    $menu_tree[] = &$all_items[$id];
                } else {
                    // It's a child item
                    if (isset($all_items[$item['parent']])) {
                        $all_items[$item['parent']]['children'][] = &$all_items[$id];
                    }
                }
            }

            // Clean up - remove parent property since it's no longer needed
            $clean_items = function(&$items) use (&$clean_items) {
                foreach ($items as &$item) {
                    unset($item['parent']);
                    if (!empty($item['children'])) {
                        $clean_items($item['children']);
                    }
                }
            };

            $clean_items($menu_tree);

            return $menu_tree;
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
    register_rest_route('startpage/v2', '/homepage-data', [
        'methods' => 'GET',
        'callback' => 'get_homepage_data',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_homepage_data_endpoint');



function get_homepage_data() {
    // Show deprecation notice in the REST API response
    $deprecated_notice = 'This endpoint is deprecated. Please use /startpage/v2/homepage-data instead.';

    $homepage_id = get_option('page_on_front');

    // Include feature API files
    require_once get_template_directory() . '/inc/features/hero-api.php';
    require_once get_template_directory() . '/inc/features/posts-api.php';
    require_once get_template_directory() . '/inc/features/testimonials-api.php';
    require_once get_template_directory() . '/inc/features/cta-api.php';

    // Use the new modular functions but maintain the original format
    return [
        'hero' => get_hero_data($homepage_id),
        'featured_posts' => get_featured_posts_data(),
        'featured_posts_title' => 'Nytt från bloggen',
        'cta' => get_cta_data($homepage_id),
        'testimonials' => get_testimonials_data(),
        'testimonials_title' => 'Vad våra klienter säger',
        '_deprecated' => $deprecated_notice
    ];
}

function register_homepage_data_v2_endpoint() {
    register_rest_route('startpage/v2', '/homepage-data', [
        'methods' => 'GET',
        'callback' => 'get_homepage_data_v2',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_homepage_data_v2_endpoint');

function get_homepage_data_v2() {
    $homepage_id = get_option('page_on_front');

    // Include all feature API files if not already included
    require_once get_template_directory() . '/inc/features/hero-api.php';
    require_once get_template_directory() . '/inc/features/posts-api.php';
    require_once get_template_directory() . '/inc/features/testimonials-api.php';
    require_once get_template_directory() . '/inc/features/cta-api.php';

    return [
        'hero' => get_hero_data($homepage_id),
        'featured_posts' => get_featured_posts_data(),
        'featured_posts_title' => 'Nytt från bloggen',
        'cta' => get_cta_data($homepage_id),
        'testimonials' => get_testimonials_data(),
        'testimonials_title' => 'Vad våra klienter säger'
    ];
}
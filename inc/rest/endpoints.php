<?php
/**
 * inc/rest/endpoints.php
 * NEEDS CLEANING UP FROM OLD "SECTIONS"
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('rest_api_init', function () {
    // Test endpoint
    register_rest_route('steget/v1', '/test', [
        'methods' => 'GET',
        'callback' => function () {
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
        'callback' => function () {
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
        'callback' => function ($request) {
            $posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => 12,
                'post_status' => 'publish'
            ]);

            return array_map(function ($post) {
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
        'callback' => function ($request) {
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
            $clean_items = function (&$items) use (&$clean_items) {
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

    // Public endpoint for evaluation questions
    register_rest_route('public/v1', '/evaluation/questions', [
        'methods' => 'GET',
        'callback' => function() {
            // Get the latest assessment that has question data
            $assessments = get_posts(array(
                'post_type'      => 'ham_assessment',
                'posts_per_page' => 1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => 'ham_assessment_data',
                        'compare' => 'EXISTS',
                    ),
                ),
            ));

            if (empty($assessments)) {
                return new WP_Error('no_data', 'No assessment data found', ['status' => 404]);
            }

            $assessment_data = get_post_meta($assessments[0]->ID, 'ham_assessment_data', true);

            if (empty($assessment_data)) {
                return new WP_Error('no_data', 'No assessment data found', ['status' => 404]);
            }

            return $assessment_data;
        },
        'permission_callback' => '__return_true'
    ]);

    // Assessment data endpoint
    register_rest_route('ham/v1', '/assessment/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            $post_id = $request['id'];
            
            // Try both meta keys
            $assessment_data = get_post_meta($post_id, 'ham_assessment_data', true);
            if (empty($assessment_data)) {
                $assessment_data = get_post_meta($post_id, '_ham_assessment_data', true);
            }

            // Debug info
            $debug = [
                'post_exists' => get_post($post_id) !== null,
                'meta_keys' => array_filter(get_post_custom_keys($post_id) ?: []), // Filter out null/false values
                'raw_meta' => array_filter(get_post_meta($post_id)), // Filter out empty values
                'post_type' => get_post_type($post_id)
            ];
            
            if (empty($assessment_data)) {
                return new WP_Error('no_data', 'No assessment data found', [
                    'status' => 404,
                    'debug' => $debug
                ]);
            }

            return [
                'id' => $post_id,
                'assessment_data' => $assessment_data,
                'debug' => $debug
            ];
        },
        'permission_callback' => '__return_true'
    ]);

    // Debug endpoint for page modules
    register_rest_route('steget/v1', '/debug/page-modules/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            $page_id = $request['id'];
            $page_modules = get_post_meta($page_id, 'page_modules', true);
            return [
                'raw_meta' => $page_modules,
                'decoded' => json_decode($page_modules, true),
                'page_id' => $page_id
            ];
        },
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
});

// Add featured image to REST API
add_action('rest_api_init', function () {
    register_rest_field('post', 'featured_image_url', [
        'get_callback' => function ($post) {
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

// General UTF-8 encoding filter for REST API responses
function steget_ensure_utf8_encoding_rest($response, $handler, $request)
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    return $response;
}
add_filter('rest_pre_echo_response', 'steget_ensure_utf8_encoding_rest', 10, 3);
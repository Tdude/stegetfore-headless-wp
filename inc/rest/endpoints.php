<?php
/**
 * inc/rest/endpoints.php
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
    register_rest_route('startpage/v1', '/homepage-data', [
        'methods' => 'GET',
        'callback' => 'get_homepage_data',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_homepage_data_endpoint');



function get_homepage_data() {
    $homepage_id = get_option('page_on_front');

    // Get featured image from the homepage
    $featured_image_url = get_the_post_thumbnail_url($homepage_id, 'full');

    // Get hero data
    $hero_data = [
        'title' => get_post_meta($homepage_id, 'hero_title', true) ?: get_the_title($homepage_id),
        'intro' => get_post_meta($homepage_id, 'hero_intro', true) ?: get_the_excerpt($homepage_id),
        'image' => get_post_meta($homepage_id, 'hero_image_id', true) ?
                wp_get_attachment_image_src(get_post_meta($homepage_id, 'hero_image_id', true), 'full')[0] :
                $featured_image_url, // Use featured image as fallback
        'buttons' => json_decode(get_post_meta($homepage_id, 'hero_cta_buttons', true), true) ?: [
            [
                'text' => 'Upptäck mer',
                'url' => '/om-oss',
                'style' => 'primary'
            ]
        ],
    ];

    // Featured posts - using meta field
    $featured_posts = get_posts([
        'post_type' => 'post',
        'posts_per_page' => 6,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $posts_data = [];
    foreach ($featured_posts as $post) {
        $posts_data[] = [
            'id' => $post->ID,
            'title' => [
                'rendered' => $post->post_title
            ],
            'excerpt' => [
                'rendered' => get_the_excerpt($post)
            ],
            'content' => [
                'rendered' => $post->post_content
            ],
            'slug' => $post->post_name,
            'featured_image_url' => get_the_post_thumbnail_url($post, 'full'),
            'categories' => wp_get_post_categories($post->ID),
            'date' => get_the_date('c', $post),
        ];
    }

    // Get testimonials
    $testimonials_query = get_posts([
        'post_type' => 'testimonial',
        'posts_per_page' => 6,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $testimonials_data = [];
    foreach ($testimonials_query as $testimonial) {
        $testimonials_data[] = [
            'id' => $testimonial->ID,
            'content' => $testimonial->post_content,
            'author_name' => get_post_meta($testimonial->ID, 'author_name', true) ?: $testimonial->post_title,
            'author_position' => get_post_meta($testimonial->ID, 'author_position', true) ?: '',
            'author_image' => get_the_post_thumbnail_url($testimonial->ID, 'thumbnail')
        ];
    }

    // Get the new features data
    // Check if these functions exist before calling them
    $selling_points_data = function_exists('steget_get_selling_points_data') ? steget_get_selling_points_data() : [
        'title' => 'Varför välja oss',
        'points' => []
    ];

    $stats_data = function_exists('steget_get_stats_data') ? steget_get_stats_data() : [
        'title' => 'Vårt arbete i siffror',
        'subtitle' => 'Bakom varje siffra finns ett barn.',
        'background_color' => 'bg-muted/30',
        'stats' => []
    ];

    $gallery_data = function_exists('steget_get_gallery_data') ? steget_get_gallery_data() : [
        'title' => 'Vårt Galleri',
        'items' => []
    ];

    // Get categories for the posts
    $categories = get_categories(['hide_empty' => false]);
    $categories_data = [];
    foreach ($categories as $category) {
        $categories_data[$category->term_id] = [
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug
        ];
    }

    // Return all data
    return [
        'hero' => $hero_data,
        'featured_posts' => $posts_data,
        'featured_posts_title' => 'Nytt från bloggen',
        'categories' => $categories_data,
        'cta' => [
            'title' => get_post_meta($homepage_id, 'cta_title', true) ?: '',
            'description' => get_post_meta($homepage_id, 'cta_description', true) ?: '',
            'button_text' => get_post_meta($homepage_id, 'cta_button_text', true) ?: '',
            'button_url' => get_post_meta($homepage_id, 'cta_button_url', true) ?: '',
            'background_color' => get_post_meta($homepage_id, 'cta_background_color', true) ?: 'bg-primary',
        ],
        'testimonials' => $testimonials_data,
        'testimonials_title' => 'Vad våra klienter säger',

        // New sections data
        'selling_points' => $selling_points_data['points'],
        'selling_points_title' => $selling_points_data['title'],

        'stats' => $stats_data['stats'],
        'stats_title' => $stats_data['title'],
        'stats_subtitle' => $stats_data['subtitle'],
        'stats_background_color' => $stats_data['background_color'],

        'gallery' => $gallery_data['items'],
        'gallery_title' => $gallery_data['title']
    ];
}
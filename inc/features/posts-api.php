<?php
/**
 * inc/features/posts-api.php
*/
function get_featured_posts_data() {
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
            'title' => $post->post_title,
            'excerpt' => get_the_excerpt($post),
            'link' => get_permalink($post),
            'image' => get_the_post_thumbnail_url($post, 'medium'),
            'date' => get_the_date('c', $post),
        ];
    }

    return $posts_data;
}

// In posts-api.php
function register_featured_posts_endpoint() {
    register_rest_route('steget/v1', '/featured-posts', [
        'methods' => 'GET',
        'callback' => function() {
            return get_featured_posts_data();
        },
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_featured_posts_endpoint');
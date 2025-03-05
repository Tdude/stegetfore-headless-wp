<?php
/**
 * inc/features/testimonials-api.php
*/

function get_testimonials_data() {
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

    return $testimonials_data;
}

function register_testimonials_endpoint() {
    register_rest_route('steget/v1', '/testimonials', [
        'methods' => 'GET',
        'callback' => function($request) {
            return get_testimonials_data();
        },
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_testimonials_endpoint');
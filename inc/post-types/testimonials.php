<?php
/**
 * inc/post-types/testimonials.php
 */
function register_testimonial_post_type() {
    register_post_type('testimonial', [
        'labels' => [
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-format-quote',
    ]);

    // Register meta fields for testimonials
    register_post_meta('testimonial', 'author_name', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ]);

    register_post_meta('testimonial', 'author_position', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ]);
}
add_action('init', 'register_testimonial_post_type');
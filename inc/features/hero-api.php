<?php
/**
 * inc/features/hero-api.php
*/

function get_hero_data($homepage_id) {
    // Get featured image from the homepage
    $featured_image_url = get_the_post_thumbnail_url($homepage_id, 'full');

    // Get hero data
    return [
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
}
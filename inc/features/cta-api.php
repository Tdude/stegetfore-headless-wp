<?php
/**
 * features/cta-api.php
*/
function get_cta_data($homepage_id) {
    return [
        'title' => get_post_meta($homepage_id, 'cta_title', true) ?: '',
        'description' => get_post_meta($homepage_id, 'cta_description', true) ?: '',
        'button_text' => get_post_meta($homepage_id, 'cta_button_text', true) ?: '',
        'button_url' => get_post_meta($homepage_id, 'cta_button_url', true) ?: '',
        'background_color' => get_post_meta($homepage_id, 'cta_background_color', true) ?: 'bg-primary',
    ];
}

function register_cta_endpoint() {
    register_rest_route('steget/v1', '/cta/(?P<page_id>\d+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            return get_cta_data($request['page_id']);
        },
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_cta_endpoint');
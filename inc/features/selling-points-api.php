<?php
/**
 * inc/features/selling-points-api.php
*/

function get_selling_points_data($page_id) {
    $selling_points_title = get_post_meta($page_id, 'selling_points_title', true);
    $selling_points_json = get_post_meta($page_id, 'selling_points', true);
    $selling_points = json_decode($selling_points_json, true) ?: [];

    return [
        'title' => $selling_points_title ?: 'Our Key Benefits',
        'points' => $selling_points,
    ];
}

function register_selling_points_endpoint() {
    register_rest_route('steget/v1', '/selling-points/(?P<page_id>\d+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            return get_selling_points_data($request['page_id']);
        },
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_selling_points_endpoint');
<?php
/*
 * inc/rest/endpoints.php
 *
 * */
function register_custom_endpoints() {
    register_rest_route('headless-theme/v1', '/settings', [
        'methods' => 'GET',
        'callback' => 'get_theme_settings',
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('headless-theme/v1', '/menu/(?P<location>[a-zA-Z0-9_-]+)', [
        'methods' => 'GET',
        'callback' => 'get_menu_by_location',
        'permission_callback' => '__return_true'
    ]);
}
add_action('rest_api_init', 'register_custom_endpoints');

function get_theme_settings() {
    return [
        'site_title' => get_bloginfo('name'),
        'site_description' => get_bloginfo('description'),
        'logo' => get_custom_logo(),
        'menu_locations' => get_nav_menu_locations()
    ];
}

function get_menu_by_location($request) {
    $location = $request['location'];
    $locations = get_nav_menu_locations();

    if (!isset($locations[$location])) {
        return new WP_Error('no_menu', 'No menu in this location');
    }

    $menu = wp_get_nav_menu_object($locations[$location]);
    $menu_items = wp_get_nav_menu_items($menu->term_id);

    return rest_ensure_response($menu_items);
}

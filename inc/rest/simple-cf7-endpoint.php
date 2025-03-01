<?php
/**
 * Simple Contact Form 7 API endpoint
 */

// Register the endpoint
function register_simple_cf7_endpoint() {
    register_rest_route('steget/v1', '/cf7/(?P<id>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_simple_cf7_data',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_simple_cf7_endpoint');

// Get basic form data
function get_simple_cf7_data($request) {
    $form_id = $request['id'];

    // Check if the form exists
    $form = wpcf7_contact_form($form_id);
    if (!$form) {
        return new WP_Error(
            'form_not_found',
            'The requested form was not found',
            array('status' => 404)
        );
    }

    // Get just the basic form info
    $data = array(
        'id' => $form_id,
        'title' => $form->title(),
        'has_form' => true
    );

    return rest_ensure_response($data);
}
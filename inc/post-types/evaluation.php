<?php
/**
 * inc/post-types/evaluation.php
 */
// Register custom post type for student evaluation
function register_evaluation_post_type() {
    register_post_type('student_evaluation', [
        'public' => false,
        'show_ui' => true,
        'labels' => ['name' => 'Student Evaluations'],
        'supports' => ['title', 'custom-fields']
    ]);
}
add_action('init', 'register_evaluation_post_type');

// Register REST API endpoints
function register_evaluation_endpoints() {
    register_rest_route('evaluation/v1', '/save', [
        'methods' => 'POST',
        'callback' => 'save_evaluation',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_rest_route('evaluation/v1', '/get/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'get_evaluation',
        'permission_callback' => function() {
            return current_user_can('read');
        }
    ]);
}
add_action('rest_api_init', 'register_evaluation_endpoints');

function save_evaluation($request) {
    $params = $request->get_params();

    $post_data = [
        'post_type' => 'student_evaluation',
        'post_title' => $params['student_name'] ?? 'Unnamed Evaluation',
        'post_status' => 'publish'
    ];

    // Create or update post
    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        return new WP_Error('save_failed', 'Failed to save evaluation', ['status' => 500]);
    }

    // Save form data as meta
    update_post_meta($post_id, 'evaluation_data', $params['formData']);
    update_post_meta($post_id, 'last_updated', current_time('mysql'));

    return [
        'success' => true,
        'id' => $post_id,
        'message' => 'Evaluation saved successfully'
    ];
}

function get_evaluation($request) {
    $post_id = $request['id'];
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'student_evaluation') {
        return new WP_Error('not_found', 'Evaluation not found', ['status' => 404]);
    }

    return [
        'id' => $post_id,
        'formData' => get_post_meta($post_id, 'evaluation_data', true),
        'last_updated' => get_post_meta($post_id, 'last_updated', true)
    ];
}
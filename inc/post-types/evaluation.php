<?php
/**
 * inc/post-types/evaluation.php
 */


// Register REST API endpoints
function register_evaluation_endpoints() {
    register_rest_route('ham/v1', '/evaluation/save', [
        'methods' => 'POST',
        'callback' => 'save_evaluation',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_rest_route('ham/v1', '/evaluation/get/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'get_evaluation',
        'permission_callback' => function() {
            return current_user_can('read');
        }
    ]);

    register_rest_route('ham/v1', '/evaluation/questions', [
        'methods' => 'GET',
        'callback' => 'get_evaluation_questions',
        'permission_callback' => function() {
            return true; // Allow public access to questions structure
        }
    ]);
    
    /**
     * Register a public endpoint for evaluation questions
     * 
     * This endpoint is created outside the HAM authentication system to allow public access
     * without requiring JWT authentication. The Headless Access Manager (HAM) plugin enforces
     * JWT authentication on all endpoints in the 'ham/v1' namespace, regardless of the 
     * permission_callback setting. 
     * 
     * By creating this endpoint in the 'public/v1' namespace, we ensure it can be accessed
     * without authentication, which is necessary for the initial loading of the evaluation form
     * before a user is authenticated.
     * 
     * @since 1.0.0
     */
    register_rest_route('public/v1', '/evaluation/questions', [
        'methods' => 'GET',
        'callback' => 'get_evaluation_questions',
        'permission_callback' => '__return_true' // Ensure public access
    ]);
}
add_action('rest_api_init', 'register_evaluation_endpoints');

function save_evaluation($request) {
    $params = $request->get_params();

    $post_data = [
        'post_type' => 'post',
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

    if (!$post || $post->post_type !== 'post') {
        return new WP_Error('not_found', 'Evaluation not found', ['status' => 404]);
    }

    return [
        'id' => $post_id,
        'formData' => get_post_meta($post_id, 'evaluation_data', true),
        'last_updated' => get_post_meta($post_id, 'last_updated', true)
    ];
}

/**
 * Get evaluation questions structure
 * 
 * This function returns the evaluation questions structure in the format expected by the frontend.
 * Each question has a text field and an options array with value, label, and stage properties.
 * 
 * @return array The evaluation questions structure
 */
function get_evaluation_questions() {
    // Default structure in case no HAM assessment data is found
    $questions_structure = [];
    
    // Try to get HAM assessments using a direct database query to avoid conflicts
    global $wpdb;
    $assessment_id = null;
    
    // First try to get the latest assessment
    $assessment_query = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY post_date DESC LIMIT 1",
        'ham_assessment',
        'publish'
    );
    $assessment_id = $wpdb->get_var($assessment_query);
    
    // If no assessment found, try specific ID from logs
    if (!$assessment_id) {
        $assessment_id = 564; // ID from logs
    }
    
    if ($assessment_id) {
        // Get assessment data from HAM using direct meta query
        $ham_data = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
            $assessment_id,
            '_ham_assessment_data'
        ));
        
        if ($ham_data) {
            $ham_data = maybe_unserialize($ham_data);
            
            if (is_array($ham_data)) {
                error_log('Retrieved assessment data for ID ' . $assessment_id . ': ' . json_encode(array_keys($ham_data)));
                
                // Transform HAM data to our expected format
                foreach ($ham_data as $section_id => $section) {
                    if (!isset($section['questions']) || !is_array($section['questions'])) {
                        continue;
                    }
                    
                    $questions_structure[$section_id] = [
                        'title' => isset($section['title']) ? $section['title'] : ucfirst($section_id),
                        'questions' => [],
                    ];
                    
                    foreach ($section['questions'] as $question_id => $question) {
                        if (!isset($question['text'])) {
                            continue;
                        }
                        
                        // Default options if not provided
                        $formatted_options = [
                            ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                            ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                            ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                            ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                            ['value' => '5', 'label' => '5', 'stage' => 'full']
                        ];
                        
                        // Use provided options if available
                        if (isset($question['options']) && is_array($question['options'])) {
                            $formatted_options = [];
                            foreach ($question['options'] as $option) {
                                // Ensure the option has the required fields
                                if (isset($option['value']) && isset($option['label'])) {
                                    $formatted_options[] = [
                                        'value' => $option['value'],
                                        'label' => $option['label'],
                                        'stage' => isset($option['stage']) ? $option['stage'] : 'trans',
                                    ];
                                }
                            }
                        }
                        
                        $questions_structure[$section_id]['questions'][$question_id] = [
                            'text' => $question['text'],
                            'options' => $formatted_options,
                        ];
                    }
                }
            }
        }
    }
    
    // If no valid sections were found, use fallback
    if (empty($questions_structure)) {
        error_log('No valid HAM assessment data found, using fallback structure');
        return get_fallback_questions_structure();
    }
    
    // For debugging
    error_log('Returning evaluation questions structure from HAM assessment: ' . json_encode(array_keys($questions_structure)));
    
    return $questions_structure;
}

/**
 * Get fallback questions structure
 * 
 * This function returns a hardcoded fallback structure for evaluation questions
 * when no HAM templates are available or valid.
 * 
 * @return array The fallback questions structure
 */
function get_fallback_questions_structure() {
    return [
        'anknytning' => [
            'title' => 'Anknytning',
            'questions' => [
                'a1' => [
                    'text' => 'Eleven söker kontakt med läraren vid behov',
                    'options' => [
                        ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                        ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                        ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                        ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                        ['value' => '5', 'label' => '5', 'stage' => 'full']
                    ]
                ],
                'a2' => [
                    'text' => 'Eleven tar emot tröst från läraren',
                    'options' => [
                        ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                        ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                        ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                        ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                        ['value' => '5', 'label' => '5', 'stage' => 'full']
                    ]
                ]
            ]
        ],
        'ansvar' => [
            'title' => 'Ansvar',
            'questions' => [
                'b1' => [
                    'text' => 'Eleven tar ansvar för sina uppgifter',
                    'options' => [
                        ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                        ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                        ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                        ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                        ['value' => '5', 'label' => '5', 'stage' => 'full']
                    ]
                ],
                'b2' => [
                    'text' => 'Eleven följer klassens regler',
                    'options' => [
                        ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                        ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                        ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                        ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                        ['value' => '5', 'label' => '5', 'stage' => 'full']
                    ]
                ]
            ]
        ]
    ];
}
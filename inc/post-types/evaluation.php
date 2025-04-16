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
    $questions_structure = [];
    
    // First, try to find posts explicitly marked as templates
    // The headless-access-manager plugin might use different meta keys for templates
    $possible_template_meta_keys = [
        '_ham_is_template',
        '_ham_template',
        'is_template',
        '_is_template',
        'template'
    ];
    
    // Log for debugging
    error_log('Attempting to find assessment template with meta keys: ' . json_encode($possible_template_meta_keys));
    
    // Try each possible meta key to find templates
    $found_template = false;
    foreach ($possible_template_meta_keys as $meta_key) {
        $template_query = new WP_Query([
            'post_type' => 'ham_assessment',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => $meta_key,
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]);
        
        // Log the query for debugging
        error_log("Trying meta key '$meta_key' query: " . json_encode($template_query->request));
        
        if ($template_query->have_posts()) {
            error_log("Found template with meta key: $meta_key");
            $assessment = $template_query->posts[0];
            $ham_data = get_post_meta($assessment->ID, '_ham_assessment_data', true);
            
            if (!empty($ham_data) && is_array($ham_data)) {
                error_log('Retrieved template data for ID ' . $assessment->ID . ': ' . json_encode(array_keys($ham_data)));
                process_ham_data($ham_data, $questions_structure);
                $found_template = true;
                break;
            }
        }
    }
    
    // If no explicit template was found, try admin-created posts without user_id
    if (!$found_template) {
        error_log('No explicit template found, looking for admin-created posts');
        
        // Look for posts that are likely admin-created templates (no user_id field)
        $admin_query = new WP_Query([
            'post_type' => 'ham_assessment',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_ham_student_id', // Filter out user submissions
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);
        
        error_log('Admin-created post query: ' . json_encode($admin_query->request));
        
        if ($admin_query->have_posts()) {
            error_log('Found admin-created post without student_id');
            $assessment = $admin_query->posts[0];
            $ham_data = get_post_meta($assessment->ID, '_ham_assessment_data', true);
            
            if (!empty($ham_data) && is_array($ham_data)) {
                error_log('Retrieved admin post data for ID ' . $assessment->ID . ': ' . json_encode(array_keys($ham_data)));
                process_ham_data($ham_data, $questions_structure);
                $found_template = true;
            }
        }
    }
    
    // As a last resort, try to find any ham_assessment with valid structure
    if (!$found_template) {
        error_log('No template or admin post found, trying fallback logic');
        
        $fallback_query = new WP_Query([
            'post_type' => 'ham_assessment',
            'posts_per_page' => 10, // Try more posts 
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'no_found_rows' => true
        ]);
        
        if ($fallback_query->have_posts()) {
            foreach ($fallback_query->posts as $post) {
                $ham_data = get_post_meta($post->ID, '_ham_assessment_data', true);
                
                if (!empty($ham_data) && is_array($ham_data)) {
                    // Check if this post has valid questions structure
                    $has_valid_structure = false;
                    foreach ($ham_data as $section_id => $section) {
                        if (isset($section['questions']) && is_array($section['questions']) && !empty($section['questions'])) {
                            $has_valid_structure = true;
                            break;
                        }
                    }
                    
                    if ($has_valid_structure) {
                        error_log('Found fallback post with valid structure, ID: ' . $post->ID);
                        process_ham_data($ham_data, $questions_structure);
                        $found_template = true;
                        break;
                    }
                }
            }
        }
    }
    
    // If still no valid structure, use the hardcoded fallback
    if (empty($questions_structure)) {
        error_log('No valid template found, using hardcoded fallback');
        return get_fallback_questions_structure();
    }
    
    return $questions_structure;
}

/**
 * Process HAM assessment data into questions structure
 * 
 * Helper function to avoid duplicate code when processing HAM data
 */
function process_ham_data($ham_data, &$questions_structure) {
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
            
            // Default options if not provided (scaled 1-5 with stages)
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
                            'stage' => isset($option['stage']) ? $option['stage'] : 'trans', // Default to 'trans' if no stage
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
    
    // Log structure for debugging
    error_log('Processed structure: ' . json_encode(array_keys($questions_structure)));
    
    // Check if we have valid options with stages for progress bars
    $has_valid_options = false;
    foreach ($questions_structure as $section_id => $section) {
        if (!empty($section['questions'])) {
            foreach ($section['questions'] as $question_id => $question) {
                if (!empty($question['options'])) {
                    $has_stages = false;
                    foreach ($question['options'] as $option) {
                        if (isset($option['stage'])) {
                            $has_stages = true;
                            break;
                        }
                    }
                    if ($has_stages) {
                        $has_valid_options = true;
                        break;
                    }
                }
            }
        }
    }
    
    if (!$has_valid_options) {
        error_log('WARNING: No valid options with stages found in template data. Progress bars may not work correctly.');
    }
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
                    'text' => 'Admin har en testfråga här',
                    'options' => [
                        ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                        ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                        ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                        ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                        ['value' => '5', 'label' => '5', 'stage' => 'full']
                    ]
                ],
                'a2' => [
                    'text' => 'Eleven får en fråga till här',
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
                    'text' => 'Programmeraren tar ansvar för sina uppgifter',
                    'options' => [
                        ['value' => '1', 'label' => '1', 'stage' => 'ej'],
                        ['value' => '2', 'label' => '2', 'stage' => 'ej'],
                        ['value' => '3', 'label' => '3', 'stage' => 'trans'],
                        ['value' => '4', 'label' => '4', 'stage' => 'trans'],
                        ['value' => '5', 'label' => '5', 'stage' => 'full']
                    ]
                ],
                'b2' => [
                    'text' => 'Programmeraren följer klassens regler',
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
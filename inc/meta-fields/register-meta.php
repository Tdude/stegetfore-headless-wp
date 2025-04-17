<?php
/**
* inc/meta-fields/register-meta.php
*/

/**
 * Safe JSON parser - prevents errors when invalid JSON is encountered
 * Use this instead of json_decode throughout the theme
 */
function safe_json_decode($json, $assoc = false, $depth = 512, $options = 0) {
    // Early exit for empty strings
    if (empty($json) || !is_string($json)) {
        return $assoc ? [] : null;
    }

    // Quick check if it looks like JSON
    $trimmed = trim($json);
    $first_char = substr($trimmed, 0, 1);
    $last_char = substr($trimmed, -1);
    $looks_like_json =
        ($first_char === '{' && $last_char === '}') ||
        ($first_char === '[' && $last_char === ']');

    if (!$looks_like_json) {
        return $assoc ? [] : null;
    }

    // Try to decode with error suppression
    try {
        $result = json_decode($json, $assoc, $depth, $options);

        // Check for JSON errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON parsing error: ' . json_last_error_msg() . ' in: ' . substr($json, 0, 100) . '...');
            return $assoc ? [] : null;
        }

        return $result;
    } catch (Exception $e) {
        error_log('Exception during JSON parsing: ' . $e->getMessage());
        return $assoc ? [] : null;
    }
}

 // Content type Portfolio NOT ACTIVATED in frontend!
function register_portfolio_meta_fields() {
    register_post_meta('portfolio', 'project_url', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('portfolio', 'project_date', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}
add_action('init', 'register_portfolio_meta_fields');

function register_template_choice_rest_field() {
    register_rest_field(
        array('page', 'post'),
        'template',
        array(
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_wp_page_template', true);
            },
            'update_callback' => function($value, $object) {
                if (!$value || !is_string($value)) {
                    return;
                }
                return update_post_meta($object->ID, '_wp_page_template', $value);
            },
            'schema' => array(
                'description' => 'Template choice for this page',
                'type' => 'string',
                'context' => array('view', 'edit'),
            ),
        )
    );
}
add_action('rest_api_init', 'register_template_choice_rest_field');

function register_custom_templates($templates) {
    $custom_templates = [
        'templates/full-width.php' => 'Full Width Layout',
        'templates/sidebar.php'    => 'Sidebar Layout',
        'templates/landing.php'    => 'Startsida m blogg (Landing Page)',
        'templates/evaluation.php' => '',
        'templates/circle-chart.php' => 'Cirkeldiagram (Circle Chart)',
        'templates/contact.php' => 'Kontaktformulär (WPCF7)',
        'templates/homepage.php' => 'Startsidan (Specialinlägg)'
    ];
    return array_merge($templates, $custom_templates);
}
add_filter('theme_page_templates', 'register_custom_templates');

// Content display toggle - controls whether to show/hide content when modules are present
function register_show_content_with_modules_meta() {
    // Register for all supported post types including HAM assessments
    register_meta('post', 'show_content_with_modules', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => false,
        'description' => 'Whether to hide the main content when modules are present',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        },
        'object_subtype' => '' // Apply to all post types
    ]);
    
    // Register content position meta for pages
    register_meta('post', 'content_position', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => 'after',
        'description' => 'Position of the content relative to modules (before/after)',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        },
        'object_subtype' => '' // Apply to all post types 
    ]);
}
add_action('init', 'register_show_content_with_modules_meta');

// Register REST field to expose content display settings
function register_show_content_with_modules_rest_field() {
    // Get all supported post types (including custom post types)
    $all_post_types = get_post_types(['show_in_rest' => true], 'names');
    
    // Add support for HAM assessment type specifically, even if it's not public
    if (!in_array('ham_assessment', $all_post_types)) {
        $all_post_types[] = 'ham_assessment';
    }
    
    // Add the content display settings field to the REST API
    register_rest_field(
        $all_post_types,
        'content_display_settings',
        [
            'get_callback' => function ($post) {
                $post_id = $post['id'];
                $show_content = get_post_meta($post_id, 'show_content_with_modules', true);
                $content_position = get_post_meta($post_id, 'content_position', true);
                
                // Debug output for specific post types or problematic posts
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    $post_type = get_post_type($post_id);
                    if ($post_type === 'ham_assessment') {
                        error_log("HAM Assessment ID: {$post_id} - Content settings: show={$show_content}, position={$content_position}");
                    }
                }
                
                // Use explicit boolean conversion and provide defaults
                return [
                    'show_content_with_modules' => $show_content === '1' || $show_content === 'true' || $show_content === true || $show_content === 1,
                    'content_position' => !empty($content_position) ? $content_position : 'before',
                ];
            },
            'update_callback' => function ($value, $post) {
                if (!current_user_can('edit_post', $post->ID)) {
                    return false;
                }

                // If not an array, try to decode or default
                if (!is_array($value)) {
                    if (is_string($value)) {
                        $decoded = safe_json_decode($value, true);
                        if (is_array($decoded)) {
                            $value = $decoded;
                        } else {
                            // Default to safe values if string is not JSON
                            $value = [
                                'show_content_with_modules' => true,
                                'content_position' => 'before',
                            ];
                        }
                    } else {
                        return false;
                    }
                }

                // Update each setting individually
                if (isset($value['show_content_with_modules'])) {
                    $show_content = $value['show_content_with_modules'] ? '1' : '0';
                    update_post_meta($post->ID, 'show_content_with_modules', $show_content);
                }

                if (isset($value['content_position'])) {
                    $position = in_array($value['content_position'], ['before', 'after'])
                        ? $value['content_position']
                        : 'before';
                    update_post_meta($post->ID, 'content_position', $position);
                }

                return true;
            },
            'schema' => [
                'description' => 'Content display settings for the post/page',
                'type' => 'object',
                'properties' => [
                    'show_content_with_modules' => [
                        'type' => 'boolean',
                        'description' => 'Whether to show content when modules are present',
                    ],
                    'content_position' => [
                        'type' => 'string',
                        'enum' => ['before', 'after'],
                        'description' => 'Where to position the content relative to modules',
                    ],
                ],
            ],
        ]
    );
}
add_action('rest_api_init', 'register_show_content_with_modules_rest_field');

// Featured posts on the Startpage
function register_featured_post_meta() {
    register_post_meta('post', 'is_featured', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => false
    ]);
}
add_action('init', 'register_featured_post_meta');
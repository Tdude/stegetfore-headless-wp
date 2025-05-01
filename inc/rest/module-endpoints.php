<?php
/** inc/rest/module-endpoints.php
 *
 * Register REST API endpoints for modules
 */

// --- DRY Helper for getting JSON meta array ---
function get_json_meta_array($post_id, $meta_key) {
    $raw = get_post_meta($post_id, $meta_key, true);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function register_modules_rest_routes()
{
    register_rest_route('steget/v1', '/modules', [
        'methods' => 'GET',
        'callback' => 'get_modules_endpoint',
        'permission_callback' => '__return_true',
        'args' => [
            'template' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Filter by module template'
            ],
            'category' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Filter by module category slug'
            ],
            'placement' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Filter by module placement slug'
            ],
            'per_page' => [
                'type' => 'integer',
                'required' => false,
                'default' => 10
            ],
            'page' => [
                'type' => 'integer',
                'required' => false,
                'default' => 1
            ]
        ]
    ]);

    register_rest_route('steget/v1', '/modules/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'get_single_module_endpoint',
        'permission_callback' => '__return_true',
        'args' => [
            'id' => [
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                }
            ]
        ]
    ]);

    // Add debug route for page modules
    register_rest_route('steget/v1', '/modules/debug/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            $page_id = $request['id'];
            $page_modules = get_post_meta($page_id, 'page_modules', true);
            
            // Get all modules for comparison
            $all_modules = get_posts([
                'post_type' => 'module',
                'posts_per_page' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ]);
            
            return [
                'page_id' => $page_id,
                'page_title' => get_the_title($page_id),
                'stored_modules' => [
                    'raw' => $page_modules,
                    'decoded' => json_decode($page_modules, true)
                ],
                'all_available_modules' => array_map(function($post) {
                    return [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'menu_order' => $post->menu_order,
                        'status' => $post->post_status
                    ];
                }, $all_modules)
            ];
        },
        'permission_callback' => '__return_true'  // Allow anyone to access for debugging
    ]);
}
add_action('rest_api_init', 'register_modules_rest_routes');

/**
 * Modules endpoint callback
 */
function get_modules_endpoint($request)
{
    $args = [
        'post_type' => 'module',
        'posts_per_page' => $request['per_page'],
        'paged' => $request['page'],
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ];

    // Template filter
    if (isset($request['template']) && !empty($request['template'])) {
        $args['meta_query'][] = [
            'key' => 'module_template',
            'value' => $request['template'],
            'compare' => '='
        ];
    }

    // Category filter
    if (isset($request['category']) && !empty($request['category'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'module_category',
            'field' => 'slug',
            'terms' => $request['category']
        ];
    }

    // Placement filter
    if (isset($request['placement']) && !empty($request['placement'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'module_placement',
            'field' => 'slug',
            'terms' => $request['placement']
        ];
    }

    $modules_query = new WP_Query($args);
    $modules = [];

    if ($modules_query->have_posts()) {
        foreach ($modules_query->posts as $post) {
            $modules[] = prepare_module_for_response($post);
        }
    }

    return [
        'modules' => $modules,
        'total' => $modules_query->found_posts,
        'pages' => $modules_query->max_num_pages
    ];
}

/**
 * Single module endpoint callback
 */
function get_single_module_endpoint($request)
{
    $post = get_post($request['id']);

    if (!$post || $post->post_type !== 'module' || $post->post_status !== 'publish') {
        return new WP_Error(
            'module_not_found',
            __('Module not found', 'steget'),
            ['status' => 404]
        );
    }

    return prepare_module_for_response($post);
}

/**
 * Prepare module data for REST response
 */
function prepare_module_for_response($post)
{
    $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
    $template = get_post_meta($post->ID, 'module_template', true);
    // Get explicit order if set, or use menu_order field as fallback
    $explicit_order = get_post_meta($post->ID, 'module_order', true);
    $menu_order = $post->menu_order;
    // Module type priorities - higher numbers appear first in display
    $type_priorities = [
        'hero' => 100,
        'selling-points' => 90,
        'cta' => 80,
        'testimonials' => 70,
        'faq' => 60,
        'tabbed-content' => 50
    ];

    // Create consistent type naming (convert underscores to hyphens)
    $type = str_replace('_', '-', $template);

    // Use type priority if available, otherwise fallback to existing order
    $type_priority = isset($type_priorities[$type]) ? $type_priorities[$type] : 0;
    $existing_order = !empty($explicit_order) ? intval($explicit_order) : $menu_order;

    // For JSON fields, properly decode to remove excessive slashes
    $json_fields = [
        'selling_points' => 'module_selling_points',
        'stats' => 'module_stats',
        'testimonials' => 'module_testimonials',
        'faq_items' => 'module_faq_items',
        'tabbed_content' => 'module_tabbed_content'
    ];
    foreach ($json_fields as $field => $meta_key) {
        $data[$field] = get_json_meta_array($post->ID, $meta_key);
    }

    // --- BUTTONS: Always decode as array of objects, never array of JSON strings ---
    $buttons_raw = get_post_meta($post->ID, 'module_buttons', true);
    $buttons = json_decode($buttons_raw, true);
    // If any item is a string, legacy: decode it
    if (is_array($buttons) && isset($buttons[0]) && is_string($buttons[0])) {
        $buttons = array_map(function($btn) {
            return is_string($btn) ? json_decode($btn, true) : $btn;
        }, $buttons);
    }
    // Ensure every button has a size, default to 'md' if missing
    if (is_array($buttons)) {
        foreach ($buttons as &$btn) {
            if (!isset($btn['size']) || !$btn['size']) {
                $btn['size'] = 'md';
            }
        }
        unset($btn);
    }
    $data['buttons'] = is_array($buttons) ? $buttons : [];

    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'excerpt' => $post->post_excerpt,
        'slug' => $post->post_name,
        'featured_image' => $featured_image,
        'date' => $post->post_date,
        'modified' => $post->post_modified,
        'layout' => get_post_meta($post->ID, 'module_layout', true),
        'fullWidth' => (bool) get_post_meta($post->ID, 'module_full_width', true), // camelCase
        'backgroundColor' => get_post_meta($post->ID, 'module_background_color', true), // camelCase
        'categories' => wp_get_post_terms($post->ID, 'module_category', ['fields' => 'slugs']),
        'placements' => wp_get_post_terms($post->ID, 'module_placement', ['fields' => 'names']),
        'type' => $type,
        // Use menu_order as the primary sort criterion
        'order' => $post->menu_order,
        // Keep typeOrder for backwards compatibility
        'typeOrder' => $type_priority,
        // Initialize points as empty array by default for selling-points type
        'points' => $type === 'selling-points' ? [] : null
    ];

    // Always include buttons for hero and cta modules
    if (in_array($template, ['hero', 'cta'])) {
        $data['buttons'] = is_array($buttons) ? $buttons : [];
    }
    // Always include items for faq modules
    if ($template === 'faq') {
        $faq_items = get_post_meta($post->ID, 'module_faq_items', true);
        $data['items'] = is_string($faq_items) ? json_decode(stripslashes_deep($faq_items), true) : [];
        if (!is_array($data['items'])) $data['items'] = [];
        $data['allowMultipleOpen'] = true;
    }
    if ($template === 'testimonials') {
        $testimonials = get_post_meta($post->ID, 'module_testimonials', true);
        $data['testimonials'] = is_string($testimonials) ? json_decode(stripslashes_deep($testimonials), true) : [];
        if (!is_array($data['testimonials'])) $data['testimonials'] = [];
        $data['displayStyle'] = get_post_meta($post->ID, 'module_testimonials_style', true) ?: 'carousel';
    }

    // Format module-specific data to match React component props
    switch ($template) {
        case 'hero':
            $hero_settings = json_decode(get_post_meta($post->ID, 'module_hero_settings', true), true) ?: [];
            $data = array_merge($data, [
                'overlayOpacity' => $hero_settings['overlay_opacity'] ?? 0.3,
                'textColor' => $hero_settings['text_color'] ?? '',
                'height' => $hero_settings['height'] ?? 'large',
                'alignment' => $hero_settings['alignment'] ?? 'center',
            ]);
            break;

        case 'selling_points':
            $points = get_json_meta_array($post->ID, 'module_selling_points');
            
            // Transform points to match frontend structure
            $data['points'] = array_map(function($point) {
                return [
                    'id' => uniqid(), // Add an ID for React keys
                    'title' => $point['title'] ?? '',
                    'description' => $point['description'] ?? '',
                    'icon' => $point['icon'] ?? '',
                    'content' => $point['description'] ?? ''
                ];
            }, $points);
            
            $data['columns'] = intval(get_post_meta($post->ID, 'module_columns', true) ?: 3);
            // Ensure type is consistent with template name
            $data['type'] = 'selling_points';
            break;

        case 'cta':
            // If using buttons array, also extract first button for direct properties
            if (!empty($data['buttons']) && is_array($data['buttons'])) {
                $first_button = $data['buttons'][0];
                $data['buttonText'] = $first_button['text'] ?? '';
                $data['buttonUrl'] = $first_button['url'] ?? '';
            }
            $data['description'] = $post->post_content;
            break;

        case 'testimonials':
            $testimonials = get_json_meta_array($post->ID, 'module_testimonials');
            $data['testimonials'] = $testimonials;
            $data['displayStyle'] = get_post_meta($post->ID, 'module_testimonials_style', true) ?: 'carousel';
            break;

        case 'faq':
            $data['items'] = get_json_meta_array($post->ID, 'module_faq_items');
            $data['allowMultipleOpen'] = true; // camelCase
            break;

        case 'tabbed_content':
            $data['tabs'] = get_json_meta_array($post->ID, 'module_tabbed_content');
            $data['layout'] = get_post_meta($post->ID, 'tabbed_content_layout', true)
                ?: get_post_meta($post->ID, 'module_tabbed_content_layout', true)
                ?: get_post_meta($post->ID, 'module_layout', true);
            break;

        case 'featured_posts':
            $settings = json_decode(get_post_meta($post->ID, 'module_featured_posts_settings', true), true) ?: [];

            // Prepare query args
            $args = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => isset($settings['post_count']) ? intval($settings['post_count']) : 6,
            ];

            // Add category filter if specified
            if (!empty($settings['categories'])) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'category',
                        'field' => 'term_id',
                        'terms' => $settings['categories']
                    ]
                ];
            }

            // Query for posts
            $posts_query = new WP_Query($args);

            $posts_data = [];
            if ($posts_query->have_posts()) {
                foreach ($posts_query->posts as $post_item) {
                    $featured_image = get_the_post_thumbnail_url($post_item->ID, 'medium_large');
                    $author_id = $post_item->post_author;

                    $post_data = [
                        'id' => $post_item->ID,
                        'title' => $post_item->post_title,
                        'excerpt' => get_the_excerpt($post_item),
                        'content' => $post_item->post_content,
                        'date' => get_the_date('c', $post_item),
                        'link' => get_permalink($post_item->ID),
                        'slug' => $post_item->post_name,
                        'featured_image' => $featured_image ?: null,
                        'categories' => wp_get_post_categories($post_item->ID, ['fields' => 'names']),
                    ];

                    // Add author info if requested
                    if (isset($settings['show_author']) && $settings['show_author']) {
                        $post_data['author'] = [
                            'id' => $author_id,
                            'name' => get_the_author_meta('display_name', $author_id),
                            'avatar' => get_avatar_url($author_id, ['size' => 96])
                        ];
                    }

                    $posts_data[] = $post_data;
                }
            }

            $data['title'] = isset($settings['title']) ? $settings['title'] : $post->post_title;
            $data['subtitle'] = $settings['subtitle'] ?? '';
            $data['display_style'] = $settings['display_style'] ?? 'grid';
            $data['show_date'] = $settings['show_date'] ?? true;
            $data['show_excerpt'] = $settings['show_excerpt'] ?? true;
            $data['show_author'] = $settings['show_author'] ?? false;
            $data['posts'] = $posts_data;
            break;
    }

    // Ensure all text fields are properly UTF-8 encoded
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $data[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        }
    }

    return $data;
}

/**
 * Register REST field for page modules
 */
function register_page_modules_rest_field()
{
    register_rest_field(
        ['page', 'post'],
        'modules',
        [
            'get_callback' => function ($post) {
                $post_id = $post['id'];
                $page_slug = get_post_field('post_name', $post_id);
                $page_template = get_post_meta($post_id, '_wp_page_template', true);
                
                // Get the modules that are directly assigned to this page
                $page_modules = get_post_meta($post_id, 'page_modules', true);
                
                error_log("Processing modules for page: $page_slug (ID: $post_id, Template: $page_template)");
                
                // Ensure we have a string before trying to handle it as JSON
                if (!is_string($page_modules)) {
                    error_log("Modules meta is not a string: " . gettype($page_modules));
                    $page_modules = '';
                }
                
                // Convert JSON string to array using safe parsing
                $modules_array = json_decode($page_modules, true);
                if (empty($modules_array)) {
                    error_log("No valid modules found in page_modules meta for page: $page_slug");
                    $modules_array = [];
                } else {
                    error_log("Successfully decoded modules JSON. Found " . count($modules_array) . " modules");
                }
                
                $modules_data = [];
                
                // Process each module
                foreach ($modules_array as $index => $module_data) {
                    if (!isset($module_data['id'])) {
                        error_log("Missing module ID at index $index");
                        continue;
                    }
                    
                    $module_id = $module_data['id'];
                    $module_post = get_post($module_id);
                    
                    if (!$module_post || $module_post->post_status !== 'publish') {
                        error_log("Module not found or not published: $module_id");
                        continue;
                    }
                    
                    // Prepare module data for response
                    $module = prepare_module_for_response($module_post);
                    $module['order'] = $index;
                    
                    // Include any overridden settings from the page association
                    if (isset($module_data['override_settings']) && $module_data['override_settings']) {
                        if (isset($module_data['layout'])) {
                            $module['layout'] = $module_data['layout'];
                        }
                        if (isset($module_data['full_width'])) {
                            $module['fullWidth'] = $module_data['full_width'];
                        }
                        if (isset($module_data['background_color'])) {
                            $module['backgroundColor'] = $module_data['background_color'];
                        }
                    }
                    
                    $modules_data[] = $module;
                    error_log("Added module: {$module_post->post_title} (ID: {$module_id})");
                }
                
                // For known problematic pages, log additional debugging info
                if ($page_slug === 'moduloversikt' && empty($modules_data)) {
                    error_log("DEBUG: No modules found for moduloversikt page");
                    
                    // Do a direct database query to check for modules
                    global $wpdb;
                    $meta_results = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'page_modules'",
                            $post_id
                        )
                    );
                    
                    if (empty($meta_results)) {
                        error_log("No page_modules meta found in database!");
                    } else {
                        error_log("Found page_modules meta in database: " . print_r($meta_results, true));
                        
                        // Try to decode again directly from database result
                        foreach ($meta_results as $meta) {
                            $meta_value = $meta->meta_value;
                            error_log("Meta value from DB: " . substr($meta_value, 0, 200));
                            
                            try {
                                $test_decode = json_decode($meta_value, true);
                                if (is_array($test_decode)) {
                                    error_log("DB meta decoded successfully to array with " . count($test_decode) . " items");
                                } else {
                                    error_log("DB meta decoded to non-array: " . gettype($test_decode));
                                }
                            } catch (Exception $e) {
                                error_log("Error decoding DB meta: " . $e->getMessage());
                            }
                        }
                    }
                }
                
                error_log("Returning " . count($modules_data) . " modules for page: $page_slug");
                return $modules_data;
            },
            'update_callback' => function ($value, $post, $field_name, $request, $object_type) {
                // Skip processing if it's a parent update from the save_post hook
                if (doing_action('save_post')) {
                    return true;
                }
                
                $post_id = $post->ID;
                
                // Basic validation: ensure we have a proper modules array
                if (!is_array($value)) {
                    error_log("Received non-array data for post ID $post_id - skipping modules update");
                    return true; // Return success but don't update
                }
                
                // Verify that each item has an ID (basic structure check)
                foreach ($value as $module) {
                    if (!is_array($module) || !isset($module['id'])) {
                        error_log("Received invalid module structure for post ID $post_id - skipping modules update");
                        return true; // Return success but don't update
                    }
                }
                
                // Prepare data for storage
                $modules_to_save = array();
                foreach ($value as $module) {
                    $module_data = array(
                        'id' => absint($module['id']),
                    );
                    
                    // Include any overridden settings
                    if (isset($module['override_settings']) && $module['override_settings']) {
                        $module_data['override_settings'] = true;
                        
                        if (isset($module['layout'])) {
                            $module_data['layout'] = sanitize_text_field($module['layout']);
                        }
                        
                        if (isset($module['fullWidth'])) {
                            $module_data['full_width'] = (bool) $module['fullWidth'];
                        }
                        
                        if (isset($module['backgroundColor'])) {
                            $module_data['background_color'] = sanitize_text_field($module['backgroundColor']);
                        }
                    }
                    
                    $modules_to_save[] = $module_data;
                }
                
                // Convert to JSON for storage
                $json = json_encode($modules_to_save, JSON_UNESCAPED_UNICODE);
                if ($json === false) {
                    error_log("Failed to encode modules to JSON for post ID $post_id: " . json_last_error_msg());
                    return false;
                }
                
                // Update post meta with the modules data
                $result = update_post_meta($post_id, 'page_modules', $json);
                
                return $result !== false;
            },
            'schema' => [
                'description' => __('Modules associated with this page', 'steget'),
                'type' => 'array',
                'items' => [
                    'type' => 'object'
                ]
            ]
        ]
    );
}
add_action('rest_api_init', 'register_page_modules_rest_field');

/**
 * Fix encoding in REST API responses for modules
 */
function fix_module_rest_api_encoding($response, $post, $request)
{
    if ($post->post_type !== 'module') {
        return $response;
    }

    $data = $response->get_data();
    
    // Fix potentially problematic fields
    if (isset($data['points']) && is_array($data['points'])) {
        // Re-encode points through proper JSON encoding
        $json_encoded = wp_json_encode($data['points'], JSON_UNESCAPED_UNICODE);
        $data['points'] = json_decode($json_encoded, true);
    }

    // Do the same for other problematic fields
    $json_fields = ['items', 'tabs'];
    foreach ($json_fields as $field) {
        if (isset($data[$field]) && is_array($data[$field])) {
            $json_encoded = wp_json_encode($data[$field], JSON_UNESCAPED_UNICODE);
            $data[$field] = json_decode($json_encoded, true);
        }
    }

    $response->set_data($data);
    return $response;
}
add_filter('rest_prepare_module', 'fix_module_rest_api_encoding', 10, 3);

/**
 * Fix Swedish character encoding issues in REST API responses
 * This is a targeted fix for the specific issue with Swedish characters
 */
function fix_swedish_characters_in_rest_api() {
    // Modify how safe_json_decode handles Swedish characters
    global $safe_json_decode_fixed;
    
    if (!isset($safe_json_decode_fixed) || !$safe_json_decode_fixed) {
        // Only apply the fix once
        $safe_json_decode_fixed = true;
        
        // Add a pre-filter to ensure wp_json_encode uses proper flags
        add_filter('wp_json_encode_options', function($options) {
            return $options | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT;
        });
        
        // Make sure response headers indicate proper UTF-8 encoding
        add_filter('rest_pre_serve_request', function($served, $result) {
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            }
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, Origin, X-Requested-With, Accept');
            header('Access-Control-Allow-Credentials: true');
            header('Content-Type: application/json; charset=utf-8');
            return $served;
        }, 10, 2);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Applied Swedish character encoding fix to REST API');
        }
    }
}
add_action('rest_api_init', 'fix_swedish_characters_in_rest_api', 5); // Run early in the process
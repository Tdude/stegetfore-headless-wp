<?php
/** inc/rest/module-endpoints.php
 *
 * Register REST API endpoints for modules
 */
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
        'post_status' => 'publish'
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
    $json_fields = ['buttons', 'selling_points', 'stats', 'testimonials', 'faq_items', 'tabbed_content'];
    foreach ($json_fields as $field) {
        $meta_key = 'module_' . $field;
        $meta_value = get_post_meta($post->ID, $meta_key, true);

        if (!empty($meta_value)) {
            // Strip slashes if needed and decode the JSON
            $decoded = json_decode(stripslashes_deep($meta_value), true);
            if (is_array($decoded)) {
                $data[$field] = $decoded;
            }
        }
    }

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
        'buttons' => json_decode(get_post_meta($post->ID, 'module_buttons', true), true),
        'categories' => wp_get_post_terms($post->ID, 'module_category', ['fields' => 'slugs']),
        'placements' => wp_get_post_terms($post->ID, 'module_placement', ['fields' => 'names']),
        'type' => $type,
        // Use type priority as primary sort, existing order as secondary
        'order' => $type_priority > 0 ? $type_priority : $existing_order,
        'typeOrder' => $type_priority,
    ];

    // Format module-specific data to match React component props
    switch ($template) {
        case 'hero':
            $hero_settings = json_decode(get_post_meta($post->ID, 'module_hero_settings', true), true) ?: [];
            $data = array_merge($data, [
                'intro' => $post->post_excerpt,
                'image' => $featured_image,
                'overlayOpacity' => $hero_settings['overlay_opacity'] ?? 0.3,
                'textColor' => $hero_settings['text_color'] ?? '',
                'height' => $hero_settings['height'] ?? 'large',
                'alignment' => $hero_settings['alignment'] ?? 'center',
            ]);
            break;

        case 'selling_points':
            $points = json_decode(get_post_meta($post->ID, 'module_selling_points', true), true) ?: [];
            $data['points'] = $points; // This matches what React expects
            $data['columns'] = intval(get_post_meta($post->ID, 'module_columns', true) ?: 3);
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
            $testimonials = json_decode(get_post_meta($post->ID, 'module_testimonials', true), true) ?: [];
            $data['testimonials'] = $testimonials;
            $data['displayStyle'] = get_post_meta($post->ID, 'module_testimonials_style', true) ?: 'carousel';
            break;

        case 'faq':
            $data['items'] = json_decode(get_post_meta($post->ID, 'module_faq_items', true), true) ?: [];
            $data['allowMultipleOpen'] = true; // camelCase
            break;

        case 'tabbed_content':
            $data['tabs'] = json_decode(get_post_meta($post->ID, 'module_tabbed_content', true), true) ?: [];
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
 * Register REST field for module preview
 */
function register_module_preview_rest_field()
{
    register_rest_field('module', 'preview_html', [
        'get_callback' => function ($post) {
            // Get minimal rendered HTML for preview purposes
            $template = get_post_meta($post['id'], 'module_template', true);

            if (!$template) {
                return '';
            }

            // Basic wrapper with module class
            $html = '<div class="module module-' . esc_attr($template) . '">';

            // Add basic content based on template type
            switch ($template) {
                case 'hero':
                    $title = $post['title']['rendered'];
                    $content = $post['content']['rendered'];
                    $image = $post['featured_image_url'] ?? '';

                    $html .= '<div class="hero-container">';
                    if ($image) {
                        $html .= '<div class="hero-image" style="background-image: url(\'' . esc_url($image) . '\');"></div>';
                    }
                    $html .= '<div class="hero-content">';
                    $html .= '<h2>' . $title . '</h2>';
                    $html .= $content;
                    $html .= '</div></div>';
                    break;

                case 'testimonials':
                    $html .= '<div class="testimonials-container">';
                    $html .= '<h2>' . $post['title']['rendered'] . '</h2>';
                    $html .= '<div class="testimonials-preview">' . __('Testimonials module preview', 'steget') . '</div>';
                    $html .= '</div>';
                    break;

                default:
                    $html .= '<h2>' . $post['title']['rendered'] . '</h2>';
                    $html .= $post['content']['rendered'];
            }

            $html .= '</div>';

            return $html;
        }
    ]);
}
add_action('rest_api_init', 'register_module_preview_rest_field');


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
                $page_modules = json_decode(get_post_meta($post['id'], 'page_modules', true), true) ?: [];
                $modules_data = [];

                foreach ($page_modules as $index => $module_data) {
                    $module_id = $module_data['id'];
                    $module_post = get_post($module_id);

                    if ($module_post && $module_post->post_status === 'publish') {
                        $module = prepare_module_for_response($module_post);

                        // Set the order explicitly
                        if (isset($module_data['order'])) {
                            $module['order'] = intval($module_data['order']);
                        } else {
                            $module['order'] = $index; // Use the array index as fallback
                        }

                        $modules_data[] = $module;
                    }
                }

                // Sort modules by order property
                usort($modules_data, function ($a, $b) {
                    return ($a['order'] ?? 0) - ($b['order'] ?? 0);
                });

                return $modules_data;
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
    $json_fields = ['buttons', 'items', 'tabs'];
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
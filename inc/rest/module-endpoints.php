<?php
/** inc/rest/module-endpoints.php
 *
 * Register REST API endpoints for modules
*/
function register_modules_rest_routes() {
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
                'validate_callback' => function($param) {
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
function get_modules_endpoint($request) {
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
function get_single_module_endpoint($request) {
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
function prepare_module_for_response($post) {
    $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
    $template = get_post_meta($post->ID, 'module_template', true);
    // Get explicit order if set, or use menu_order field as fallback
    $explicit_order = get_post_meta($post->ID, 'module_order', true);
    $menu_order = $post->menu_order;
    // Create consistent type naming (convert underscores to hyphens)
    $type = str_replace('_', '-', $template);

    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'excerpt' => $post->post_excerpt,
        'slug' => $post->post_name,
        'featured_image' => $featured_image,
        'date' => $post->post_date,
        'modified' => $post->post_modified,
        'type' => $type, // Add this for React components
        'layout' => get_post_meta($post->ID, 'module_layout', true),
        'fullWidth' => (bool) get_post_meta($post->ID, 'module_full_width', true), // camelCase
        'backgroundColor' => get_post_meta($post->ID, 'module_background_color', true), // camelCase
        'buttons' => json_decode(get_post_meta($post->ID, 'module_buttons', true), true),
        'categories' => wp_get_post_terms($post->ID, 'module_category', ['fields' => 'names']),
        'placements' => wp_get_post_terms($post->ID, 'module_placement', ['fields' => 'names']),
        'order' => !empty($explicit_order) ? intval($explicit_order) : $menu_order,
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

        // Add other module types...

        case 'faq':
            $data['items'] = json_decode(get_post_meta($post->ID, 'module_faq_items', true), true) ?: [];
            $data['allowMultipleOpen'] = true; // camelCase
            break;

        case 'tabbed_content':
            $data['tabs'] = json_decode(get_post_meta($post->ID, 'module_tabbed_content', true), true) ?: [];
            break;
    }

    return $data;
}

/**
 * Register REST field for module preview
 */
function register_module_preview_rest_field() {
    register_rest_field('module', 'preview_html', [
        'get_callback' => function($post) {
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
function register_page_modules_rest_field() {
    register_rest_field(
        ['page', 'post'],
        'modules',
        [
            'get_callback' => function($post) {
                $page_modules = json_decode(get_post_meta($post['id'], 'page_modules', true), true) ?: [];
                $modules_data = [];

                // Tracking position for implicit ordering if needed
                $position = 0;

                foreach ($page_modules as $module_data) {
                    $module_id = $module_data['id'];
                    $module_post = get_post($module_id);

                    if ($module_post && $module_post->post_status === 'publish') {
                        $module = prepare_module_for_response($module_post);

                        // Override settings if needed
                        if (isset($module_data['override_settings']) && $module_data['override_settings']) {
                            // Existing overrides...

                            // Include explicit order if set in page_modules
                            if (isset($module_data['order'])) {
                                $module['order'] = intval($module_data['order']);
                            } else {
                                // Use position as implicit order
                                $module['order'] = $position;
                            }
                        }

                        $modules_data[] = $module;
                    }

                    $position++;
                }

                // Sort modules by order property
                usort($modules_data, function($a, $b) {
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
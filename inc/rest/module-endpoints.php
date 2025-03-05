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

    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'excerpt' => $post->post_excerpt,
        'slug' => $post->post_name,
        'featured_image' => $featured_image,
        'date' => $post->post_date,
        'modified' => $post->post_modified,
        'template' => $template,
        'layout' => get_post_meta($post->ID, 'module_layout', true),
        'full_width' => (bool) get_post_meta($post->ID, 'module_full_width', true),
        'background_color' => get_post_meta($post->ID, 'module_background_color', true),
        'buttons' => json_decode(get_post_meta($post->ID, 'module_buttons', true), true),
        'categories' => wp_get_post_terms($post->ID, 'module_category', ['fields' => 'names']),
        'placements' => wp_get_post_terms($post->ID, 'module_placement', ['fields' => 'names'])
    ];

    // Add template-specific data
    switch ($template) {
        case 'hero':
            $data['hero_settings'] = json_decode(get_post_meta($post->ID, 'module_hero_settings', true), true);
            break;

        case 'selling_points':
            $data['selling_points'] = json_decode(get_post_meta($post->ID, 'module_selling_points', true), true);
            break;

        case 'stats':
            $data['stats'] = json_decode(get_post_meta($post->ID, 'module_stats', true), true);
            break;

        case 'testimonials':
            $testimonial_ids = json_decode(get_post_meta($post->ID, 'module_testimonials_ids', true), true) ?: [];

            $testimonials = [];
            foreach ($testimonial_ids as $id) {
                $testimonial = get_post($id);
                if ($testimonial && $testimonial->post_status === 'publish') {
                    $testimonials[] = [
                        'id' => $testimonial->ID,
                        'content' => $testimonial->post_content,
                        'author_name' => get_post_meta($testimonial->ID, 'author_name', true) ?: $testimonial->post_title,
                        'author_position' => get_post_meta($testimonial->ID, 'author_position', true),
                        'author_image' => get_the_post_thumbnail_url($testimonial->ID, 'thumbnail')
                    ];
                }
            }

            $data['testimonials'] = $testimonials;
            break;

        case 'gallery':
            $gallery_ids = json_decode(get_post_meta($post->ID, 'module_gallery_ids', true), true) ?: [];

            $gallery = [];
            foreach ($gallery_ids as $id) {
                $image_url = wp_get_attachment_image_url($id, 'full');
                $image_medium = wp_get_attachment_image_url($id, 'medium');
                $image_thumbnail = wp_get_attachment_image_url($id, 'thumbnail');
                $attachment = get_post($id);

                if ($image_url) {
                    $gallery[] = [
                        'id' => $id,
                        'url' => $image_url,
                        'medium' => $image_medium,
                        'thumbnail' => $image_thumbnail,
                        'alt' => get_post_meta($id, '_wp_attachment_image_alt', true),
                        'title' => $attachment ? $attachment->post_title : '',
                        'caption' => $attachment ? $attachment->post_excerpt : ''
                    ];
                }
            }

            $data['gallery'] = $gallery;
            break;

        case 'faq':
            $data['faq_items'] = json_decode(get_post_meta($post->ID, 'module_faq_items', true), true);
            break;

        case 'tabbed_content':
            $data['tabbed_content'] = json_decode(get_post_meta($post->ID, 'module_tabbed_content', true), true);
            break;

        case 'charts':
            $data['chart_type'] = get_post_meta($post->ID, 'module_chart_type', true);
            $data['chart_data'] = json_decode(get_post_meta($post->ID, 'module_chart_data', true), true);
            break;

        case 'sharing':
            $data['sharing_networks'] = json_decode(get_post_meta($post->ID, 'module_sharing_networks', true), true);
            break;

        case 'login':
            $data['login_settings'] = json_decode(get_post_meta($post->ID, 'module_login_settings', true), true);
            break;

        case 'payment':
            $data['payment_settings'] = json_decode(get_post_meta($post->ID, 'module_payment_settings', true), true);
            break;

        case 'calendar':
            $data['calendar_settings'] = json_decode(get_post_meta($post->ID, 'module_calendar_settings', true), true);
            break;

        case 'video':
            $data['video_url'] = get_post_meta($post->ID, 'module_video_url', true);
            break;

        case 'form':
            $form_id = get_post_meta($post->ID, 'module_form_id', true);
            $data['form_id'] = $form_id;

            // Get form shortcode if using Contact Form 7
            if (class_exists('WPCF7_ContactForm') && $form_id) {
                $form = wpcf7_contact_form($form_id);
                if ($form) {
                    $data['form_title'] = $form->title();
                    $data['form_shortcode'] = '[contact-form-7 id="' . $form_id . '" title="' . esc_attr($form->title()) . '"]';
                }
            }
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

                foreach ($page_modules as $module_data) {
                    $module_id = $module_data['id'];
                    $module_post = get_post($module_id);

                    if ($module_post && $module_post->post_status === 'publish') {
                        $module = prepare_module_for_response($module_post);

                        // Override settings if needed
                        if (isset($module_data['override_settings']) && $module_data['override_settings']) {
                            if (isset($module_data['layout'])) {
                                $module['layout'] = $module_data['layout'];
                            }

                            if (isset($module_data['full_width'])) {
                                $module['full_width'] = $module_data['full_width'];
                            }

                            if (isset($module_data['background_color'])) {
                                $module['background_color'] = $module_data['background_color'];
                            }
                        }

                        $modules_data[] = $module;
                    }
                }

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
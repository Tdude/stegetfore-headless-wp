<?php
/**
* inc/meta-fields/register-meta.php
*/

 // Content type Porrtofolio if we need it
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
        'templates/evaluation.php' => 'Obsverktyget (Evaluation)',
        'templates/circle-chart.php' => 'Cirkeldiagram (Circle Chart)',
        'templates/homepage.php' => 'Startsidan (Specialinlägg)'
    ];
    return array_merge($templates, $custom_templates);
}
add_filter('theme_page_templates', 'register_custom_templates');

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

// Register component specific fields in the theme
function register_homepage_meta_fields() {
    // Hero Section
    register_post_meta('page', 'hero_title', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ]);

    register_post_meta('page', 'hero_intro', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ]);

    register_post_meta('page', 'hero_image_id', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'integer',
    ]);

    // For CTA buttons and other repeatable fields, store as JSON
    register_post_meta('page', 'hero_cta_buttons', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string', // Will store JSON string
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    // Selleri
    register_post_meta('page', 'selling_points', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    // Testimonials
    register_post_meta('page', 'testimonials', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
}
add_action('init', 'register_homepage_meta_fields');
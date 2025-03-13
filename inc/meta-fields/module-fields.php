<?php
/**
 * inc/meta-fields/module-fields.php
 * Register meta fields for modules
 */
function register_module_meta_fields()
{
    // Core fields for all modules
    register_post_meta('module', 'module_template', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_layout', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => 'center',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_full_width', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
        'default' => false,
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_background_color', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    // Button fields
    register_post_meta('module', 'module_buttons', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    // Template-specific fields
    register_post_meta('module', 'module_hero_settings', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_selling_points', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_stats', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_testimonials_ids', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_gallery_ids', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_faq_items', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_tabbed_content', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_chart_data', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_chart_type', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => 'bar',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_sharing_networks', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_login_settings', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string', // JSON object
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_payment_settings', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string', // JSON object
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_calendar_settings', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string', // JSON object
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_video_url', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_form_id', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_post_meta('module', 'module_featured_posts_settings', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string', // JSON object
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);
}
add_action('init', 'register_module_meta_fields');
<?php
/**
 * inc/meta-fields/register-meta.php
 *
 * */
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

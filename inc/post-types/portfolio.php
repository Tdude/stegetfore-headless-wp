<?php
/*
 * inc/post/types/portfolio.php
 * */
function register_portfolio_post_type() {
    $labels = [
        'name' => 'Portfolio',
        'singular_name' => 'Portfolio Item',
        'menu_name' => 'Portfolio',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Portfolio Item',
        'edit_item' => 'Edit Portfolio Item',
        'new_item' => 'New Portfolio Item',
        'view_item' => 'View Portfolio Item',
        'search_items' => 'Search Portfolio',
        'not_found' => 'No portfolio items found',
        'not_found_in_trash' => 'No portfolio items found in trash'
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'show_in_rest' => true,
        'supports' => [
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'custom-fields',
            'revisions'
        ],
        'has_archive' => true,
        'rewrite' => ['slug' => 'portfolio'],
        'menu_icon' => 'dashicons-portfolio',
        'show_in_graphql' => true, // If using WPGraphQL
        'taxonomies' => ['category', 'post_tag'],
        'menu_position' => 5
    ];

    register_post_type('portfolio', $args);
}
add_action('init', 'register_portfolio_post_type');

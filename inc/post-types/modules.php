<?php
 /**
  * inc/post-types/modules.php
  * Main module loader
  * Registers the Modules custom post type and associated taxonomies, meta fields,
  * and admin UI components for the headless WordPress theme.
  */

 if (!defined('ABSPATH')) exit;

// Load module components
$module_components = [
    '/meta-fields/module-fields.php',
    '/admin/module-ui.php',
    '/admin/module-page-integration.php',
    '/admin/module-enhancements.php',
    '/rest/module-endpoints.php'
];

foreach ($module_components as $component) {
    require_once get_template_directory() . '/inc' . $component;
}



 /**
  * Register the Modules custom post type
  */
 function register_modules_post_type() {
     $labels = [
         'name'               => _x('Modules', 'post type general name', 'steget'),
         'singular_name'      => _x('Module', 'post type singular name', 'steget'),
         'menu_name'          => _x('Modules', 'admin menu', 'steget'),
         'name_admin_bar'     => _x('Module', 'add new on admin bar', 'steget'),
         'add_new'            => _x('Add New', 'module', 'steget'),
         'add_new_item'       => __('Add New Module', 'steget'),
         'new_item'           => __('New Module', 'steget'),
         'edit_item'          => __('Edit Module', 'steget'),
         'view_item'          => __('View Module', 'steget'),
         'all_items'          => __('All Modules', 'steget'),
         'search_items'       => __('Search Modules', 'steget'),
         'parent_item_colon'  => __('Parent Modules:', 'steget'),
         'not_found'          => __('No modules found.', 'steget'),
         'not_found_in_trash' => __('No modules found in Trash.', 'steget')
     ];

     $args = [
         'labels'             => $labels,
         'description'        => __('Reusable content modules for the headless theme', 'steget'),
         'public'             => true,
         'publicly_queryable' => true,
         'show_ui'            => true,
         'show_in_menu'       => true,
         'query_var'          => true,
         'rewrite'            => ['slug' => 'module'],
         'capability_type'    => 'post',
         'has_archive'        => false,
         'hierarchical'       => false,
         'menu_position'      => 20,
         'menu_icon'          => 'dashicons-layout',
         'show_in_rest'       => true,
         'rest_base'          => 'modules',
         'supports'           => [
             'title',
             'editor',
             'thumbnail',
             'excerpt',
             'custom-fields',
             'revisions'
         ]
     ];

     register_post_type('module', $args);
 }
 add_action('init', 'register_modules_post_type');

 /**
  * Register Module Categories taxonomy
  */
 function register_module_taxonomies() {
     // Category taxonomy
     $category_labels = [
         'name'              => _x('Module Categories', 'taxonomy general name', 'steget'),
         'singular_name'     => _x('Module Category', 'taxonomy singular name', 'steget'),
         'search_items'      => __('Search Module Categories', 'steget'),
         'all_items'         => __('All Module Categories', 'steget'),
         'parent_item'       => __('Parent Module Category', 'steget'),
         'parent_item_colon' => __('Parent Module Category:', 'steget'),
         'edit_item'         => __('Edit Module Category', 'steget'),
         'update_item'       => __('Update Module Category', 'steget'),
         'add_new_item'      => __('Add New Module Category', 'steget'),
         'new_item_name'     => __('New Module Category Name', 'steget'),
         'menu_name'         => __('ModCats', 'steget'),
     ];

     $category_args = [
         'hierarchical'      => true,
         'labels'            => $category_labels,
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'show_in_rest'      => true,
         'rest_base'         => 'module-categories',
         'rewrite'           => ['slug' => 'module-category'],
     ];

     register_taxonomy('module_category', ['module'], $category_args);

     // Module Placement taxonomy (for site sections)
     $placement_labels = [
         'name'              => _x('Placements', 'taxonomy general name', 'steget'),
         'singular_name'     => _x('Placement', 'taxonomy singular name', 'steget'),
         'search_items'      => __('Search Placements', 'steget'),
         'all_items'         => __('All Placements', 'steget'),
         'edit_item'         => __('Edit Placement', 'steget'),
         'update_item'       => __('Update Placement', 'steget'),
         'add_new_item'      => __('Add New Placement', 'steget'),
         'new_item_name'     => __('New Placement Name', 'steget'),
         'menu_name'         => __('Placements', 'steget'),
     ];

     $placement_args = [
         'hierarchical'      => false,
         'labels'            => $placement_labels,
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'show_in_rest'      => true,
         'rest_base'         => 'module-placements',
         'rewrite'           => ['slug' => 'module-placement'],
     ];

     register_taxonomy('module_placement', ['module'], $placement_args);
 }
 add_action('init', 'register_module_taxonomies');

 /**
  * Define available module templates
  */
 function get_module_templates() {
     return [
         'hero'             => __('Hero Banner', 'steget'),
         'selling_points'   => __('Selling Points', 'steget'),
         'stats'            => __('Statistics', 'steget'),
         'testimonials'     => __('Testimonials', 'steget'),
         'gallery'          => __('Image Gallery', 'steget'),
         'faq'              => __('FAQ Accordion', 'steget'),
         'tabbed_content'   => __('Tabbed Content', 'steget'),
         'charts'           => __('Data Charts', 'steget'),
         'sharing'          => __('Social Sharing', 'steget'),
         'login'            => __('Login Form', 'steget'),
         'payment'          => __('Payment Form', 'steget'),
         'calendar'         => __('Calendar', 'steget'),
         'cta'              => __('Call to Action', 'steget'),
         'text_media'       => __('Text with Media', 'steget'),
         'video'            => __('Video', 'steget'),
         'form'             => __('Contact Form', 'steget')
     ];
 }

 /**
  * Define button styles
  */
 function get_button_styles() {
     return [
         'primary'   => __('Primary', 'steget'),
         'secondary' => __('Secondary', 'steget'),
         'default'   => __('Default', 'steget'),
         'ghost'     => __('Ghost', 'steget'),
         'link'      => __('Link', 'steget')
     ];
 }

 /**
  * Define layout options
  */
 function get_layout_options() {
     return [
         'left'     => __('Left Aligned', 'steget'),
         'center'   => __('Centered', 'steget'),
         'right'    => __('Right Aligned', 'steget')
     ];
 }
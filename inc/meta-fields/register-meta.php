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



// Add a meta box for template selection
function add_template_choice_meta_box() {
    add_meta_box(
        'template_choice_meta_box', // Meta box ID
        'Template Choice',         // Meta box title
        'render_template_choice_meta_box', // Callback to render the meta box
        'page',                    // Post type (pages in this case)
        'side',                    // Context (side, normal, advanced)
        'high'                     // Priority
    );
}
add_action('add_meta_boxes', 'add_template_choice_meta_box');

// Render the meta box content
function render_template_choice_meta_box($post) {
    // Retrieve the current template choice (if any)
    $template_choice = get_post_meta($post->ID, '_template_choice', true);

    // Define available template choices
    $templates = array(
        'default' => 'Normalsida',
        'full-width' => 'Fullbredd',
        'sidebar' => 'Sida med sidebar',
        'evaluation' => 'Sida med obschema',
        'circle-chart' => 'Sida med livshjul',
    );

    // Add a nonce field for security
    wp_nonce_field('template_choice_nonce', 'template_choice_nonce');

    // Render the dropdown
    echo '<label for="template_choice">Select Template:</label>';
    echo '<select name="template_choice" id="template_choice">';
    foreach ($templates as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($template_choice, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

// Save the template choice when the page is saved
function save_template_choice($post_id) {
    // Check nonce and user permissions
    if (!isset($_POST['template_choice_nonce']) || !wp_verify_nonce($_POST['template_choice_nonce'], 'template_choice_nonce')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save the template choice
    if (isset($_POST['template_choice'])) {
        update_post_meta($post_id, '_template_choice', sanitize_text_field($_POST['template_choice']));
    }
}
add_action('save_post', 'save_template_choice');
<?php
/**
 * Content Display Meta Fields
 * 
 * Handles UI components for controlling whether to hide/show the main content 
 * when modules are present on a page.
 * 
 * Note: Registration of meta fields is handled in register-meta.php
 */

// Add meta box to control content display
function add_content_display_meta_box() {
    add_meta_box(
        'content-display-meta-box',
        'Content Display Options',
        'render_content_display_meta_box',
        ['page', 'post', 'ham_assessment'],
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_content_display_meta_box');

// Render the meta box content
function render_content_display_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('content_display_meta_box', 'content_display_meta_box_nonce');
    
    // Get current value
    $value = get_post_meta($post->ID, 'show_content_with_modules', true);
    $position = get_post_meta($post->ID, 'content_position', true);
    if (empty($position)) {
        $position = 'after'; // Default to after if not set
    }
    
    ?>
    <p>
        <label>
            <input type="checkbox" name="show_content_with_modules" value="1" <?php checked($value, '1'); ?> />
            Show content with modules
        </label>
    </p>
    <p class="description">
        Content is hidden by default when modules are present.
    </p>
    
    <p>
        <label for="content_position">Content position:</label>
        <select name="content_position" id="content_position">
            <option value="before" <?php selected($position, 'before'); ?>>Before modules</option>
            <option value="after" <?php selected($position, 'after'); ?>>After modules</option>
        </select>
    </p>
    <?php
}

// Save the meta box value
function save_content_display_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['content_display_meta_box_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['content_display_meta_box_nonce'], 'content_display_meta_box')) {
        return;
    }
    
    // Don't save during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if ('page' === $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    
    // Save the value
    if (isset($_POST['show_content_with_modules'])) {
        update_post_meta($post_id, 'show_content_with_modules', '1');
    } else {
        delete_post_meta($post_id, 'show_content_with_modules');
    }
    
    // Save content position
    if (isset($_POST['content_position'])) {
        update_post_meta($post_id, 'content_position', $_POST['content_position']);
    } else {
        delete_post_meta($post_id, 'content_position');
    }
}
add_action('save_post', 'save_content_display_meta_box');

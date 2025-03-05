<?php
/**
 * inc/features/cta.php
*/

function register_cta_metabox() {
    add_meta_box(
        'cta_metabox',
        'Call to Action Section',
        'render_cta_metabox',
        'page',
        'normal',
        'default',
        ['__back_compat_meta_box' => true]
    );
}

function register_cta_fields() {
    register_meta('post', 'cta_title', [
        'type' => 'string',
        'description' => 'CTA section title',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'cta_description', [
        'type' => 'string',
        'description' => 'CTA description text',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'cta_button_text', [
        'type' => 'string',
        'description' => 'CTA button text',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'cta_button_url', [
        'type' => 'string',
        'description' => 'CTA button URL',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'cta_background_color', [
        'type' => 'string',
        'description' => 'CTA background color',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}

function render_cta_metabox($post) {
    wp_nonce_field('save_cta_meta', 'cta_meta_nonce');

    $cta_title = get_post_meta($post->ID, 'cta_title', true);
    $cta_description = get_post_meta($post->ID, 'cta_description', true);
    $cta_button_text = get_post_meta($post->ID, 'cta_button_text', true);
    $cta_button_url = get_post_meta($post->ID, 'cta_button_url', true);
    $cta_background_color = get_post_meta($post->ID, 'cta_background_color', true) ?: 'bg-primary';

    ?>
<div class="metabox-container">
    <p>
        <label for="cta_title">CTA Title:</label>
        <input type="text" id="cta_title" name="cta_title" value="<?php echo esc_attr($cta_title); ?>" class="widefat">
    </p>
    <p>
        <label for="cta_description">CTA Description:</label>
        <textarea id="cta_description" name="cta_description"
            class="widefat"><?php echo esc_textarea($cta_description); ?></textarea>
    </p>
    <p>
        <label for="cta_button_text">Button Text:</label>
        <input type="text" id="cta_button_text" name="cta_button_text" value="<?php echo esc_attr($cta_button_text); ?>"
            class="widefat">
    </p>
    <p>
        <label for="cta_button_url">Button URL:</label>
        <input type="text" id="cta_button_url" name="cta_button_url" value="<?php echo esc_attr($cta_button_url); ?>"
            class="widefat">
    </p>
    <p>
        <label for="cta_background_color">Background Color:</label>
        <select id="cta_background_color" name="cta_background_color" class="widefat">
            <option value="bg-primary" <?php selected($cta_background_color, 'bg-primary'); ?>>Primary</option>
            <option value="bg-secondary" <?php selected($cta_background_color, 'bg-secondary'); ?>>Secondary</option>
            <option value="bg-dark" <?php selected($cta_background_color, 'bg-dark'); ?>>Dark</option>
            <option value="bg-light" <?php selected($cta_background_color, 'bg-light'); ?>>Light</option>
        </select>
    </p>
</div>
<?php
}

add_action('add_meta_boxes', 'register_cta_metabox');

function save_cta_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['cta_meta_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['cta_meta_nonce'], 'save_cta_meta')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save CTA fields
    if (isset($_POST['cta_title'])) {
        update_post_meta($post_id, 'cta_title', sanitize_text_field($_POST['cta_title']));
    }

    if (isset($_POST['cta_description'])) {
        update_post_meta($post_id, 'cta_description', sanitize_textarea_field($_POST['cta_description']));
    }

    if (isset($_POST['cta_button_text'])) {
        update_post_meta($post_id, 'cta_button_text', sanitize_text_field($_POST['cta_button_text']));
    }

    if (isset($_POST['cta_button_url'])) {
        update_post_meta($post_id, 'cta_button_url', esc_url_raw($_POST['cta_button_url']));
    }

    if (isset($_POST['cta_background_color'])) {
        update_post_meta($post_id, 'cta_background_color', sanitize_text_field($_POST['cta_background_color']));
    }
}

add_action('save_post', 'save_cta_meta');
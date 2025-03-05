<?php
/**
 * inc/features/hero.php
*/

function register_hero_metabox() {
    add_meta_box(
        'hero_metabox',
        'Hero Section',
        'render_hero_metabox',
        'page',
        'normal',
        'high',
        ['__back_compat_meta_box' => true]
    );
}

add_action('add_meta_boxes', 'register_hero_metabox');

function register_hero_fields() {
    register_meta('post', 'hero_title', [
        'type' => 'string',
        'description' => 'Hero title text',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'hero_intro', [
        'type' => 'string',
        'description' => 'Hero introduction text',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'hero_image_id', [
        'type' => 'integer',
        'description' => 'Hero image ID',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'hero_cta_buttons', [
        'type' => 'string',
        'description' => 'Hero CTA buttons (JSON)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}

function render_hero_metabox($post) {
    // Only show for homepage
    if ($post->ID != get_option('page_on_front')) {
        echo '<p>These settings only apply to the homepage.</p>';
        return;
    }

    $hero_title = get_post_meta($post->ID, 'hero_title', true);
    $hero_intro = get_post_meta($post->ID, 'hero_intro', true);
    $hero_image_id = get_post_meta($post->ID, 'hero_image_id', true);
    $cta_buttons = json_decode(get_post_meta($post->ID, 'hero_cta_buttons', true), true) ?: [];

    // Render form fields
    ?>
<div class="hero-section-fields">
    <p>
        <label for="hero_title">Hero Title:</label>
        <input type="text" id="hero_title" name="hero_title" value="<?php echo esc_attr($hero_title); ?>"
            class="widefat">
    </p>
    <p>
        <label for="hero_intro">Hero Intro:</label>
        <textarea id="hero_intro" name="hero_intro" class="widefat"><?php echo esc_textarea($hero_intro); ?></textarea>
    </p>
    <p>
        <label for="hero_image_id">Hero Image:</label>
        <input type="hidden" id="hero_image_id" name="hero_image_id" value="<?php echo esc_attr($hero_image_id); ?>">
        <button class="button button-primary upload-hero-image">Upload Image</button>
        <?php if ($hero_image_id) : ?>
        <img src="<?php echo wp_get_attachment_url($hero_image_id); ?>" alt="Hero Image"
            style="max-width: 100px; margin-top: 10px;">
        <?php endif; ?>
    </p>
    <h3>CTA Buttons</h3>
    <div id="cta_buttons_wrapper">
        <?php if (!empty($cta_buttons)) : ?>
        <?php foreach ($cta_buttons as $index => $button) : ?>
        <div class="cta-button-field">
            <input type="text" name="cta_button_text[]" value="<?php echo esc_attr($button['text']); ?>"
                placeholder="Button Text">
            <input type="url" name="cta_button_url[]" value="<?php echo esc_url($button['url']); ?>"
                placeholder="Button URL">
            <select name="cta_button_style[]">
                <option value="primary" <?php selected($button['style'], 'primary'); ?>>Primary</option>
                <option value="secondary" <?php selected($button['style'], 'secondary'); ?>>Secondary</option>
            </select>
            <button class="button button-danger remove-cta-button">Remove</button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <button class="button button-primary add-cta-button">Add Button</button>
    </div>
</div>
<?php
    // Add nonce for security
    wp_nonce_field('save_homepage_meta', 'homepage_meta_nonce');
}

function save_homepage_meta($post_id) {
    // Security checks, permissions, etc.
    if (!isset($_POST['homepage_meta_nonce']) || !wp_verify_nonce($_POST['homepage_meta_nonce'], 'save_homepage_meta')) {
        return;
    }

    if (isset($_POST['hero_title'])) {
        update_post_meta($post_id, 'hero_title', sanitize_text_field($_POST['hero_title']));
    }

    if (isset($_POST['hero_intro'])) {
        update_post_meta($post_id, 'hero_intro', sanitize_textarea_field($_POST['hero_intro']));
    }

    if (isset($_POST['hero_image_id'])) {
        update_post_meta($post_id, 'hero_image_id', sanitize_text_field($_POST['hero_image_id']));
    }

    // Handle repeatable fields (CTA buttons)
    if (isset($_POST['cta_button_text']) && is_array($_POST['cta_button_text'])) {
        $buttons = [];
        for ($i = 0; $i < count($_POST['cta_button_text']); $i++) {
            if (!empty($_POST['cta_button_text'][$i])) {
                $buttons[] = [
                    'text' => sanitize_text_field($_POST['cta_button_text'][$i]),
                    'url' => esc_url_raw($_POST['cta_button_url'][$i]),
                    'style' => sanitize_text_field($_POST['cta_button_style'][$i]),
                ];
            }
        }
        update_post_meta($post_id, 'hero_cta_buttons', json_encode($buttons));
    }
}
add_action('save_post', 'save_homepage_meta');
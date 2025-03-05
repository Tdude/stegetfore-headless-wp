<?php
/**
 * inc/features/testimonials.php
*/

function register_testimonials_metabox() {
    add_meta_box(
        'testimonials_metabox',
        'Testimonials Section',
        'render_testimonials_metabox',
        'page',
        'normal',
        'default',
        ['__back_compat_meta_box' => true]
    );
}

function register_testimonials_fields() {
    register_meta('post', 'testimonials_title', [
        'type' => 'string',
        'description' => 'Testimonials section title',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'featured_testimonials', [
        'type' => 'string',
        'description' => 'Featured testimonial IDs (JSON array)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}

function render_testimonials_metabox($post) {
    wp_nonce_field('save_testimonials_meta', 'testimonials_meta_nonce');

    $testimonials_title = get_post_meta($post->ID, 'testimonials_title', true);
    $featured_testimonials = get_post_meta($post->ID, 'featured_testimonials', true);
    $featured_testimonials_array = $featured_testimonials ? json_decode($featured_testimonials, true) : [];

    // Get all testimonials for selection
    $all_testimonials = get_posts([
        'post_type' => 'testimonial',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);

    ?>
<div class="metabox-container">
    <p>
        <label for="testimonials_title">Section Title:</label>
        <input type="text" id="testimonials_title" name="testimonials_title"
            value="<?php echo esc_attr($testimonials_title); ?>" class="widefat">
    </p>

    <div class="testimonials-selection">
        <h4>Select Featured Testimonials</h4>
        <select id="testimonials-select" name="featured_testimonials[]" multiple
            style="width: 100%; min-height: 150px;">
            <?php
                foreach ($all_testimonials as $testimonial) {
                    $selected = in_array($testimonial->ID, $featured_testimonials_array) ? 'selected' : '';
                    echo '<option value="' . esc_attr($testimonial->ID) . '" ' . $selected . '>' . esc_html($testimonial->post_title) . '</option>';
                }
                ?>
        </select>
        <p class="description">Hold Ctrl/Cmd to select multiple testimonials.</p>
    </div>
</div>
<?php
}

add_action('add_meta_boxes', 'register_testimonials_metabox');

function save_testimonials_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['testimonials_meta_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['testimonials_meta_nonce'], 'save_testimonials_meta')) {
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

    // Save title
    if (isset($_POST['testimonials_title'])) {
        update_post_meta($post_id, 'testimonials_title', sanitize_text_field($_POST['testimonials_title']));
    }

    // Save featured testimonials
    if (isset($_POST['featured_testimonials']) && is_array($_POST['featured_testimonials'])) {
        $testimonial_ids = array_map('intval', $_POST['featured_testimonials']);
        update_post_meta($post_id, 'featured_testimonials', json_encode($testimonial_ids));
    } else {
        update_post_meta($post_id, 'featured_testimonials', json_encode([]));
    }
}

add_action('save_post', 'save_testimonials_meta');
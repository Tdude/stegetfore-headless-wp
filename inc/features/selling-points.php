<?php
/**
 * inc/features/selling-points.php
*/

function register_selling_points_metabox() {
    add_meta_box(
        'selling_points_metabox',
        'Selling Points Section',
        'render_selling_points_metabox',
        'page',
        'normal',
        'default',
        ['__back_compat_meta_box' => true]
    );
}

function register_selling_points_fields() {
    register_meta('post', 'selling_points_title', [
        'type' => 'string',
        'description' => 'Selling Points section title',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'selling_points', [
        'type' => 'string',
        'description' => 'Selling points (JSON array)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}

function render_selling_points_metabox($post) {
    wp_nonce_field('save_selling_points_meta', 'selling_points_meta_nonce');

    $selling_points_title = get_post_meta($post->ID, 'selling_points_title', true);
    $selling_points = get_post_meta($post->ID, 'selling_points', true);
    $selling_points_array = $selling_points ? json_decode($selling_points, true) : [];

    // Output HTML for the metabox
    ?>
<div class="metabox-container">
    <p>
        <label for="selling_points_title">Section Title:</label>
        <input type="text" id="selling_points_title" name="selling_points_title"
            value="<?php echo esc_attr($selling_points_title); ?>" class="widefat">
    </p>

    <div class="selling-points-container">
        <h4>Selling Points</h4>
        <div id="selling-points-list">
            <?php
                if (!empty($selling_points_array)) {
                    foreach ($selling_points_array as $index => $point) {
                        ?>
            <div class="selling-point-item">
                <p>
                    <label>Title:</label>
                    <input type="text" name="selling_point_title[]" value="<?php echo esc_attr($point['title']); ?>"
                        class="widefat">
                </p>
                <p>
                    <label>Description:</label>
                    <textarea name="selling_point_description[]"
                        class="widefat"><?php echo esc_textarea($point['description']); ?></textarea>
                </p>
                <p>
                    <label>Icon:</label>
                    <input type="text" name="selling_point_icon[]" value="<?php echo esc_attr($point['icon']); ?>"
                        class="widefat">
                </p>
                <button type="button" class="button remove-selling-point">Remove</button>
            </div>
            <?php
                    }
                }
                ?>
        </div>
        <button type="button" id="add-selling-point" class="button">Add Selling Point</button>
    </div>
</div>
<script>
// Add JS for the repeatable fields (similar to the admin.js)
jQuery(document).ready(function($) {
    // Add new selling point
    $('#add-selling-point').on('click', function() {
        var html = '<div class="selling-point-item">' +
            '<p><label>Title:</label><input type="text" name="selling_point_title[]" class="widefat"></p>' +
            '<p><label>Description:</label><textarea name="selling_point_description[]" class="widefat"></textarea></p>' +
            '<p><label>Icon:</label><input type="text" name="selling_point_icon[]" class="widefat"></p>' +
            '<button type="button" class="button remove-selling-point">Remove</button>' +
            '</div>';
        $('#selling-points-list').append(html);
    });

    // Remove selling point
    $(document).on('click', '.remove-selling-point', function() {
        $(this).closest('.selling-point-item').remove();
    });
});
</script>
<?php
}

add_action('add_meta_boxes', 'register_selling_points_metabox');


function save_selling_points_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['selling_points_meta_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['selling_points_meta_nonce'], 'save_selling_points_meta')) {
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
    if (isset($_POST['selling_points_title'])) {
        update_post_meta($post_id, 'selling_points_title', sanitize_text_field($_POST['selling_points_title']));
    }

    // Save selling points
    if (isset($_POST['selling_point_title']) && is_array($_POST['selling_point_title'])) {
        $selling_points = [];

        for ($i = 0; $i < count($_POST['selling_point_title']); $i++) {
            if (!empty($_POST['selling_point_title'][$i])) {
                $selling_points[] = [
                    'title' => sanitize_text_field($_POST['selling_point_title'][$i]),
                    'description' => sanitize_textarea_field($_POST['selling_point_description'][$i]),
                    'icon' => sanitize_text_field($_POST['selling_point_icon'][$i])
                ];
            }
        }

        update_post_meta($post_id, 'selling_points', json_encode($selling_points));
    }
}

add_action('save_post', 'save_selling_points_meta');
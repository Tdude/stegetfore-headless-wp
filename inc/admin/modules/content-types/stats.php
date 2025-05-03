<?php
/**
 * Module: Stats
 * Description: Handles the Stats section as a module (refactored from admin/features/stats.php)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Stats module fields for pages
 */
function steget_register_stats_module_fields() {
    register_meta('post', 'stats_title', [
        'type' => 'string',
        'description' => 'Stats section title',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'stats_subtitle', [
        'type' => 'string',
        'description' => 'Stats section subtitle',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'stats_bg_color', [
        'type' => 'string',
        'description' => 'Stats section background color',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);

    register_meta('post', 'stats_items', [
        'type' => 'string',
        'description' => 'Stats items (JSON array)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'steget_sanitize_stats_items',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);
}
add_action('init', 'steget_register_stats_module_fields');

/**
 * Sanitize stats items array
 */
function steget_sanitize_stats_items($input) {
    if (!is_array($input)) {
        $input = json_decode($input, true);
    }
    if (!is_array($input)) {
        return array();
    }
    $sanitized_input = array();
    foreach ($input as $item) {
        if (empty($item['id'])) {
            continue;
        }
        $sanitized_item = array(
            'id' => absint($item['id']),
            'value' => sanitize_text_field($item['value'] ?? ''),
            'label' => sanitize_text_field($item['label'] ?? ''),
            'icon' => sanitize_text_field($item['icon'] ?? '')
        );
        $sanitized_input[] = $sanitized_item;
    }
    return $sanitized_input;
}

/**
 * Render Stats module fields in admin UI (metabox or module fields)
 */
function render_stats_module_fields($post) {
    $stats_title = get_post_meta($post->ID, 'stats_title', true);
    $stats_subtitle = get_post_meta($post->ID, 'stats_subtitle', true);
    $stats_bg_color = get_post_meta($post->ID, 'stats_bg_color', true);
    $stats_items = get_post_meta($post->ID, 'stats_items', true);
    $stats_items_array = $stats_items ? json_decode($stats_items, true) : [];
    ?>
    <div class="stats-module-fields">
        <p>
            <label for="stats_title"><strong><?php _e('Stats Title', 'steget'); ?>:</strong></label><br>
            <input type="text" id="stats_title" name="stats_title" value="<?php echo esc_attr($stats_title); ?>" class="widefat" />
        </p>
        <p>
            <label for="stats_subtitle"><strong><?php _e('Stats Subtitle', 'steget'); ?>:</strong></label><br>
            <input type="text" id="stats_subtitle" name="stats_subtitle" value="<?php echo esc_attr($stats_subtitle); ?>" class="widefat" />
        </p>
        <p>
            <label for="stats_bg_color"><strong><?php _e('Background Color', 'steget'); ?>:</strong></label><br>
            <input type="text" id="stats_bg_color" name="stats_bg_color" value="<?php echo esc_attr($stats_bg_color); ?>" class="widefat" />
        </p>
        <p>
            <label><strong><?php _e('Stats Items', 'steget'); ?>:</strong></label><br>
            <textarea name="stats_items" rows="5" class="widefat"><?php echo esc_textarea($stats_items); ?></textarea>
            <small><?php _e('Enter as JSON array: [{"id":1,"value":"100","label":"Points","icon":"star"}, ...]', 'steget'); ?></small>
        </p>
    </div>
    <?php
}
// You may need to hook render_stats_module_fields to your admin UI or module system as needed.

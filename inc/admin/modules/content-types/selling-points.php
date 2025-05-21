<?php
/**
 * Selling Points module template fields
 *
 * @package Steget
 */

function render_selling_points_template_fields($post) {
    // Load existing data from saved meta
    $settings = json_decode(get_post_meta($post->ID, 'module_selling_points', true), true);
    if (!is_array($settings)) {
        $settings = [];
    }
    // Defaults
    $settings['layout'] = $settings['layout'] ?? '';
    $settings['points_per_row'] = $settings['points_per_row'] ?? 3;
    $settings['points'] = $settings['points'] ?? [];
    // Note: The module title/content comes from the WP post title/content, not from meta fields.
    // The admin UI does not render or save any custom title/content fields for the module header.
    // This makes admin and frontend titles always match.
    // --- Render icon select template for JS ---
    require_once dirname(__DIR__, 2) . '/lucide-icons.php';
    $icon_options = [
        'briefcase' => 'Briefcase',
        'star' => 'Star',
        'check' => 'Check',
        'award' => 'Award',
        'bug' => 'Bug',
        'chart-column-decreasing' => 'Chart Column Decreasing',
        'chart-column-increasing' => 'Chart Column Increasing',
        'library' => 'Library',
        'mic' => 'Mic',
        'facebook' => 'Facebook',
        'github' => 'Github',
        'graduation-cap' => 'Graduation Cap',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'trending-down' => 'Trending Down',
        'trending-up' => 'Trending Up',
        'twitter' => 'Twitter',
        'youtube' => 'Youtube',
        'folder' => 'Folder',
    ];
    $icons_dir = ABSPATH . 'wp-content/themes/stegetfore-wp-frontend/public/images/icons/';
    $svg_files = glob($icons_dir . '*.svg');
    $svg_options = [];
    if ($svg_files) {
        foreach ($svg_files as $svg_path) {
            $svg_file = basename($svg_path);
            $svg_options[$svg_file] = ucfirst(str_replace(['-', '_', '.svg'], [' ', ' ', ''], $svg_file));
        }
    }
    ?>
    <div id="selling_points_fields" class="template-fields">
        <p>
            <label><strong><?php _e('Layout', 'steget'); ?>:</strong></label><br>
            <select name="selling_points_layout" class="widefat">
                <option value="left" <?php selected($settings['layout'], 'left'); ?>><?php _e('Left', 'steget'); ?></option>
                <option value="center" <?php selected($settings['layout'], 'center'); ?>><?php _e('Center', 'steget'); ?></option>
                <option value="right" <?php selected($settings['layout'], 'right'); ?>><?php _e('Right', 'steget'); ?></option>
            </select>
        </p>
        <p>
            <label><strong><?php _e('Points per row', 'steget'); ?>:</strong></label><br>
            <input type="number" name="selling_points_per_row" value="<?php echo esc_attr($settings['points_per_row']); ?>" class="small-text" min="1" max="6">
        </p>
        <!-- Hidden icon select template for JS -->
        <div id="selling-point-icon-select-template" style="display:none;">
            <select name="selling_point_icon[]" class="selling-point-icon-select">
                <?php foreach ($icon_options as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
                <?php foreach ($svg_options as $file => $label): ?>
                    <option value="<?php echo esc_attr($file); ?>"><?php echo esc_html($label); ?> (SVG)</option>
                <?php endforeach; ?>
            </select>
            <span class="selling-point-icon-preview" style="vertical-align:middle; margin-left:8px;"></span>
        </div>
        <div id="selling_points_container">
            <?php if (!empty($settings['points'])): ?>
                <?php foreach ($settings['points'] as $index => $point) : ?>
                    <div class="selling-point-item">
                        <h4><?php _e('Selling Point', 'steget'); ?> #<?php echo $index + 1; ?></h4>
                        <p>
                            <label><strong><?php _e('Title', 'steget'); ?>:</strong></label><br>
                            <input type="text" name="selling_point_title[]" value="<?php echo esc_attr($point['title']); ?>" class="widefat">
                        </p>
                        <p>
                            <label><strong><?php _e('Description', 'steget'); ?>:</strong></label><br>
                            <textarea name="selling_point_description[]" rows="3" class="widefat"><?php echo esc_textarea($point['description']); ?></textarea>
                        </p>
                        <p>
                            <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>
                            <select name="selling_point_icon[]" class="selling-point-icon-select">
                                <?php foreach ($icon_options as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($point['icon'], $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                                <?php foreach ($svg_options as $file => $label): ?>
                                    <option value="<?php echo esc_attr($file); ?>" <?php selected($point['icon'], $file); ?>><?php echo esc_html($label); ?> (SVG)</option>
                                <?php endforeach; ?>
                            </select>
                            <span class="selling-point-icon-preview" style="vertical-align:middle; margin-left:8px;">
                                <?php
                                $icon = $point['icon'];
                                $lucide_svg = class_exists('Lucide_Icons') ? Lucide_Icons::get($icon) : null;
                                if ($lucide_svg) {
                                    echo $lucide_svg;
                                } elseif ($icon && file_exists($icons_dir . (substr($icon, -4) === '.svg' ? $icon : $icon . '.svg'))) {
                                    $svg_file = $icons_dir . (substr($icon, -4) === '.svg' ? $icon : $icon . '.svg');
                                    echo file_get_contents($svg_file);
                                } elseif ($icon) {
                                    echo esc_html($icon);
                                }
                                ?>
                            </span>
                        </p>
                        <p>
                            <label><strong><?php _e('Color', 'steget'); ?>:</strong></label><br>
                            <input type="text" name="selling_point_color[]" value="<?php echo esc_attr($point['color']); ?>" class="widefat selling-point-color-picker">
                        </p>
                        <button type="button" class="button is-destructive steget-remove-selling-point"><?php _e('Remove', 'steget'); ?></button>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" class="button add-selling-point"><?php _e('Add Selling Point', 'steget'); ?></button>
    </div>
    <?php
}

function render_selling_points_module_meta_box($post) {
    $module_type = get_post_meta($post->ID, 'module_type', true);
    if ($module_type !== 'selling_points') {
        // No instructional message; just return silently
        return;
    }
    wp_nonce_field('save_selling_points_module', 'selling_points_module_nonce');
    render_selling_points_template_fields($post);
}

function add_selling_points_module_meta_box() {
    // Only add the meta box if this is a Selling Points module
    add_meta_box(
        'selling_points_module_meta_box',
        '', // Remove the title
        'render_selling_points_module_meta_box',
        'module',
        'normal',
        'high',
        ['module_type' => 'selling_points']
    );
}

// Always register the Selling Points meta box for all modules
add_action('add_meta_boxes_module', function($post) {
    add_selling_points_module_meta_box();
});

add_action('admin_enqueue_scripts', function($hook) {
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_script(
            'selling-points-admin',
            get_template_directory_uri() . '/inc/js/selling-points-admin.js',
            ['jquery'],
            filemtime(get_template_directory() . '/inc/js/selling-points-admin.js'),
            true
        );
        wp_localize_script('selling-points-admin', 'stegetSellingPointsAdmin', [
            'labels' => [
                'selling_point' => __('Selling Point', 'steget'),
                'title' => __('Title', 'steget'),
                'description' => __('Description', 'steget'),
                'icon' => __('Icon', 'steget'),
                'color' => __('Color', 'steget'),
                'remove' => __('Remove', 'steget'),
            ]
        ]);
    }
});

// --- AJAX handler for icon preview ---
add_action('wp_ajax_get_selling_point_icon_svg', function() {
    $icon = isset($_POST['icon']) ? sanitize_text_field($_POST['icon']) : '';
    $icons_dir = ABSPATH . 'wp-content/themes/stegetfore-wp-frontend/public/images/icons/';
    require_once dirname(__DIR__, 2) . '/lucide-icons.php';
    $lucide_svg = class_exists('Lucide_Icons') ? Lucide_Icons::get($icon) : null;
    if ($lucide_svg) {
        wp_send_json_success($lucide_svg);
    } elseif ($icon && file_exists($icons_dir . (substr($icon, -4) === '.svg' ? $icon : $icon . '.svg'))) {
        $svg_file = $icons_dir . (substr($icon, -4) === '.svg' ? $icon : $icon . '.svg');
        wp_send_json_success(file_get_contents($svg_file));
    } elseif ($icon) {
        wp_send_json_success(esc_html($icon));
    } else {
        wp_send_json_error('No icon');
    }
    wp_die();
});

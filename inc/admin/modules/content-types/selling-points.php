<?php
/**
 * Selling Points module template fields
 *
 * @package Steget
 */

/**
 * Render selling points template fields
 */
function render_selling_points_template_fields($post) {
    $selling_points = json_decode(get_post_meta($post->ID, 'module_selling_points', true), true) ?: [
        ['title' => '', 'description' => '', 'icon' => '', 'color' => '']
    ];
    ?>
    <div id="selling_points_fields" class="template-fields">
        <div id="selling_points_container">
            <?php foreach ($selling_points as $index => $point) : ?>
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
                        <?php
                        // --- Lucide icon options (for dropdown and preview) ---
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
                        ];
                        // --- SVG icon options from public/images/icons/ ---
                        $icons_dir = ABSPATH . 'wp-content/themes/stegetfore-wp-frontend/public/images/icons/';
                        $svg_files = glob($icons_dir . '*.svg');
                        $svg_options = [];
                        if ($svg_files) {
                            foreach ($svg_files as $svg_path) {
                                $svg_file = basename($svg_path);
                                $svg_options[$svg_file] = ucfirst(str_replace(['-', '_', '.svg'], [' ', ' ', ''], $svg_file));
                            }
                        }
                        // --- Lucide SVGs for preview only (not for dropdown) ---
                        $lucide_svgs = [
                            'briefcase' => '<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3v4M8 3v4"/></svg>',
                            'star' => '<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                            'check' => '<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>',
                            'award' => '<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
                            'bug' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bug-icon lucide-bug"><path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1a3.003 3.003 0 1 1 6 0v1"/><path d="M12 20c-3.3 0-6-2.7-6-6v-3a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v3c0 3.3-2.7 6-6 6"/><path d="M12 20v-9"/><path d="M6.53 9C4.6 8.8 3 7.1 3 5"/><path d="M6 13H2"/><path d="M3 21c0-2.1 1.7-3.9 3.8-4"/><path d="M20.97 5c0 2.1-1.6 3.8-3.5 4"/><path d="M22 13h-4"/><path d="M17.2 17c2.1.1 3.8 1.9 3.8 4"/></svg>',
                            'library' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-library-icon lucide-library"><path d="m16 6 4 14"/><path d="M12 6v14"/><path d="M8 8v12"/><path d="M4 4v16"/></svg>',
                            'mic' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic-icon lucide-mic"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>',
                            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-facebook-icon lucide-facebook"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
                            'github' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-github-icon lucide-github"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>',
                            'graduation-cap' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap-icon lucide-graduation-cap"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>',
                            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-instagram-icon lucide-instagram"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>',
                            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-linkedin-icon lucide-linkedin"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg>',
                            'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-twitter-icon lucide-twitter"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>',
                            'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-youtube-icon lucide-youtube"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>',
                            'shield' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-icon lucide-shield"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>',
                            'chart-column-decreasing' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column-decreasing-icon lucide-chart-column-decreasing"><path d="M13 17V9"/><path d="M18 17v-3"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17V5"/></svg>',
                            'chart-column-increasing' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column-increasing-icon lucide-chart-column-increasing"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>',
                            'trending-down' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-down-icon lucide-trending-down"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>',
                            'trending-up' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up-icon lucide-trending-up"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
                                                   ];
                        ?>
                        <select name="selling_point_icon[]" class="selling-point-icon-select">
                            <!-- Lucide options -->
                            <?php foreach ($icon_options as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($point['icon'], $key); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                            <!-- SVG file options -->
                            <?php foreach ($svg_options as $file => $label): ?>
                                <option value="<?php echo esc_attr($file); ?>" <?php selected($point['icon'], $file); ?>><?php echo esc_html($label); ?> (SVG)</option>
                            <?php endforeach; ?>
                        </select>
                        <span class="selling-point-icon-preview" style="vertical-align:middle; margin-left:8px;">
                            <?php
                            if (!empty($point['icon'])) {
                                $icon_key = $point['icon'];
                                // Only show preview if the icon exists in either Lucide or SVG options
                                if (isset($lucide_svgs[$icon_key])) {
                                    echo $lucide_svgs[$icon_key];
                                } elseif (isset($svg_options[$icon_key])) {
                                    $svg_path = $icons_dir . $icon_key;
                                    if (file_exists($svg_path)) {
                                        echo file_get_contents($svg_path);
                                    }
                                } else {
                                    // If the icon is not found, show nothing or a fallback
                                    // echo $lucide_svgs['briefcase']; // Uncomment for fallback
                                }
                            } else {
                                // Removed the default icon preview
                            }
                            ?>
                        </span>
                    </p>
                    <p>
                        <label><strong><?php _e('Color', 'steget'); ?>:</strong></label><br>
                        <input type="text" name="selling_point_color[]" value="<?php echo esc_attr($point['color'] ?? ''); ?>" class="widefat selling-point-color-picker">
                    </p>
                    <button type="button" class="button steget-remove-selling-point"><?php _e('Remove', 'steget'); ?></button>
                    <hr>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary add-selling-point"><?php _e('Add Selling Point', 'steget'); ?></button>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new selling point
        $('.add-selling-point').on('click', function() {
            var count = $('.selling-point-item').length + 1;
            var iconOptions = `
                <select name=\"selling_point_icon[]\" class=\"selling-point-icon-select\">
                    <!-- Lucide options -->
                    <?php foreach ($icon_options as $key => $label): ?>
                        <option value=\"<?php echo esc_attr($key); ?>\"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                    <!-- SVG file options -->
                    <?php foreach ($svg_options as $file => $label): ?>
                        <option value=\"<?php echo esc_attr($file); ?>\"><?php echo esc_html($label); ?> (SVG)</option>
                    <?php endforeach; ?>
                </select>
                <span class=\"selling-point-icon-preview\" style=\"vertical-align:middle; margin-left:8px;\">\
                    <?php echo $lucide_svgs['briefcase']; ?>\
                </span>
            `;
            var template = `
                <div class=\"selling-point-item\">\n\
                    <h4><?php _e('Selling Point', 'steget'); ?> #${count}</h4>\n\
                    <p>\n\
                        <label><strong><?php _e('Title', 'steget'); ?>:</strong></label><br>\n\
                        <input type=\"text\" name=\"selling_point_title[]\" value=\"\" class=\"widefat\">\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Description', 'steget'); ?>:</strong></label><br>\n\
                        <textarea name=\"selling_point_description[]\" rows=\"3\" class=\"widefat\"></textarea>\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>\n\
                        ${iconOptions}\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Color', 'steget'); ?>:</strong></label><br>\n\
                        <input type=\"text\" name=\"selling_point_color[]\" value=\"\" class=\"widefat selling-point-color-picker\">\n\
                    </p>\n\
                    <button type=\"button\" class=\"button steget-remove-selling-point\"><?php _e('Remove', 'steget'); ?></button>\n\
                    <hr>\n\
                </div>\n\
            `;
            $('#selling_points_container').append(template);
            // Init color picker for new field
            $('#selling_points_container .selling-point-color-picker').last().wpColorPicker();
        });
        // Remove selling point
        $(document).on('click', '.steget-remove-selling-point', function() {
            $(this).closest('.selling-point-item').remove();
        });
        // Icon preview on select change
        var iconSvgs = {};
        <?php foreach ($lucide_svgs as $key => $svg): ?>
            iconSvgs['<?php echo esc_attr($key); ?>'] = '<?php echo $svg; ?>';
        <?php endforeach; ?>
        var svgIcons = {};
        <?php foreach ($svg_options as $file => $label): ?>
            svgIcons['<?php echo esc_attr($file); ?>'] = '<?php echo file_get_contents($icons_dir . $file); ?>';
        <?php endforeach; ?>
        $(document).on('change', '.selling-point-icon-select', function() {
            var selected = $(this).val();
            var $preview = $(this).siblings('.selling-point-icon-preview');
            if (iconSvgs[selected]) {
                $preview.html(iconSvgs[selected]);
            } else if (svgIcons[selected]) {
                $preview.html(svgIcons[selected]);
            } else {
                $preview.html(''); // Clear preview if icon is not found
            }
        });
        // On page load, make sure all previews are correct
        $('.selling-point-icon-select').each(function() {
            var selected = $(this).val();
            var $preview = $(this).siblings('.selling-point-icon-preview');
            if (iconSvgs[selected]) {
                $preview.html(iconSvgs[selected]);
            } else if (svgIcons[selected]) {
                $preview.html(svgIcons[selected]);
            } else {
                $preview.html('');
            }
        });
        // Init color pickers on page load
        $('.selling-point-color-picker').wpColorPicker();
    });
    </script>
    <?php
}

/**
 * Save selling points template fields
 */
function save_selling_points_template_fields($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['selling_point_title'])) return;
    $points = [];
    $titles = $_POST['selling_point_title'];
    $descriptions = $_POST['selling_point_description'];
    $icons = $_POST['selling_point_icon'];
    $colors = $_POST['selling_point_color'];
    for ($i = 0; $i < count($titles); $i++) {
        if (empty($titles[$i])) continue;
        $points[] = [
            'title' => sanitize_text_field($titles[$i]),
            'description' => sanitize_text_field($descriptions[$i]),
            'icon' => sanitize_text_field($icons[$i]),
            'color' => sanitize_hex_color($colors[$i])
        ];
    }
    update_post_meta($post_id, 'module_selling_points', wp_json_encode($points, JSON_UNESCAPED_UNICODE));
}

// Register meta box
add_action('add_meta_boxes', function() {
    add_meta_box(
        'selling_points_template_fields',
        __('Selling Points', 'steget'),
        'render_selling_points_template_fields',
        'page', // Change to your post type if needed
        'normal',
        'default'
    );
});
add_action('save_post', 'save_selling_points_template_fields');

<?php
/**
 * File: inc/features/stats.php
 * Description: Handles the Stats section functionality for the homepage
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Stats section settings
 */
function steget_register_stats_settings() {
    register_setting('steget_theme_options', 'steget_stats_title', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_stats_subtitle', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_stats_bg_color', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_stats_items', 'steget_sanitize_stats_items');
}
add_action('admin_init', 'steget_register_stats_settings');

/**
 * Sanitize stats items array
 */
function steget_sanitize_stats_items($input) {
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
 * Add Stats section to homepage options tab
 */
function steget_render_stats_section() {
    $stats_title = get_option('steget_stats_title', 'Vårt arbete i siffror');
    $stats_subtitle = get_option('steget_stats_subtitle', 'Bakom varje siffra finns ett barn.');
    $stats_bg_color = get_option('steget_stats_bg_color', 'bg-muted/30');
    $stats_items = get_option('steget_stats_items', array());

    // Default stats items if none exist
    if (empty($stats_items)) {
        $stats_items = array(
            array(
                'id' => 1,
                'value' => '95%',
                'label' => 'Nöjda kunder',
                'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>'
            ),
            array(
                'id' => 2,
                'value' => '200+',
                'label' => 'Hjälpta elever',
                'icon' => '<circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>'
            )
        );
    }
    ?>
<div class="steget-admin-block">
    <h3>Statistik/Siffror</h3>

    <table class="form-table">
        <tr>
            <th scope="row">Titel</th>
            <td>
                <input type="text" name="steget_stats_title" value="<?php echo esc_attr($stats_title); ?>"
                    class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Undertitel</th>
            <td>
                <input type="text" name="steget_stats_subtitle" value="<?php echo esc_attr($stats_subtitle); ?>"
                    class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Bakgrundsfärg</th>
            <td>
                <select name="steget_stats_bg_color">
                    <option value="bg-muted/30" <?php selected($stats_bg_color, 'bg-muted/30'); ?>>Ljusgrå</option>
                    <option value="bg-white" <?php selected($stats_bg_color, 'bg-white'); ?>>Vit</option>
                    <option value="bg-primary/10" <?php selected($stats_bg_color, 'bg-primary/10'); ?>>Ljusgul</option>
                </select>
            </td>
        </tr>
    </table>

    <div class="steget-repeater-field" data-field="stats">
        <h4>Statistikobjekt</h4>
        <div class="steget-repeater-items" id="stats-items-container">
            <?php foreach ($stats_items as $index => $item) : ?>
            <div class="steget-repeater-item">
                <h4>Statistik #<?php echo $index + 1; ?></h4>
                <input type="hidden" name="steget_stats_items[<?php echo $index; ?>][id]"
                    value="<?php echo esc_attr($item['id']); ?>" />

                <p>
                    <label>Värde:</label>
                    <input type="text" name="steget_stats_items[<?php echo $index; ?>][value]"
                        value="<?php echo esc_attr($item['value']); ?>" class="regular-text" />
                    <span class="description">Ex: "95%", "200+", "1000"</span>
                </p>

                <p>
                    <label>Etikett:</label>
                    <input type="text" name="steget_stats_items[<?php echo $index; ?>][label]"
                        value="<?php echo esc_attr($item['label']); ?>" class="regular-text" />
                </p>

                <p>
                    <label>Ikon (SVG-kod):</label>
                    <textarea name="steget_stats_items[<?php echo $index; ?>][icon]" rows="2"
                        class="large-text"><?php echo esc_textarea($item['icon']); ?></textarea>
                    <span class="description">Lucide-ikon SVG-kod (inre path)</span>
                </p>

                <button type="button" class="button steget-remove-item">Ta bort</button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button steget-add-item" data-template="stats-item-template"
            data-container="stats-items-container">Lägg till statistikobjekt</button>

        <!-- Template for new items -->
        <script type="text/template" id="stats-item-template">
            <div class="steget-repeater-item">
                    <h4>Ny statistik</h4>
                    <input type="hidden" name="steget_stats_items[{{index}}][id]" value="{{id}}" />

                    <p>
                        <label>Värde:</label>
                        <input type="text" name="steget_stats_items[{{index}}][value]" value="" class="regular-text" />
                        <span class="description">Ex: "95%", "200+", "1000"</span>
                    </p>

                    <p>
                        <label>Etikett:</label>
                        <input type="text" name="steget_stats_items[{{index}}][label]" value="" class="regular-text" />
                    </p>

                    <p>
                        <label>Ikon (SVG-kod):</label>
                        <textarea name="steget_stats_items[{{index}}][icon]" rows="2" class="large-text"></textarea>
                        <span class="description">Lucide-ikon SVG-kod (inre path)</span>
                    </p>

                    <button type="button" class="button steget-remove-item">Ta bort</button>
                </div>
            </script>
    </div>
</div>
<?php
}

/**
 * Get stats data for the API response
 */
function steget_get_stats_data() {
    $stats_title = get_option('steget_stats_title', 'Vårt arbete i siffror');
    $stats_subtitle = get_option('steget_stats_subtitle', 'Bakom varje siffra finns ett barn.');
    $stats_bg_color = get_option('steget_stats_bg_color', 'bg-muted/30');
    $stats_items = get_option('steget_stats_items', array());

    // Set default stats if none exist
    if (empty($stats_items)) {
        $stats_items = array(
            array(
                'id' => 1,
                'value' => '95%',
                'label' => 'Nöjda kunder',
                'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>'
            ),
            array(
                'id' => 2,
                'value' => '200+',
                'label' => 'Hjälpta elever',
                'icon' => '<circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>'
            )
        );
    }

    return array(
        'title' => $stats_title,
        'subtitle' => $stats_subtitle,
        'background_color' => $stats_bg_color,
        'stats' => $stats_items
    );
}
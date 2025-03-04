<?php
/**
 * File: inc/features/selling-points.php
 * Description: Handles the Selling Points section functionality for the homepage
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Selling Points section settings
 */
function steget_register_selling_points_settings() {
    register_setting('steget_theme_options', 'steget_selling_points_title', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_selling_points_items', 'steget_sanitize_selling_points_items');
}
add_action('admin_init', 'steget_register_selling_points_settings');

/**
 * Sanitize selling points items array
 */
function steget_sanitize_selling_points_items($input) {
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
            'title' => sanitize_text_field($item['title'] ?? ''),
            'description' => sanitize_text_field($item['description'] ?? ''),
            'icon' => sanitize_text_field($item['icon'] ?? '')
        );

        $sanitized_input[] = $sanitized_item;
    }

    return $sanitized_input;
}

/**
 * Add Selling Points section to homepage options tab
 */
function steget_render_selling_points_section() {
    $title = get_option('steget_selling_points_title', 'Varför välja oss');
    $items = get_option('steget_selling_points_items', array());

    // Default selling points if none exist
    if (empty($items)) {
        $items = array(
            array(
                'id' => 1,
                'title' => 'Professionell hjälp',
                'description' => 'Vi erbjuder högkvalitativ professionell service som anpassas efter dina behov.',
                'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'
            ),
            array(
                'id' => 2,
                'title' => 'Ett team av experter',
                'description' => 'Vårt team av experter är redo att hjälpa dig att nå dina mål.',
                'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>'
            ),
            array(
                'id' => 3,
                'title' => 'Fokuserad målbild',
                'description' => 'Vi är dedikerade till att du ska känna dig nöjd med vårt arbete.',
                'icon' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'
            )
        );
    }
    ?>
<div class="steget-admin-block">
    <h3>Försäljningspunkter/USP</h3>

    <table class="form-table">
        <tr>
            <th scope="row">Sektionsrubrik</th>
            <td>
                <input type="text" name="steget_selling_points_title" value="<?php echo esc_attr($title); ?>"
                    class="regular-text" />
            </td>
        </tr>
    </table>

    <div class="steget-repeater-field" data-field="selling-points">
        <h4>Punkter</h4>
        <div class="steget-repeater-items" id="selling-points-container">
            <?php foreach ($items as $index => $item) : ?>
            <div class="steget-repeater-item">
                <h4>Punkt #<?php echo $index + 1; ?></h4>
                <input type="hidden" name="steget_selling_points_items[<?php echo $index; ?>][id]"
                    value="<?php echo esc_attr($item['id']); ?>" />

                <p>
                    <label>Rubrik:</label>
                    <input type="text" name="steget_selling_points_items[<?php echo $index; ?>][title]"
                        value="<?php echo esc_attr($item['title']); ?>" class="regular-text" />
                </p>

                <p>
                    <label>Beskrivning:</label>
                    <textarea name="steget_selling_points_items[<?php echo $index; ?>][description]" rows="3"
                        class="large-text"><?php echo esc_textarea($item['description']); ?></textarea>
                </p>

                <p>
                    <label>Ikon (SVG-kod):</label>
                    <textarea name="steget_selling_points_items[<?php echo $index; ?>][icon]" rows="2"
                        class="large-text"><?php echo esc_textarea($item['icon']); ?></textarea>
                    <span class="description">Lucide-ikon SVG-kod (inre path)</span>
                </p>

                <button type="button" class="button steget-remove-item">Ta bort</button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button steget-add-item" data-template="selling-point-template"
            data-container="selling-points-container">Lägg till punkt</button>

        <!-- Template for new items -->
        <script type="text/template" id="selling-point-template">
            <div class="steget-repeater-item">
                    <h4>Ny punkt</h4>
                    <input type="hidden" name="steget_selling_points_items[{{index}}][id]" value="{{id}}" />

                    <p>
                        <label>Rubrik:</label>
                        <input type="text" name="steget_selling_points_items[{{index}}][title]" value="" class="regular-text" />
                    </p>

                    <p>
                        <label>Beskrivning:</label>
                        <textarea name="steget_selling_points_items[{{index}}][description]" rows="3" class="large-text"></textarea>
                    </p>

                    <p>
                        <label>Ikon (SVG-kod):</label>
                        <textarea name="steget_selling_points_items[{{index}}][icon]" rows="2" class="large-text"></textarea>
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
 * Get selling points data for the API response
 */
function steget_get_selling_points_data() {
    $title = get_option('steget_selling_points_title', 'Varför välja oss');
    $items = get_option('steget_selling_points_items', array());

    // Default selling points if none exist
    if (empty($items)) {
        $items = array(
            array(
                'id' => 1,
                'title' => 'Professionell hjälp',
                'description' => 'Vi erbjuder högkvalitativ professionell service som anpassas efter dina behov.',
                'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'
            ),
            array(
                'id' => 2,
                'title' => 'Ett team av experter',
                'description' => 'Vårt team av experter är redo att hjälpa dig att nå dina mål.',
                'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>'
            ),
            array(
                'id' => 3,
                'title' => 'Fokuserad målbild',
                'description' => 'Vi är dedikerade till att du ska känna dig nöjd med vårt arbete.',
                'icon' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'
            )
        );
    }

    return array(
        'title' => $title,
        'points' => $items
    );
}
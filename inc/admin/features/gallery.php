<?php
/**
 * File: inc/features/gallery.php
 * Description: Handles the Gallery section functionality for the homepage
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Gallery section settings
 */
function steget_register_gallery_settings() {
    register_setting('steget_theme_options', 'steget_gallery_title', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_gallery_items', 'steget_sanitize_gallery_items');
}
add_action('admin_init', 'steget_register_gallery_settings');

/**
 * Sanitize gallery items array
 */
function steget_sanitize_gallery_items($input) {
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
            'image' => esc_url_raw($item['image'] ?? '')
        );

        $sanitized_input[] = $sanitized_item;
    }

    return $sanitized_input;
}

/**
 * Add Gallery section to homepage options tab
 */
function steget_render_gallery_section() {
    $title = get_option('steget_gallery_title', 'Vårt Galleri');
    $items = get_option('steget_gallery_items', array());

    // Initialize the WordPress media uploader
    wp_enqueue_media();
    ?>
<div class="steget-admin-block">
    <h3>Gallerisektion</h3>

    <table class="form-table">
        <tr>
            <th scope="row">Sektionsrubrik</th>
            <td>
                <input type="text" name="steget_gallery_title" value="<?php echo esc_attr($title); ?>"
                    class="regular-text" />
            </td>
        </tr>
    </table>

    <div class="steget-repeater-field" data-field="gallery">
        <h4>Galleribilder</h4>
        <div class="steget-repeater-items" id="gallery-items-container">
            <?php foreach ($items as $index => $item) : ?>
            <div class="steget-repeater-item">
                <h4>Bild #<?php echo $index + 1; ?></h4>
                <input type="hidden" name="steget_gallery_items[<?php echo $index; ?>][id]"
                    value="<?php echo esc_attr($item['id']); ?>" />

                <p>
                    <label>Titel:</label>
                    <input type="text" name="steget_gallery_items[<?php echo $index; ?>][title]"
                        value="<?php echo esc_attr($item['title']); ?>" class="regular-text" />
                </p>

                <p>
                    <label>Beskrivning:</label>
                    <textarea name="steget_gallery_items[<?php echo $index; ?>][description]" rows="2"
                        class="large-text"><?php echo esc_textarea($item['description'] ?? ''); ?></textarea>
                </p>

                <p>
                    <label>Bild:</label>
                <div class="steget-media-field">
                    <input type="hidden" name="steget_gallery_items[<?php echo $index; ?>][image]"
                        value="<?php echo esc_attr($item['image']); ?>" class="steget-media-input" />
                    <div class="steget-image-preview" style="max-width: 150px; margin-bottom: 10px;">
                        <?php if (!empty($item['image'])) : ?>
                        <img src="<?php echo esc_url($item['image']); ?>" style="max-width: 100%; height: auto;" />
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button steget-upload-image">Välj bild</button>
                    <button type="button" class="button steget-remove-image"
                        <?php echo empty($item['image']) ? 'style="display:none;"' : ''; ?>>Ta bort bild</button>
                </div>
                </p>

                <button type="button" class="button steget-remove-item">Ta bort</button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button steget-add-item" data-template="gallery-item-template"
            data-container="gallery-items-container">Lägg till galleribild</button>

        <!-- Template for new items -->
        <script type="text/template" id="gallery-item-template">
            <div class="steget-repeater-item">
                    <h4>Ny bild</h4>
                    <input type="hidden" name="steget_gallery_items[{{index}}][id]" value="{{id}}" />

                    <p>
                        <label>Titel:</label>
                        <input type="text" name="steget_gallery_items[{{index}}][title]" value="" class="regular-text" />
                    </p>

                    <p>
                        <label>Beskrivning:</label>
                        <textarea name="steget_gallery_items[{{index}}][description]" rows="2" class="large-text"></textarea>
                    </p>

                    <p>
                        <label>Bild:</label>
                        <div class="steget-media-field">
                            <input type="hidden" name="steget_gallery_items[{{index}}][image]" value="" class="steget-media-input" />
                            <div class="steget-image-preview" style="max-width: 150px; margin-bottom: 10px;"></div>
                            <button type="button" class="button steget-upload-image">Välj bild</button>
                            <button type="button" class="button steget-remove-image" style="display:none;">Ta bort bild</button>
                        </div>
                    </p>

                    <button type="button" class="button steget-remove-item">Ta bort</button>
                </div>
            </script>
    </div>
</div>

<!-- Media uploader JavaScript -->
<script>
jQuery(document).ready(function($) {
    // Handle the media uploader
    $(document).on('click', '.steget-upload-image', function(e) {
        e.preventDefault();

        var button = $(this);
        var container = button.closest('.steget-media-field');
        var inputField = container.find('.steget-media-input');
        var previewContainer = container.find('.steget-image-preview');
        var removeButton = container.find('.steget-remove-image');

        var frame = wp.media({
            title: 'Välj eller ladda upp en bild',
            button: {
                text: 'Använd denna bild'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            inputField.val(attachment.url);

            previewContainer.html('<img src="' + attachment.url +
                '" style="max-width: 100%; height: auto;" />');
            removeButton.show();
        });

        frame.open();
    });

    // Handle the remove image button
    $(document).on('click', '.steget-remove-image', function() {
        var button = $(this);
        var container = button.closest('.steget-media-field');
        var inputField = container.find('.steget-media-input');
        var previewContainer = container.find('.steget-image-preview');

        inputField.val('');
        previewContainer.empty();
        button.hide();
    });
});
</script>
<?php
}

/**
 * Get gallery data for the API response
 */
function steget_get_gallery_data() {
    $title = get_option('steget_gallery_title', 'Vårt Galleri');
    $items = get_option('steget_gallery_items', array());

    // If no gallery items exist, provide defaults with placeholder images
    if (empty($items)) {
        $items = array(
            array(
                'id' => 1,
                'title' => 'Klassrum',
                'description' => 'Moderna klassrum för optimal inlärning.',
                'image' => 'https://via.placeholder.com/800x600?text=Klassrum'
            ),
            array(
                'id' => 2,
                'title' => 'Utemiljö',
                'description' => 'Inspirerande utemiljö för lek och lärande.',
                'image' => 'https://via.placeholder.com/800x600?text=Utemiljo'
            ),
            array(
                'id' => 3,
                'title' => 'Personal',
                'description' => 'Vår engagerade personal arbetar för barnens bästa.',
                'image' => 'https://via.placeholder.com/800x600?text=Personal'
            )
        );
    }

    return array(
        'title' => $title,
        'items' => $items
    );
}
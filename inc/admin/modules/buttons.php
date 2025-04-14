<?php
/**
 * Button functionality for modules
 * 
 * @package Steget
 */

/**
 * Render module buttons meta box
 */
function render_module_buttons_meta_box($post) {
    $buttons = json_decode(get_post_meta($post->ID, 'module_buttons', true), true) ?: [];
    ?>
<div class="module-buttons-panel">
    <div id="module_buttons_container">
        <?php if (empty($buttons)) : ?>
        <div class="no-buttons-message">
            <p><?php _e('No buttons added yet. Click "Add Button" below to add the first button.', 'steget'); ?></p>
        </div>
        <?php else : ?>
        <?php foreach ($buttons as $index => $button) : ?>
        <div class="button-item" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
            <h4 style="margin-top: 0;"><?php _e('Button', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label for="button_text_<?php echo $index; ?>"><strong><?php _e('Button Text', 'steget'); ?>:</strong></label><br>
                <input type="text" name="button_text[]" id="button_text_<?php echo $index; ?>"
                    value="<?php echo esc_attr($button['text']); ?>" class="widefat">
            </p>
            <p>
                <label for="button_url_<?php echo $index; ?>"><strong><?php _e('Button URL', 'steget'); ?>:</strong></label><br>
                <input type="text" name="button_url[]" id="button_url_<?php echo $index; ?>"
                    value="<?php echo esc_attr($button['url']); ?>" class="widefat">
            </p>
            <p>
                <label for="button_style_<?php echo $index; ?>"><strong><?php _e('Button Style', 'steget'); ?>:</strong></label><br>
                <select name="button_style[]" id="button_style_<?php echo $index; ?>" class="widefat">
                    <option value="primary" <?php selected($button['style'], 'primary'); ?>>
                        <?php _e('Primary', 'steget'); ?></option>
                    <option value="secondary" <?php selected($button['style'], 'secondary'); ?>>
                        <?php _e('Secondary', 'steget'); ?></option>
                    <option value="outline" <?php selected($button['style'], 'outline'); ?>>
                        <?php _e('Outline', 'steget'); ?></option>
                    <option value="link" <?php selected($button['style'], 'link'); ?>><?php _e('Link', 'steget'); ?>
                    </option>
                </select>
            </p>
            <p>
                <label for="button_size_<?php echo $index; ?>"><strong><?php _e('Button Size', 'steget'); ?>:</strong></label><br>
                <select name="button_size[]" id="button_size_<?php echo $index; ?>" class="widefat">
                    <option value="sm" <?php selected($button['size'], 'sm'); ?>><?php _e('Small', 'steget'); ?>
                    </option>
                    <option value="md" <?php selected($button['size'], 'md'); ?>><?php _e('Medium', 'steget'); ?>
                    </option>
                    <option value="lg" <?php selected($button['size'], 'lg'); ?>><?php _e('Large', 'steget'); ?>
                    </option>
                </select>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="button_new_tab[]" <?php checked($button['new_tab'], true); ?>>
                    <strong><?php _e('Open in New Tab', 'steget'); ?></strong>
                </label>
            </p>
            <p>
                <button type="button" class="button remove-button"><?php _e('Remove Button', 'steget'); ?></button>
            </p>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p>
        <button type="button" id="add_button" class="button-secondary">
            <span class="dashicons dashicons-plus" style="vertical-align: text-top;"></span>
            <?php _e('Add Button', 'steget'); ?>
        </button>
    </p>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Add a new button
    $('#add_button').on('click', function() {
        var buttonCount = $('.button-item').length;
        var template = `
            <div class="button-item" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                <h4 style="margin-top: 0;"><?php _e('Button', 'steget'); ?> #${buttonCount + 1}</h4>
                <p>
                    <label for="button_text_${buttonCount}"><strong><?php _e('Button Text', 'steget'); ?>:</strong></label><br>
                    <input type="text" name="button_text[]" id="button_text_${buttonCount}" value="" class="widefat">
                </p>
                <p>
                    <label for="button_url_${buttonCount}"><strong><?php _e('Button URL', 'steget'); ?>:</strong></label><br>
                    <input type="text" name="button_url[]" id="button_url_${buttonCount}" value="" class="widefat">
                </p>
                <p>
                    <label for="button_style_${buttonCount}"><strong><?php _e('Button Style', 'steget'); ?>:</strong></label><br>
                    <select name="button_style[]" id="button_style_${buttonCount}" class="widefat">
                        <option value="primary"><?php _e('Primary', 'steget'); ?></option>
                        <option value="secondary"><?php _e('Secondary', 'steget'); ?></option>
                        <option value="outline"><?php _e('Outline', 'steget'); ?></option>
                        <option value="link"><?php _e('Link', 'steget'); ?></option>
                    </select>
                </p>
                <p>
                    <label for="button_size_${buttonCount}"><strong><?php _e('Button Size', 'steget'); ?>:</strong></label><br>
                    <select name="button_size[]" id="button_size_${buttonCount}" class="widefat">
                        <option value="sm"><?php _e('Small', 'steget'); ?></option>
                        <option value="md" selected><?php _e('Medium', 'steget'); ?></option>
                        <option value="lg"><?php _e('Large', 'steget'); ?></option>
                    </select>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="button_new_tab[]">
                        <strong><?php _e('Open in New Tab', 'steget'); ?></strong>
                    </label>
                </p>
                <p>
                    <button type="button" class="button remove-button"><?php _e('Remove Button', 'steget'); ?></button>
                </p>
            </div>
        `;

        $('.no-buttons-message').remove();
        $('#module_buttons_container').append(template);
    });

    // Remove Button
    $('#module_buttons_container').on('click', '.remove-button', function() {
        $(this).closest('.button-item').remove();

        // Renumber the buttons
        $('.button-item h4').each(function(index) {
            $(this).text('<?php _e('Button', 'steget'); ?> #' + (index + 1));
        });
    });
});
</script>
<?php
}

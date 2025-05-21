<?php
/** 
 * Base module functionality for admin UI
 * 
 * @package Steget
 */

/**
 * Add meta boxes for module settings
 */
function add_module_meta_boxes() {
    add_meta_box(
        'module_settings',
        __('Module Settings', 'steget'),
        'render_module_settings_meta_box',
        'module',
        'normal',
        'high'
    );

    add_meta_box(
        'module_template_settings',
        __('Template Settings', 'steget'),
        'render_module_template_settings_meta_box',
        'module',
        'normal',
        'high'
    );

    add_meta_box(
        'module_buttons',
        __('Buttons', 'steget'),
        'render_module_buttons_meta_box',
        'module',
        'normal',
        'default'
    );

    add_meta_box(
        'module_preview',
        __('Module Preview', 'steget'),
        'render_module_preview_meta_box',
        'module',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_module_meta_boxes');

/**
 * Render core module settings meta box
 */
function render_module_settings_meta_box($post) {
    wp_nonce_field('save_module_meta', 'module_meta_nonce');

    $template = get_post_meta($post->ID, 'module_template', true);
    $layout = get_post_meta($post->ID, 'module_layout', true) ?: 'center';
    $full_width = get_post_meta($post->ID, 'module_full_width', true);
    $bg_color = get_post_meta($post->ID, 'module_background_color', true);

    $template_options = get_module_templates();
    $layout_options = get_layout_options();
    ?>
<div class="module-settings-panel">
    <p>
        <label for="module_template"><strong><?php _e('Template Type', 'steget'); ?>:</strong></label><br>
        <select name="module_template" id="module_template" class="widefat">
            <option value=""><?php _e('Select a template', 'steget'); ?></option>
            <?php foreach ($template_options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($template, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="module_layout"><strong><?php _e('Layout', 'steget'); ?>:</strong></label><br>
        <select name="module_layout" id="module_layout" class="widefat">
            <?php foreach ($layout_options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($layout, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="module_full_width">
            <input type="checkbox" name="module_full_width" id="module_full_width" <?php checked($full_width, true); ?>>
            <strong><?php _e('Full Width Layout', 'steget'); ?></strong>
        </label>
    </p>

    <p>
        <label for="module_background_color"><strong><?php _e('Background Color', 'steget'); ?>:</strong></label><br>
        <input type="text" name="module_background_color" id="module_background_color"
            value="<?php echo esc_attr($bg_color); ?>" class="color-picker widefat">
        <span
            class="description"><?php _e('Background color for this module (leave empty for default)', 'steget'); ?></span>
    </p>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Show/hide template fields based on template selection
    function toggleTemplateFields() {
        var template = $('#module_template').val();
        $('.template-fields').hide();
        $('#' + template + '_fields').show();
    }

    $('#module_template').on('change', toggleTemplateFields);
    toggleTemplateFields();
});
</script>
<?php
}

/**
 * Module preview meta box
 */
function render_module_preview_meta_box($post) {
    $template = get_post_meta($post->ID, 'module_template', true);
    $id = $post->ID;
    ?>
<div class="preview-panel">
    <p><?php _e('Save the module to see a preview.', 'steget'); ?></p>
    <?php if ($template) : ?>
    <div class="preview-actions">
        <a href="<?php echo esc_url(home_url('?preview_module=' . $id)); ?>" target="_blank"
            class="button"><?php _e('View Preview', 'steget'); ?></a>
    </div>
    <?php endif; ?>
</div>
<?php
}

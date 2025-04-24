<?php
/**
 * Tabbed Content module template fields
 * 
 * @package Steget
 */

/**
 * Render tabbed content template fields
 */
function render_tabbed_content_template_fields($post) {
    $tabbed_content = json_decode(get_post_meta($post->ID, 'module_tabbed_content', true), true);
    if (!is_array($tabbed_content) || empty($tabbed_content)) {
        $tabbed_content = [['title' => '', 'content' => '', 'image' => '', 'imageAlign' => 'left']];
    }
    $layout = get_post_meta($post->ID, 'tabbed_content_layout', true);
    ?>
<div id="tabbed_content_fields" class="template-fields">
    <div id="tabs_container">
        <?php foreach ($tabbed_content as $index => $tab) : ?>
        <div class="tab-item">
            <h4><?php _e('Tab', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label><strong><?php _e('Tab Title', 'steget'); ?>:</strong></label><br>
                <input type="text" name="tab_title[]" value="<?php echo esc_attr($tab['title']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Tab Content', 'steget'); ?>:</strong></label><br>
                <textarea name="tab_content[]" class="widefat" rows="6"><?php echo esc_textarea($tab['content']); ?></textarea>
            </p>
            <p>
                <label><strong><?php _e('Tab Image', 'steget'); ?>:</strong></label><br>
                <input type="text" name="tab_image[]" value="<?php echo esc_attr($tab['image'] ?? ''); ?>" class="widefat steget-media-field">
                <button type="button" class="button select-media"><?php _e('Select Image', 'steget'); ?></button>
            </p>
            <p>
                <label><strong><?php _e('Image Alignment', 'steget'); ?>:</strong></label><br>
                <select name="tab_image_align[]" class="widefat">
                    <option value="left" <?php if (($tab['imageAlign'] ?? 'left') === 'left') echo 'selected'; ?>><?php _e('Left', 'steget'); ?></option>
                    <option value="right" <?php if (($tab['imageAlign'] ?? 'left') === 'right') echo 'selected'; ?>><?php _e('Right', 'steget'); ?></option>
                </select>
            </p>
            <button type="button" class="button remove-tab"><?php _e('Remove Tab', 'steget'); ?></button>
            <hr>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button button-primary add-tab"><?php _e('Add Tab', 'steget'); ?></button>
    <hr>
    <div class="tabbed-content-layout">
        <label><strong><?php _e('Layout', 'steget'); ?>:</strong></label><br>
        <select name="tabbed_content_layout" class="widefat">
            <option value="horizontal" <?php selected($layout, 'horizontal'); ?>><?php _e('Horizontal', 'steget'); ?></option>
            <option value="vertical" <?php selected($layout, 'vertical'); ?>><?php _e('Vertical', 'steget'); ?></option>
        </select>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new tab
        $('.add-tab').on('click', function() {
            var count = $('.tab-item').length + 1;
            var template = `
                <div class=\"tab-item\">\n\
                    <h4><?php _e('Tab', 'steget'); ?> #${count}</h4>\n\
                    <p>\n\
                        <label><strong><?php _e('Tab Title', 'steget'); ?>:</strong></label><br>\n\
                        <input type=\"text\" name=\"tab_title[]\" value=\"\" class=\"widefat\">\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Tab Content', 'steget'); ?>:</strong></label><br>\n\
                        <textarea name=\"tab_content[]\" class=\"widefat\" rows=\"6\"></textarea>\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Tab Image', 'steget'); ?>:</strong></label><br>\n\
                        <input type=\"text\" name=\"tab_image[]\" value=\"\" class=\"widefat steget-media-field\">\n\
                        <button type=\"button\" class=\"button select-media\"><?php _e('Select Image', 'steget'); ?></button>\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Image Alignment', 'steget'); ?>:</strong></label><br>\n\
                        <select name=\"tab_image_align[]\" class=\"widefat\">\n\
                            <option value=\"left\"><?php _e('Left', 'steget'); ?></option>\n\
                            <option value=\"right\"><?php _e('Right', 'steget'); ?></option>\n\
                        </select>\n\
                    </p>\n\
                    <button type=\"button\" class=\"button remove-tab\"><?php _e('Remove Tab', 'steget'); ?></button>\n\
                    <hr>\n\
                </div>\n\
            `;
            $('#tabs_container').append(template);
        });
        // Remove tab
        $(document).on('click', '.remove-tab', function() {
            $(this).closest('.tab-item').remove();
        });
    });
    </script>
</div>
<?php
}

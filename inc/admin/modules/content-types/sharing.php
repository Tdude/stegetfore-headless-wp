<?php
/**
 * Sharing module template fields
 * 
 * @package Steget
 */

/**
 * Render sharing template fields
 */
function render_sharing_template_fields($post) {
    $sharing_url = get_post_meta($post->ID, 'module_sharing_url', true);
    $networks = json_decode(get_post_meta($post->ID, 'module_sharing_networks', true), true) ?: [];
    require_once dirname(__DIR__, 2) . '/lucide-icons.php';
    $icons = array_keys(Lucide_Icons::all());
    ?>
    <div id="sharing_fields" class="template-fields">
        <p>
            <label for="sharing_url"><strong><?php _e('Share URL', 'steget'); ?>:</strong></label><br>
            <input type="text" name="sharing_url" id="sharing_url" value="<?php echo esc_attr($sharing_url); ?>" class="widefat">
        </p>
        <div id="sharing_networks_container">
            <?php
            // Always render at least one empty network item if none exist
            if (empty($networks)) {
                $networks = [ [ 'name' => '', 'url' => '', 'icon' => '' ] ];
            }
            foreach ($networks as $i => $network): ?>
                <div class="sharing-network-item">
                    <p>
                        <label><strong><?php _e('Network', 'steget'); ?>:</strong></label><br>
                        <input type="text" name="sharing_network_name[]" value="<?php echo esc_attr($network['name'] ?? ''); ?>" class="widefat">
                    </p>
                    <p>
                        <label><strong><?php _e('URL', 'steget'); ?>:</strong></label><br>
                        <input type="text" name="sharing_network_url[]" value="<?php echo esc_attr($network['url'] ?? ''); ?>" class="widefat">
                    </p>
                    <p>
                        <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>
                        <select name="sharing_network_icon[]" class="sharing-network-icon-select widefat">
                            <?php foreach ($icons as $icon): ?>
                                <option value="<?php echo esc_attr($icon); ?>" <?php selected(($network['icon'] ?? '') === $icon); ?>><?php echo esc_html($icon); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="sharing-network-icon-preview"></span>
                    </p>
                    <button type="button" class="button is-destructive steget-remove-sharing-network"><?php _e('Remove', 'steget'); ?></button>
                    <hr>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-sharing-network"><?php _e('Add Network', 'steget'); ?></button>
        <div id="sharing-icon-select-template" style="display:none;">
            <select name="sharing_network_icon[]" class="sharing-network-icon-select widefat">
                <?php foreach ($icons as $icon): ?>
                    <option value="<?php echo esc_attr($icon); ?>"><?php echo esc_html($icon); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php
}

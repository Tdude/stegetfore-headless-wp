<?php
/**
 * Call to Action Module
 * inc/admin/modules/content-types/cta.php
 */

/**
 * Render CTA template fields
 */
function render_cta_template_fields($post) {
    // You can add logic here to get saved meta if needed
    ?>
    <div id="cta_fields" class="template-fields">
        <p><?php _e('The Call to Action module uses the main content editor for text content and the Buttons section below for action buttons.', 'steget'); ?></p>
    </div>
    <?php
}

/**
 * Add custom fields for the CTA module
 */
function render_cta_module_meta_box($post) {
    // Get current values
    $title = get_post_meta($post->ID, 'module_title', true);
    $content = get_post_meta($post->ID, 'module_content', true);
    $background = get_post_meta($post->ID, 'module_background', true) ?: 'light';
    $text_alignment = get_post_meta($post->ID, 'module_text_alignment', true) ?: 'center';
    $width = get_post_meta($post->ID, 'module_width', true) ?: 'normal';
    $padding = get_post_meta($post->ID, 'module_padding', true) ?: 'medium';
    
    wp_nonce_field('save_cta_module', 'cta_module_nonce');
    ?>
    <div class="module-field">
        <label for="module_title"><?php _e('Title', 'steget'); ?></label>
        <input type="text" id="module_title" name="module_title" value="<?php echo esc_attr($title); ?>" required>
    </div>
    
    <div class="module-field">
        <label for="module_content"><?php _e('Content', 'steget'); ?></label>
        <?php 
        wp_editor(
            $content, 
            'module_content', 
            [
                'media_buttons' => true,
                'textarea_name' => 'module_content',
                'textarea_rows' => 5,
                'teeny' => false
            ]
        ); 
        ?>
    </div>
    
    <div class="module-field">
        <label for="module_background"><?php _e('Background', 'steget'); ?></label>
        <select id="module_background" name="module_background">
            <option value="none" <?php selected($background, 'none'); ?>><?php _e('None', 'steget'); ?></option>
            <option value="light" <?php selected($background, 'light'); ?>><?php _e('Light', 'steget'); ?></option>
            <option value="dark" <?php selected($background, 'dark'); ?>><?php _e('Dark', 'steget'); ?></option>
            <option value="primary" <?php selected($background, 'primary'); ?>><?php _e('Primary', 'steget'); ?></option>
        </select>
    </div>
    
    <div class="module-field">
        <label for="module_text_alignment"><?php _e('Text Alignment', 'steget'); ?></label>
        <select id="module_text_alignment" name="module_text_alignment">
            <option value="left" <?php selected($text_alignment, 'left'); ?>><?php _e('Left', 'steget'); ?></option>
            <option value="center" <?php selected($text_alignment, 'center'); ?>><?php _e('Center', 'steget'); ?></option>
            <option value="right" <?php selected($text_alignment, 'right'); ?>><?php _e('Right', 'steget'); ?></option>
        </select>
    </div>
    
    <div class="module-field">
        <label for="module_width"><?php _e('Width', 'steget'); ?></label>
        <select id="module_width" name="module_width">
            <option value="normal" <?php selected($width, 'normal'); ?>><?php _e('Normal', 'steget'); ?></option>
            <option value="wide" <?php selected($width, 'wide'); ?>><?php _e('Wide', 'steget'); ?></option>
            <option value="full" <?php selected($width, 'full'); ?>><?php _e('Full Width', 'steget'); ?></option>
        </select>
    </div>
    
    <div class="module-field">
        <label for="module_padding"><?php _e('Padding', 'steget'); ?></label>
        <select id="module_padding" name="module_padding">
            <option value="none" <?php selected($padding, 'none'); ?>><?php _e('None', 'steget'); ?></option>
            <option value="small" <?php selected($padding, 'small'); ?>><?php _e('Small', 'steget'); ?></option>
            <option value="medium" <?php selected($padding, 'medium'); ?>><?php _e('Medium', 'steget'); ?></option>
            <option value="large" <?php selected($padding, 'large'); ?>><?php _e('Large', 'steget'); ?></option>
        </select>
    </div>
    <?php
}

/**
 * Save CTA module data
 */
function save_cta_module($post_id) {
    // Check if nonce is set and valid
    if (!isset($_POST['cta_module_nonce']) || !wp_verify_nonce($_POST['cta_module_nonce'], 'save_cta_module')) {
        return;
    }
    
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save module fields
    if (isset($_POST['module_title'])) {
        update_post_meta($post_id, 'module_title', sanitize_text_field($_POST['module_title']));
    }
    
    if (isset($_POST['module_content'])) {
        update_post_meta($post_id, 'module_content', wp_kses_post($_POST['module_content']));
    }
    
    if (isset($_POST['module_background'])) {
        update_post_meta($post_id, 'module_background', sanitize_text_field($_POST['module_background']));
    }
    
    if (isset($_POST['module_text_alignment'])) {
        update_post_meta($post_id, 'module_text_alignment', sanitize_text_field($_POST['module_text_alignment']));
    }
    
    if (isset($_POST['module_width'])) {
        update_post_meta($post_id, 'module_width', sanitize_text_field($_POST['module_width']));
    }
    
    if (isset($_POST['module_padding'])) {
        update_post_meta($post_id, 'module_padding', sanitize_text_field($_POST['module_padding']));
    }
}
add_action('save_post_module', 'save_cta_module');

/**
 * Register meta box for CTA module
 */
function add_cta_module_meta_box() {
    add_meta_box(
        'cta_module_meta_box',
        __('Call to Action Settings', 'steget'),
        'render_cta_module_meta_box',
        'module',
        'normal',
        'high',
        ['module_type' => 'cta']
    );
}
add_action('add_meta_boxes_module', function($post) {
    // Only add the meta box if this is a CTA module
    $module_type = get_post_meta($post->ID, 'module_type', true);
    if ($module_type === 'cta') {
        add_cta_module_meta_box();
    }
});
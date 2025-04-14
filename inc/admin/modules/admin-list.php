<?php
/**
 * Module admin list columns and filters
 * 
 * @package Steget
 */

/**
 * Add custom columns to the modules admin list
 */
function add_module_admin_columns($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['template'] = __('Template', 'steget');
            $new_columns['layout'] = __('Layout', 'steget');
        } else if ($key === 'date') {
            $new_columns['shortcode'] = __('Shortcode', 'steget');
            $new_columns[$key] = $value;
        } else {
            $new_columns[$key] = $value;
        }
    }
    return $new_columns;
}
add_filter('manage_module_posts_columns', 'add_module_admin_columns');

/**
 * Populate custom column data
 */
function populate_module_admin_columns($column, $post_id) {
    switch ($column) {
        case 'template':
            $template = get_post_meta($post_id, 'module_template', true);
            $templates = get_module_templates();
            if (isset($templates[$template])) {
                echo esc_html($templates[$template]);
            } else {
                echo '<em>' . __('None', 'steget') . '</em>';
            }
            break;
            
        case 'layout':
            $layout = get_post_meta($post_id, 'module_layout', true) ?: 'center';
            $full_width = get_post_meta($post_id, 'module_full_width', true);
            
            $layouts = get_layout_options();
            
            if (isset($layouts[$layout])) {
                echo esc_html($layouts[$layout]);
                if ($full_width) {
                    echo ' <span class="badge" style="background: #eee; border-radius: 3px; padding: 2px 5px; font-size: 11px;">' . __('Full Width', 'steget') . '</span>';
                }
            } else {
                echo '<em>' . __('Default', 'steget') . '</em>';
            }
            break;
            
        case 'shortcode':
            echo '<code>[module id="' . $post_id . '"]</code>';
            echo ' <button class="copy-shortcode button button-small" data-shortcode="[module id=&quot;' . $post_id . '&quot;]">';
            echo '<span class="dashicons dashicons-clipboard" style="vertical-align: text-bottom;"></span> ' . __('Copy', 'steget');
            echo '</button>';
            echo '<span class="copied-message" style="display:none; margin-left: 5px; color: green;">' . __('Copied!', 'steget') . '</span>';
            break;
    }

    // Add clipboard functionality for shortcodes
    add_action('admin_footer', function() {
        ?>
<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcode).select();
        document.execCommand('copy');
        tempInput.remove();
        
        $(this).next('.copied-message').fadeIn().delay(1000).fadeOut();
    });
});
</script>
        <?php
    });
}
add_action('manage_module_posts_custom_column', 'populate_module_admin_columns', 10, 2);

/**
 * Add sortable columns
 */
function make_module_admin_columns_sortable($columns) {
    $columns['template'] = 'template';
    $columns['layout'] = 'layout';
    return $columns;
}
add_filter('manage_edit-module_sortable_columns', 'make_module_admin_columns_sortable');

/**
 * Add filter dropdowns
 */
function add_module_admin_filters() {
    global $typenow;
    
    if ($typenow === 'module') {
        // Template filter
        $templates = get_module_templates();
        $current_template = isset($_GET['module_template']) ? $_GET['module_template'] : '';
        ?>
        <select name="module_template">
            <option value=""><?php _e('All Templates', 'steget'); ?></option>
            <?php foreach ($templates as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_template, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php
        
        // Layout filter
        $layouts = get_layout_options();
        $current_layout = isset($_GET['module_layout']) ? $_GET['module_layout'] : '';
        ?>
        <select name="module_layout">
            <option value=""><?php _e('All Layouts', 'steget'); ?></option>
            <?php foreach ($layouts as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_layout, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
add_action('restrict_manage_posts', 'add_module_admin_filters');

/**
 * Process admin filters
 */
function process_module_admin_filters($query) {
    global $pagenow, $typenow;
    
    if (is_admin() && $pagenow === 'edit.php' && $typenow === 'module' && $query->is_main_query()) {
        // Template filter
        if (isset($_GET['module_template']) && !empty($_GET['module_template'])) {
            $query->query_vars['meta_key'] = 'module_template';
            $query->query_vars['meta_value'] = sanitize_text_field($_GET['module_template']);
        }
        
        // Layout filter
        if (isset($_GET['module_layout']) && !empty($_GET['module_layout'])) {
            $query->query_vars['meta_key'] = 'module_layout';
            $query->query_vars['meta_value'] = sanitize_text_field($_GET['module_layout']);
        }
    }
}
add_filter('parse_query', 'process_module_admin_filters');

<?php
/** inc/admin/module-page-integration.php
 * Add modules to page associations
 */
function add_page_modules_meta_box() {
    add_meta_box(
        'page_modules',
        __('Page Modules', 'steget'),
        'render_page_modules_meta_box',
        ['page', 'post'], // Add to both pages and posts
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_page_modules_meta_box');

/**
 * Render page modules meta box
 */
function render_page_modules_meta_box($post) {
    wp_nonce_field('save_page_modules', 'page_modules_nonce');

    $page_modules = json_decode(get_post_meta($post->ID, 'page_modules', true), true) ?: [];

    // Get all available modules
    $modules = get_posts([
        'post_type' => 'module',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    ?>
<p><?php _e('Add and arrange modules for this page:', 'steget'); ?></p>

<div id="page_modules_container">
    <?php if (!empty($page_modules)) : ?>
    <?php foreach ($page_modules as $index => $module) : ?>
    <?php $module_post = get_post($module['id']); ?>
    <?php if ($module_post && $module_post->post_status === 'publish') : ?>
    <div class="module-item" data-id="<?php echo $module['id']; ?>">
        <div class="module-header">
            <span class="module-drag dashicons dashicons-move"></span>
            <strong><?php echo esc_html($module_post->post_title); ?></strong>
            <span class="module-type">(<?php
                                $template = get_post_meta($module['id'], 'module_template', true);
                                $templates = get_module_templates();
                                echo isset($templates[$template]) ? esc_html($templates[$template]) : esc_html($template);
                            ?>)</span>
            <div class="module-actions">
                <a href="<?php echo get_edit_post_link($module['id']); ?>" class="module-edit" target="_blank"
                    title="<?php _e('Edit', 'steget'); ?>">
                    <span class="dashicons dashicons-edit"></span>
                </a>
                <a href="#" class="module-remove" title="<?php _e('Remove', 'steget'); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </a>
            </div>
        </div>
        <div class="module-settings">
            <input type="hidden" name="module_id[]" value="<?php echo $module['id']; ?>">
            <label>
                <input type="checkbox" name="module_override_settings[<?php echo $index; ?>]"
                    <?php checked(isset($module['override_settings']) && $module['override_settings']); ?>>
                <?php _e('Override module settings', 'steget'); ?>
            </label>

            <div
                class="module-override-options <?php echo (isset($module['override_settings']) && $module['override_settings']) ? '' : 'hidden'; ?>">
                <p>
                    <label><?php _e('Layout:', 'steget'); ?></label>
                    <select name="module_layout[<?php echo $index; ?>]">
                        <?php foreach (get_layout_options() as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>"
                            <?php selected(isset($module['layout']) ? $module['layout'] : 'center', $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label>
                        <input type="checkbox" name="module_full_width[<?php echo $index; ?>]"
                            <?php checked(isset($module['full_width']) && $module['full_width']); ?>>
                        <?php _e('Full Width', 'steget'); ?>
                    </label>
                </p>

                <p>
                    <label><?php _e('Background Color:', 'steget'); ?></label>
                    <input type="text" name="module_background_color[<?php echo $index; ?>]"
                        value="<?php echo esc_attr(isset($module['background_color']) ? $module['background_color'] : ''); ?>"
                        class="color-picker">
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="module-selector">
    <select id="module_selector">
        <option value=""><?php _e('Select a module to add', 'steget'); ?></option>
        <?php foreach ($modules as $module) : ?>
        <option value="<?php echo $module->ID; ?>"><?php echo esc_html($module->post_title); ?> (<?php
                    $template = get_post_meta($module->ID, 'module_template', true);
                    $templates = get_module_templates();
                    echo isset($templates[$template]) ? esc_html($templates[$template]) : esc_html($template);
                ?>)</option>
        <?php endforeach; ?>
    </select>
    <button type="button" class="button button-primary" id="add_module"><?php _e('Add Module', 'steget'); ?></button>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize sortable for modules
    $('#page_modules_container').sortable({
        handle: '.module-drag',
        placeholder: 'module-placeholder',
        forcePlaceholderSize: true
    });

    // Add new module
    $('#add_module').on('click', function() {
        var moduleId = $('#module_selector').val();

        if (!moduleId) {
            return;
        }

        var moduleTitle = $('#module_selector option:selected').text();
        var index = $('.module-item').length;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_module_info',
                module_id: moduleId,
                nonce: '<?php echo wp_create_nonce('get_module_info'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var moduleData = response.data;

                    var template = `
                            <div class="module-item" data-id="${moduleData.id}">
                                <div class="module-header">
                                    <span class="module-drag dashicons dashicons-move"></span>
                                    <strong>${moduleData.title}</strong>
                                    <span class="module-type">(${moduleData.template_name})</span>
                                    <div class="module-actions">
                                        <a href="${moduleData.edit_url}" class="module-edit" target="_blank" title="<?php _e('Edit', 'steget'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <a href="#" class="module-remove" title="<?php _e('Remove', 'steget'); ?>">
                                            <span class="dashicons dashicons-no-alt"></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="module-settings">
                                    <input type="hidden" name="module_id[]" value="${moduleData.id}">
                                    <label>
                                        <input type="checkbox" name="module_override_settings[${index}]">
                                        <?php _e('Override module settings', 'steget'); ?>
                                    </label>

                                    <div class="module-override-options hidden">
                                        <p>
                                            <label><?php _e('Layout:', 'steget'); ?></label>
                                            <select name="module_layout[${index}]">
                                                <?php foreach (get_layout_options() as $value => $label) : ?>
                                                    <option value="<?php echo esc_attr($value); ?>" <?php selected('center', $value); ?>>
                                                        <?php echo esc_html($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </p>

                                        <p>
                                            <label>
                                                <input type="checkbox" name="module_full_width[${index}]">
                                                <?php _e('Full Width', 'steget'); ?>
                                            </label>
                                        </p>

                                        <p>
                                            <label><?php _e('Background Color:', 'steget'); ?></label>
                                            <input type="text" name="module_background_color[${index}]" value="" class="color-picker">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;

                    $('#page_modules_container').append(template);

                    // Initialize color picker for the new module
                    $('.color-picker').wpColorPicker();

                    // Reset the selector
                    $('#module_selector').val('');
                }
            }
        });
    });

    // Remove module
    $(document).on('click', '.module-remove', function(e) {
        e.preventDefault();
        $(this).closest('.module-item').remove();

        // Update indices
        updateModuleIndices();
    });

    // Toggle override settings
    $(document).on('change', 'input[name^="module_override_settings"]', function() {
        $(this).closest('.module-settings').find('.module-override-options').toggleClass('hidden', !this
            .checked);
    });

    // Initialize color picker
    $('.color-picker').wpColorPicker();

    // Update module indices when sorting changes
    function updateModuleIndices() {
        $('.module-item').each(function(index) {
            $(this).find('input[name^="module_override_settings"]').attr('name',
                'module_override_settings[' + index + ']');
            $(this).find('select[name^="module_layout"]').attr('name', 'module_layout[' + index + ']');
            $(this).find('input[name^="module_full_width"]').attr('name', 'module_full_width[' + index +
                ']');
            $(this).find('input[name^="module_background_color"]').attr('name',
                'module_background_color[' + index + ']');
        });
    }

    $('#page_modules_container').on('sortupdate', function() {
        updateModuleIndices();
    });
});
</script>

<style type="text/css">
.module-item {
    background: #fff;
    border: 1px solid #ccd0d4;
    margin-bottom: 10px;
}

.module-header {
    padding: 10px;
    background: #f9f9f9;
    border-bottom: 1px solid #ccd0d4;
    display: flex;
    align-items: center;
}

.module-drag {
    cursor: move;
    margin-right: 10px;
}

.module-type {
    color: #888;
    margin-left: 10px;
}

.module-actions {
    margin-left: auto;
}

.module-edit,
.module-remove {
    text-decoration: none;
    margin-left: 5px;
}

.module-settings {
    padding: 10px;
}

.module-override-options {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.module-placeholder {
    background: #f1f1f1;
    border: 1px dashed #ccd0d4;
    height: 40px;
    margin-bottom: 10px;
}

.module-selector {
    margin-top: 15px;
    display: flex;
    align-items: center;
}

.module-selector select {
    flex-grow: 1;
    margin-right: 10px;
}

.hidden {
    display: none;
}
</style>
<?php
}

/**
 * AJAX handler for getting module info
 */
function get_module_info_ajax() {
    check_ajax_referer('get_module_info', 'nonce');

    $module_id = isset($_POST['module_id']) ? intval($_POST['module_id']) : 0;

    if (!$module_id) {
        wp_send_json_error();
    }

    $module = get_post($module_id);

    if (!$module || $module->post_type !== 'module' || $module->post_status !== 'publish') {
        wp_send_json_error();
    }

    $template = get_post_meta($module_id, 'module_template', true);
    $templates = get_module_templates();

    wp_send_json_success([
        'id' => $module->ID,
        'title' => $module->post_title,
        'template' => $template,
        'template_name' => isset($templates[$template]) ? $templates[$template] : $template,
        'edit_url' => get_edit_post_link($module->ID, '')
    ]);
}
add_action('wp_ajax_get_module_info', 'get_module_info_ajax');

/**
 * Save page modules association
 */
function save_page_modules($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify nonce
    if (!isset($_POST['page_modules_nonce']) || !wp_verify_nonce($_POST['page_modules_nonce'], 'save_page_modules')) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save modules data
    if (isset($_POST['module_id']) && is_array($_POST['module_id'])) {
        $modules = [];

        foreach ($_POST['module_id'] as $index => $module_id) {
            $module_data = [
                'id' => intval($module_id)
            ];

            // Check if overriding settings
            if (isset($_POST['module_override_settings'][$index])) {
                $module_data['override_settings'] = true;

                if (isset($_POST['module_layout'][$index])) {
                    $module_data['layout'] = sanitize_text_field($_POST['module_layout'][$index]);
                }

                if (isset($_POST['module_full_width'][$index])) {
                    $module_data['full_width'] = true;
                }

                if (isset($_POST['module_background_color'][$index])) {
                    $module_data['background_color'] = sanitize_text_field($_POST['module_background_color'][$index]);
                }
            }

            $modules[] = $module_data;
        }

        update_post_meta($post_id, 'page_modules', json_encode($modules));
    } else {
        // No modules selected, clear the meta
        delete_post_meta($post_id, 'page_modules');
    }
}
add_action('save_post', 'save_page_modules');


/**
 * Enqueue scripts for page modules meta box
 */
function enqueue_page_modules_scripts($hook) {
    global $post;

    if (!in_array($hook, ['post.php', 'post-new.php'])) {
        return;
    }

    if (!$post || !in_array($post->post_type, ['page', 'post'])) {
        return;
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'enqueue_page_modules_scripts');
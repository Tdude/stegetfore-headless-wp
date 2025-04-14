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
    $tabbed_content = json_decode(get_post_meta($post->ID, 'module_tabbed_content', true), true) ?: [
        ['title' => '', 'content' => '']
    ];
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
                <textarea name="tab_content[]" class="widefat"
                    rows="6"><?php echo esc_textarea($tab['content']); ?></textarea>
            </p>
            <button type="button" class="button remove-tab"><?php _e('Remove Tab', 'steget'); ?></button>
            <hr>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button button-primary add-tab"><?php _e('Add Tab', 'steget'); ?></button>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new tab
        $('.add-tab').on('click', function() {
            var count = $('.tab-item').length + 1;
            var template = `
                        <div class="tab-item">
                            <h4><?php _e('Tab', 'steget'); ?> #${count}</h4>
                            <p>
                                <label><strong><?php _e('Tab Title', 'steget'); ?>:</strong></label><br>
                                <input type="text" name="tab_title[]" value="" class="widefat">
                            </p>
                            <p>
                                <label><strong><?php _e('Tab Content', 'steget'); ?>:</strong></label><br>
                                <textarea name="tab_content[]" class="widefat" rows="6"></textarea>
                            </p>
                            <button type="button" class="button remove-tab"><?php _e('Remove Tab', 'steget'); ?></button>
                            <hr>
                        </div>
                    `;
            $('#tabs_container').append(template);
        });

        // Remove tab
        $(document).on('click', '.remove-tab', function() {
            $(this).closest('.tab-item').remove();

            // Renumber the tabs
            $('.tab-item h4').each(function(index) {
                $(this).text('<?php _e('Tab', 'steget'); ?> #' + (index + 1));
            });
        });
    });
    </script>
</div>
<?php
}

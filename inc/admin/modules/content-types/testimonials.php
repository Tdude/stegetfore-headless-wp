<?php
/**
 * Testimonials module template fields
 * 
 * @package Steget
 */

/**
 * Render testimonials template fields
 */
function render_testimonials_template_fields($post) {
    $testimonials = json_decode(get_post_meta($post->ID, 'module_testimonials', true), true) ?: [
        ['author' => '', 'position' => '', 'text' => '', 'image' => '']
    ];
    ?>
<div id="testimonials_fields" class="template-fields">
    <div id="testimonials_container">
        <?php foreach ($testimonials as $index => $testimonial) : ?>
        <div class="testimonial-item">
            <h4><?php _e('Testimonial', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label><strong><?php _e('Author', 'steget'); ?>:</strong></label><br>
                <input type="text" name="testimonial_author[]"
                    value="<?php echo esc_attr($testimonial['author']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Position', 'steget'); ?>:</strong></label><br>
                <input type="text" name="testimonial_position[]"
                    value="<?php echo esc_attr($testimonial['position']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Text', 'steget'); ?>:</strong></label><br>
                <textarea name="testimonial_text[]" rows="4"
                    class="widefat"><?php echo esc_textarea($testimonial['text']); ?></textarea>
            </p>
            <p>
                <label><strong><?php _e('Image', 'steget'); ?>:</strong></label><br>
            <div class="steget-media-field">
                <input type="hidden" name="testimonial_image[]"
                    value="<?php echo esc_attr($testimonial['image']); ?>" class="steget-media-input" />
                <div class="steget-image-preview" style="max-width: 100px; margin-bottom: 10px;">
                    <?php if (!empty($testimonial['image'])) : ?>
                    <img src="<?php echo esc_url($testimonial['image']); ?>"
                        style="max-width: 100%; height: auto;" />
                    <?php endif; ?>
                </div>
                <button type="button" class="button steget-upload-image"><?php _e('Select Image', 'steget'); ?></button>
                <button type="button" class="button steget-remove-image"
                    <?php echo empty($testimonial['image']) ? 'style="display:none;"' : ''; ?>><?php _e('Remove Image', 'steget'); ?></button>
            </div>
            </p>
            <button type="button" class="button steget-remove-testimonial"><?php _e('Remove', 'steget'); ?></button>
            <hr>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button"
        class="button button-primary add-testimonial"><?php _e('Add Testimonial', 'steget'); ?></button>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new testimonial
        $('.add-testimonial').on('click', function() {
            var count = $('.testimonial-item').length + 1;
            var template = `
                <div class=\"testimonial-item\">\n\
                    <h4><?php _e('Testimonial', 'steget'); ?> #${count}</h4>\n\
                    <p>\n\
                        <label><strong><?php _e('Author', 'steget'); ?>:</strong></label><br>\n\
                        <input type=\"text\" name=\"testimonial_author[]\" value=\"\" class=\"widefat\">\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Position', 'steget'); ?>:</strong></label><br>\n\
                        <input type=\"text\" name=\"testimonial_position[]\" value=\"\" class=\"widefat\">\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Text', 'steget'); ?>:</strong></label><br>\n\
                        <textarea name=\"testimonial_text[]\" rows=\"4\" class=\"widefat\"></textarea>\n\
                    </p>\n\
                    <p>\n\
                        <label><strong><?php _e('Image', 'steget'); ?>:</strong></label><br>\n\
                        <div class=\"steget-media-field\">\n\
                            <input type=\"hidden\" name=\"testimonial_image[]\" value=\"\" class=\"steget-media-input\" />\n\
                            <div class=\"steget-image-preview\" style=\"max-width: 100px; margin-bottom: 10px;\"></div>\n\
                            <button type=\"button\" class=\"button steget-upload-image\"><?php _e('Select Image', 'steget'); ?></button>\n\
                            <button type=\"button\" class=\"button steget-remove-image\" style=\"display:none;\"><?php _e('Remove Image', 'steget'); ?></button>\n\
                        </div>\n\
                    </p>\n\
                    <button type=\"button\" class=\"button steget-remove-testimonial\"><?php _e('Remove', 'steget'); ?></button>\n\
                    <hr>\n\
                </div>\n\
            `;
            $('#testimonials_container').append(template);
        });

        // Remove testimonial
        $(document).on('click', '.steget-remove-testimonial', function() {
            $(this).closest('.testimonial-item').remove();
        });
    });
    </script>
</div>
<?php
}

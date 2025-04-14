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
        ['author_name' => '', 'author_position' => '', 'content' => '', 'author_image' => '']
    ];
    ?>
<div id="testimonials_fields" class="template-fields">
    <div id="testimonials_container">
        <?php foreach ($testimonials as $index => $testimonial) : ?>
        <div class="testimonial-item">
            <h4><?php _e('Testimonial', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label><strong><?php _e('Author Name', 'steget'); ?>:</strong></label><br>
                <input type="text" name="testimonial_author_name[]"
                    value="<?php echo esc_attr($testimonial['author_name']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Author Position', 'steget'); ?>:</strong></label><br>
                <input type="text" name="testimonial_author_position[]"
                    value="<?php echo esc_attr($testimonial['author_position']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Content', 'steget'); ?>:</strong></label><br>
                <textarea name="testimonial_content[]" rows="4"
                    class="widefat"><?php echo esc_textarea($testimonial['content']); ?></textarea>
            </p>
            <p>
                <label><strong><?php _e('Author Image', 'steget'); ?>:</strong></label><br>
            <div class="steget-media-field">
                <input type="hidden" name="testimonial_author_image[]"
                    value="<?php echo esc_attr($testimonial['author_image']); ?>" class="steget-media-input" />
                <div class="steget-image-preview" style="max-width: 100px; margin-bottom: 10px;">
                    <?php if (!empty($testimonial['author_image'])) : ?>
                    <img src="<?php echo esc_url($testimonial['author_image']); ?>"
                        style="max-width: 100%; height: auto;" />
                    <?php endif; ?>
                </div>
                <button type="button" class="button steget-upload-image"><?php _e('Select Image', 'steget'); ?></button>
                <button type="button" class="button steget-remove-image"
                    <?php echo empty($testimonial['author_image']) ? 'style="display:none;"' : ''; ?>><?php _e('Remove Image', 'steget'); ?></button>
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
                <div class="testimonial-item">
                    <h4><?php _e('Testimonial', 'steget'); ?> #${count}</h4>
                    <p>
                        <label><strong><?php _e('Author Name', 'steget'); ?>:</strong></label><br>
                        <input type="text" name="testimonial_author_name[]" value="" class="widefat">
                    </p>
                    <p>
                        <label><strong><?php _e('Author Position', 'steget'); ?>:</strong></label><br>
                        <input type="text" name="testimonial_author_position[]" value="" class="widefat">
                    </p>
                    <p>
                        <label><strong><?php _e('Content', 'steget'); ?>:</strong></label><br>
                        <textarea name="testimonial_content[]" rows="4" class="widefat"></textarea>
                    </p>
                    <p>
                        <label><strong><?php _e('Author Image', 'steget'); ?>:</strong></label><br>
                        <div class="steget-media-field">
                            <input type="hidden" name="testimonial_author_image[]" value="" class="steget-media-input" />
                            <div class="steget-image-preview" style="max-width: 100px; margin-bottom: 10px;"></div>
                            <button type="button" class="button steget-upload-image"><?php _e('Select Image', 'steget'); ?></button>
                            <button type="button" class="button steget-remove-image" style="display:none;"><?php _e('Remove Image', 'steget'); ?></button>
                        </div>
                    </p>
                    <button type="button" class="button steget-remove-testimonial"><?php _e('Remove', 'steget'); ?></button>
                    <hr>
                </div>
            `;
            $('#testimonials_container').append(template);
        });

        // Remove testimonial
        $(document).on('click', '.steget-remove-testimonial', function() {
            $(this).closest('.testimonial-item').remove();

            // Renumber the testimonials
            $('.testimonial-item h4').each(function(index) {
                $(this).text('<?php _e('Testimonial', 'steget'); ?> #' + (index + 1));
            });
        });
    });
    </script>
</div>
<?php
}

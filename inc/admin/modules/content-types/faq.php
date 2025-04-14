<?php
/**
 * FAQ module template fields
 * 
 * @package Steget
 */

/**
 * Render FAQ template fields
 */
function render_faq_template_fields($post) {
    $faq_items = json_decode(get_post_meta($post->ID, 'module_faq_items', true), true) ?: [
        ['question' => '', 'answer' => '']
    ];
    ?>
<div id="faq_fields" class="template-fields">
    <div id="faq_container">
        <?php foreach ($faq_items as $index => $item) : ?>
        <div class="faq-item">
            <h4><?php _e('FAQ Item', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label><strong><?php _e('Question', 'steget'); ?>:</strong></label><br>
                <input type="text" name="faq_question[]" value="<?php echo esc_attr($item['question']); ?>"
                    class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Answer', 'steget'); ?>:</strong></label><br>
                <textarea name="faq_answer[]" class="widefat"
                    rows="4"><?php echo esc_textarea($item['answer']); ?></textarea>
            </p>
            <button type="button" class="button remove-faq"><?php _e('Remove', 'steget'); ?></button>
            <hr>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button button-primary add-faq"><?php _e('Add FAQ Item', 'steget'); ?></button>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new FAQ item
        $('.add-faq').on('click', function() {
            var count = $('.faq-item').length + 1;
            var template = `
                        <div class="faq-item">
                            <h4><?php _e('FAQ Item', 'steget'); ?> #${count}</h4>
                            <p>
                                <label><strong><?php _e('Question', 'steget'); ?>:</strong></label><br>
                                <input type="text" name="faq_question[]" value="" class="widefat">
                            </p>
                            <p>
                                <label><strong><?php _e('Answer', 'steget'); ?>:</strong></label><br>
                                <textarea name="faq_answer[]" class="widefat" rows="4"></textarea>
                            </p>
                            <button type="button" class="button remove-faq"><?php _e('Remove', 'steget'); ?></button>
                            <hr>
                        </div>
                    `;
            $('#faq_container').append(template);
        });

        // Remove FAQ item
        $(document).on('click', '.remove-faq', function() {
            $(this).closest('.faq-item').remove();

            // Renumber the FAQ items
            $('.faq-item h4').each(function(index) {
                $(this).text('<?php _e('FAQ Item', 'steget'); ?> #' + (index + 1));
            });
        });
    });
    </script>
</div>
<?php
}

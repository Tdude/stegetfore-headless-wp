<?php
/**
 * Hero module template fields
 * inc/admin/modules/content-types/hero.php
 * 
 * @package Steget
 */

/**
 * Render hero template fields
 */
function render_hero_template_fields($post) {
    $settings = json_decode(get_post_meta($post->ID, 'module_hero_settings', true), true) ?: [
        'title' => '',
        'subtitle' => '',
        'image' => '',
        'overlay' => false,
        'overlay_opacity' => 50,
        'text_color' => '#ffffff',
        'alignment' => 'center',
        'min_height' => '500'
    ];
    ?>
<div id="hero_fields" class="template-fields">
    <p>
        <label for="hero_title"><strong><?php _e('Hero Title', 'steget'); ?>:</strong></label><br>
        <input type="text" name="hero_title" id="hero_title" value="<?php echo esc_attr($settings['title']); ?>"
            class="widefat">
    </p>

    <p>
        <label for="hero_subtitle"><strong><?php _e('Hero Subtitle', 'steget'); ?>:</strong></label><br>
        <textarea name="hero_subtitle" id="hero_subtitle" class="widefat"
            rows="3"><?php echo esc_textarea($settings['subtitle']); ?></textarea>
    </p>

    <p>
        <label for="hero_image"><strong><?php _e('Background Image', 'steget'); ?>:</strong></label><br>
        <div class="image-preview-wrapper">
            <?php if ($settings['image']) : ?>
            <img src="<?php echo esc_url($settings['image']); ?>" alt=""
                style="max-width: 100%; max-height: 200px; display: block; margin-bottom: 10px;">
            <?php endif; ?>
        </div>
        <input type="hidden" name="hero_image" id="hero_image" value="<?php echo esc_attr($settings['image']); ?>">
        <button type="button" class="button image-upload"
            data-target="#hero_image"><?php _e('Set Image', 'steget'); ?></button>
        <?php if ($settings['image']) : ?>
        <button type="button" class="button image-remove"
            data-target="#hero_image"><?php _e('Remove', 'steget'); ?></button>
        <?php endif; ?>
    </p>

    <p>
        <label for="hero_overlay">
            <input type="checkbox" name="hero_overlay" id="hero_overlay" <?php checked($settings['overlay'], true); ?>>
            <strong><?php _e('Add Overlay', 'steget'); ?></strong>
        </label>
    </p>

    <div class="overlay-settings" <?php echo $settings['overlay'] ? '' : 'style="display: none;"'; ?>>
        <p>
            <label for="hero_overlay_opacity"><strong><?php _e('Overlay Opacity', 'steget'); ?>:</strong></label><br>
            <input type="range" name="hero_overlay_opacity" id="hero_overlay_opacity" min="0" max="100" step="5"
                value="<?php echo esc_attr($settings['overlay_opacity']); ?>">
            <span class="opacity-value"><?php echo esc_html($settings['overlay_opacity']); ?>%</span>
        </p>
    </div>

    <p>
        <label for="hero_text_color"><strong><?php _e('Text Color', 'steget'); ?>:</strong></label><br>
        <input type="text" name="hero_text_color" id="hero_text_color"
            value="<?php echo esc_attr($settings['text_color']); ?>" class="color-picker">
    </p>

    <p>
        <label for="hero_alignment"><strong><?php _e('Content Alignment', 'steget'); ?>:</strong></label><br>
        <select name="hero_alignment" id="hero_alignment" class="widefat">
            <option value="left" <?php selected($settings['alignment'], 'left'); ?>><?php _e('Left', 'steget'); ?>
            </option>
            <option value="center" <?php selected($settings['alignment'], 'center'); ?>><?php _e('Center', 'steget'); ?>
            </option>
            <option value="right" <?php selected($settings['alignment'], 'right'); ?>><?php _e('Right', 'steget'); ?>
            </option>
        </select>
    </p>

    <p>
        <label for="hero_min_height"><strong><?php _e('Minimum Height (px)', 'steget'); ?>:</strong></label><br>
        <input type="number" name="hero_min_height" id="hero_min_height" min="100" step="10"
            value="<?php echo esc_attr($settings['min_height']); ?>" class="small-text">
    </p>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle overlay settings
    $('#hero_overlay').on('change', function() {
        if ($(this).is(':checked')) {
            $('.overlay-settings').show();
        } else {
            $('.overlay-settings').hide();
        }
    });

    // Update opacity value display
    $('#hero_overlay_opacity').on('input', function() {
        $('.opacity-value').text($(this).val() + '%');
    });

    // Media uploader for hero image
    $('.image-upload').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var frame = wp.media({
            title: '<?php _e('Select or Upload Image', 'steget'); ?>',
            button: {
                text: '<?php _e('Use this image', 'steget'); ?>'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $(target).val(attachment.url);
            $(target).closest('p').find('.image-preview-wrapper').html(
                '<img src="' + attachment.url + '" alt="" style="max-width: 100%; max-height: 200px; display: block; margin-bottom: 10px;">'
            );
            $(target).closest('p').find('.image-remove').show();
        });

        frame.open();
    });

    // Remove image
    $('.image-remove').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        $(target).val('');
        $(target).closest('p').find('.image-preview-wrapper').empty();
        $(this).hide();
    });
});
</script>
<?php
}

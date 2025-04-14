<?php
/**
 * Gallery module template fields
 * 
 * @package Steget
 */

/**
 * Render gallery template fields
 */
function render_gallery_template_fields($post) {
    $gallery_ids = json_decode(get_post_meta($post->ID, 'module_gallery_ids', true), true) ?: [];
    $gallery_ids_str = implode(',', $gallery_ids);
    ?>
<div id="gallery_fields" class="template-fields">
    <p>
        <label><strong><?php _e('Gallery Images', 'steget'); ?>:</strong></label><br>
        <input type="hidden" name="gallery_ids" id="gallery_ids" value="<?php echo esc_attr($gallery_ids_str); ?>">
        <button type="button" class="button gallery-upload"><?php _e('Add/Edit Gallery Images', 'steget'); ?></button>
    </p>

    <div id="gallery_preview" class="gallery-preview">
        <?php if (!empty($gallery_ids)) : ?>
        <?php foreach ($gallery_ids as $image_id) :
                    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                    if ($image_url) :
                ?>
        <div class="gallery-image">
            <img src="<?php echo esc_url($image_url); ?>" alt="">
        </div>
        <?php endif; endforeach; ?>
        <?php else : ?>
        <p><?php _e('No images selected.', 'steget'); ?></p>
        <?php endif; ?>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Handle gallery upload
        $('.gallery-upload').on('click', function(e) {
            e.preventDefault();

            var galleryFrame = wp.media({
                title: '<?php _e('Select Gallery Images', 'steget'); ?>',
                button: {
                    text: '<?php _e('Add to Gallery', 'steget'); ?>'
                },
                multiple: true
            });

            galleryFrame.on('select', function() {
                var attachments = galleryFrame.state().get('selection').toJSON();
                var galleryIds = [];
                var galleryPreview = '';

                $.each(attachments, function(i, attachment) {
                    galleryIds.push(attachment.id);
                    if (attachment.sizes && attachment.sizes.thumbnail) {
                        galleryPreview += '<div class="gallery-image"><img src="' +
                            attachment.sizes.thumbnail.url + '" alt=""></div>';
                    }
                });

                $('#gallery_ids').val(galleryIds.join(','));
                if (galleryPreview) {
                    $('#gallery_preview').html(galleryPreview);
                } else {
                    $('#gallery_preview').html(
                        '<p><?php _e('No images selected.', 'steget'); ?></p>');
                }
            });

            galleryFrame.open();
        });
    });
    </script>

    <style type="text/css">
    .gallery-preview {
        display: flex;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .gallery-image {
        width: 80px;
        height: 80px;
        margin: 0 10px 10px 0;
        border: 1px solid #ddd;
        padding: 3px;
    }

    .gallery-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    </style>
</div>
<?php
}

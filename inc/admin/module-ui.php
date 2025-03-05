<?php
/** inc/admin/module-ui.php
 * Admin UI related methods
 *
 * Add meta boxes for module settings
*/
 function add_module_meta_boxes() {
     add_meta_box(
         'module_settings',
         __('Module Settings', 'steget'),
         'render_module_settings_meta_box',
         'module',
         'normal',
         'high'
     );

     add_meta_box(
         'module_template_settings',
         __('Template Settings', 'steget'),
         'render_module_template_settings_meta_box',
         'module',
         'normal',
         'high'
     );

     add_meta_box(
         'module_buttons',
         __('Buttons', 'steget'),
         'render_module_buttons_meta_box',
         'module',
         'normal',
         'default'
     );

     add_meta_box(
         'module_preview',
         __('Module Preview', 'steget'),
         'render_module_preview_meta_box',
         'module',
         'side',
         'default'
     );
 }
 add_action('add_meta_boxes', 'add_module_meta_boxes');

 /**
  * Render core module settings meta box
  */
 function render_module_settings_meta_box($post) {
     wp_nonce_field('save_module_meta', 'module_meta_nonce');

     $template = get_post_meta($post->ID, 'module_template', true);
     $layout = get_post_meta($post->ID, 'module_layout', true) ?: 'center';
     $full_width = get_post_meta($post->ID, 'module_full_width', true);
     $bg_color = get_post_meta($post->ID, 'module_background_color', true);

     $template_options = get_module_templates();
     $layout_options = get_layout_options();
     ?>
<div class="module-settings-panel">
    <p>
        <label for="module_template"><strong><?php _e('Template Type', 'steget'); ?>:</strong></label><br>
        <select name="module_template" id="module_template" class="widefat">
            <option value=""><?php _e('Select a template', 'steget'); ?></option>
            <?php foreach ($template_options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($template, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="module_layout"><strong><?php _e('Layout', 'steget'); ?>:</strong></label><br>
        <select name="module_layout" id="module_layout" class="widefat">
            <?php foreach ($layout_options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($layout, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="module_full_width">
            <input type="checkbox" name="module_full_width" id="module_full_width" <?php checked($full_width, true); ?>>
            <strong><?php _e('Full Width Layout', 'steget'); ?></strong>
        </label>
    </p>

    <p>
        <label for="module_background_color"><strong><?php _e('Background Color', 'steget'); ?>:</strong></label><br>
        <input type="text" name="module_background_color" id="module_background_color"
            value="<?php echo esc_attr($bg_color); ?>" class="color-picker widefat">
        <span
            class="description"><?php _e('Background color for this module (leave empty for default)', 'steget'); ?></span>
    </p>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize color picker
    $('.color-picker').wpColorPicker();

    // Show/hide template specific fields based on template selection
    function toggleTemplateFields() {
        var template = $('#module_template').val();
        $('.template-fields').hide();
        $('#' + template + '_fields').show();
    }

    $('#module_template').on('change', toggleTemplateFields);
    toggleTemplateFields();
});
</script>
<?php
 }

 /**
  * Render template-specific settings meta box
  */
 function render_module_template_settings_meta_box($post) {
     $template = get_post_meta($post->ID, 'module_template', true);

     // Display instructions if no template is selected
     if (!$template) {
         echo '<p>' . __('Please select a template type in the Module Settings box above to see template-specific options.', 'steget') . '</p>';
         return;
     }

     // Get template data based on selected template
     switch ($template) {
         case 'hero':
             render_hero_template_fields($post);
             break;
         case 'selling_points':
             render_selling_points_template_fields($post);
             break;
         case 'stats':
             render_stats_template_fields($post);
             break;
         case 'testimonials':
             render_testimonials_template_fields($post);
             break;
         case 'gallery':
             render_gallery_template_fields($post);
             break;
         case 'faq':
             render_faq_template_fields($post);
             break;
         case 'tabbed_content':
             render_tabbed_content_template_fields($post);
             break;
         case 'charts':
             render_charts_template_fields($post);
             break;
         case 'sharing':
             render_sharing_template_fields($post);
             break;
         case 'login':
             render_login_template_fields($post);
             break;
         case 'payment':
             render_payment_template_fields($post);
             break;
         case 'calendar':
             render_calendar_template_fields($post);
             break;
         case 'cta':
             render_cta_template_fields($post);
             break;
         case 'text_media':
             render_text_media_template_fields($post);
             break;
         case 'video':
             render_video_template_fields($post);
             break;
         case 'form':
             render_form_template_fields($post);
             break;
         default:
             echo '<p>' . __('No additional settings for this template type.', 'steget') . '</p>';
     }
 }

 /**
  * Render hero template fields
  */
 function render_hero_template_fields($post) {
     $hero_settings = json_decode(get_post_meta($post->ID, 'module_hero_settings', true), true) ?: [];
     $overlay_opacity = isset($hero_settings['overlay_opacity']) ? $hero_settings['overlay_opacity'] : 0.3;
     $text_color = isset($hero_settings['text_color']) ? $hero_settings['text_color'] : '#ffffff';
     $height = isset($hero_settings['height']) ? $hero_settings['height'] : 'medium';
     ?>
<div id="hero_fields" class="template-fields">
    <p>
        <label for="hero_height"><strong><?php _e('Hero Height', 'steget'); ?>:</strong></label><br>
        <select name="hero_height" id="hero_height" class="widefat">
            <option value="small" <?php selected($height, 'small'); ?>><?php _e('Small', 'steget'); ?></option>
            <option value="medium" <?php selected($height, 'medium'); ?>><?php _e('Medium', 'steget'); ?></option>
            <option value="large" <?php selected($height, 'large'); ?>><?php _e('Large', 'steget'); ?></option>
            <option value="full" <?php selected($height, 'full'); ?>><?php _e('Full Height', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="hero_overlay_opacity"><strong><?php _e('Overlay Opacity', 'steget'); ?>:</strong></label><br>
        <input type="range" name="hero_overlay_opacity" id="hero_overlay_opacity" min="0" max="1" step="0.1"
            value="<?php echo esc_attr($overlay_opacity); ?>" class="widefat">
        <span class="opacity-value"><?php echo esc_html($overlay_opacity); ?></span>
    </p>

    <p>
        <label for="hero_text_color"><strong><?php _e('Text Color', 'steget'); ?>:</strong></label><br>
        <input type="text" name="hero_text_color" id="hero_text_color" value="<?php echo esc_attr($text_color); ?>"
            class="color-picker widefat">
    </p>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Update opacity value display
        $('#hero_overlay_opacity').on('input', function() {
            $('.opacity-value').text($(this).val());
        });

        // Initialize color picker
        $('.color-picker').wpColorPicker();
    });
    </script>
</div>
<?php
 }

 /**
  * Render selling points template fields
  */
 function render_selling_points_template_fields($post) {
     $selling_points = json_decode(get_post_meta($post->ID, 'module_selling_points', true), true) ?: [
         ['title' => '', 'description' => '', 'icon' => '']
     ];
     ?>
<div id="selling_points_fields" class="template-fields">
    <div id="selling_points_container">
        <?php foreach ($selling_points as $index => $point) : ?>
        <div class="selling-point-item">
            <h4><?php _e('Selling Point', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label><strong><?php _e('Title', 'steget'); ?>:</strong></label><br>
                <input type="text" name="selling_point_title[]" value="<?php echo esc_attr($point['title']); ?>"
                    class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Description', 'steget'); ?>:</strong></label><br>
                <textarea name="selling_point_description[]"
                    class="widefat"><?php echo esc_textarea($point['description']); ?></textarea>
            </p>
            <p>
                <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>
                <input type="text" name="selling_point_icon[]" value="<?php echo esc_attr($point['icon']); ?>"
                    class="widefat">
                <span
                    class="description"><?php _e('Enter icon name (for Font Awesome) or upload image', 'steget'); ?></span>
            </p>
            <button type="button" class="button remove-selling-point"><?php _e('Remove', 'steget'); ?></button>
            <hr>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button"
        class="button button-primary add-selling-point"><?php _e('Add Selling Point', 'steget'); ?></button>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new selling point
        $('.add-selling-point').on('click', function() {
            var count = $('.selling-point-item').length + 1;
            var template = `
                     <div class="selling-point-item">
                         <h4><?php _e('Selling Point', 'steget'); ?> #${count}</h4>
                         <p>
                             <label><strong><?php _e('Title', 'steget'); ?>:</strong></label><br>
                             <input type="text" name="selling_point_title[]" value="" class="widefat">
                         </p>
                         <p>
                             <label><strong><?php _e('Description', 'steget'); ?>:</strong></label><br>
                             <textarea name="selling_point_description[]" class="widefat"></textarea>
                         </p>
                         <p>
                             <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>
                             <input type="text" name="selling_point_icon[]" value="" class="widefat">
                             <span class="description"><?php _e('Enter icon name (for Font Awesome) or upload image', 'steget'); ?></span>
                         </p>
                         <button type="button" class="button remove-selling-point"><?php _e('Remove', 'steget'); ?></button>
                         <hr>
                     </div>
                 `;
            $('#selling_points_container').append(template);
        });

        // Remove selling point
        $(document).on('click', '.remove-selling-point', function() {
            $(this).closest('.selling-point-item').remove();

            // Renumber the selling points
            $('.selling-point-item h4').each(function(index) {
                $(this).text('<?php _e('Selling Point', 'steget'); ?> #' + (index + 1));
            });
        });
    });
    </script>
</div>
<?php
 }

 /**
  * Render stats template fields
  */
 function render_stats_template_fields($post) {
     $stats = json_decode(get_post_meta($post->ID, 'module_stats', true), true) ?: [
         ['value' => '', 'label' => '', 'icon' => '']
     ];
     ?>
<div id="stats_fields" class="template-fields">
    <div id="stats_container">
        <?php foreach ($stats as $index => $stat) : ?>
        <div class="stat-item">
            <h4><?php _e('Statistic', 'steget'); ?> #<?php echo $index + 1; ?></h4>
            <p>
                <label><strong><?php _e('Value', 'steget'); ?>:</strong></label><br>
                <input type="text" name="stat_value[]" value="<?php echo esc_attr($stat['value']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Label', 'steget'); ?>:</strong></label><br>
                <input type="text" name="stat_label[]" value="<?php echo esc_attr($stat['label']); ?>" class="widefat">
            </p>
            <p>
                <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>
                <input type="text" name="stat_icon[]" value="<?php echo esc_attr($stat['icon']); ?>" class="widefat">
                <span class="description"><?php _e('Enter icon name or upload image', 'steget'); ?></span>
            </p>
            <button type="button" class="button remove-stat"><?php _e('Remove', 'steget'); ?></button>
            <hr>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button button-primary add-stat"><?php _e('Add Statistic', 'steget'); ?></button>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new stat
        $('.add-stat').on('click', function() {
            var count = $('.stat-item').length + 1;
            var template = `
                     <div class="stat-item">
                         <h4><?php _e('Statistic', 'steget'); ?> #${count}</h4>
                         <p>
                             <label><strong><?php _e('Value', 'steget'); ?>:</strong></label><br>
                             <input type="text" name="stat_value[]" value="" class="widefat">
                         </p>
                         <p>
                             <label><strong><?php _e('Label', 'steget'); ?>:</strong></label><br>
                             <input type="text" name="stat_label[]" value="" class="widefat">
                         </p>
                         <p>
                             <label><strong><?php _e('Icon', 'steget'); ?>:</strong></label><br>
                             <input type="text" name="stat_icon[]" value="" class="widefat">
                             <span class="description"><?php _e('Enter icon name or upload image', 'steget'); ?></span>
                         </p>
                         <button type="button" class="button remove-stat"><?php _e('Remove', 'steget'); ?></button>
                         <hr>
                     </div>
                 `;
            $('#stats_container').append(template);
        });

        // Remove stat
        $(document).on('click', '.remove-stat', function() {
            $(this).closest('.stat-item').remove();

            // Renumber the stats
            $('.stat-item h4').each(function(index) {
                $(this).text('<?php _e('Statistic', 'steget'); ?> #' + (index + 1));
            });
        });
    });
    </script>
</div>
<?php
 }

 /**
  * Render testimonials template fields
  */
 function render_testimonials_template_fields($post) {
     $testimonial_ids = json_decode(get_post_meta($post->ID, 'module_testimonials_ids', true), true) ?: [];
     $testimonials = get_posts([
         'post_type' => 'testimonial',
         'posts_per_page' => -1,
         'post_status' => 'publish'
     ]);
     ?>
<div id="testimonials_fields" class="template-fields">
    <p>
        <label><strong><?php _e('Select Testimonials', 'steget'); ?>:</strong></label><br>
        <select name="testimonial_ids[]" multiple="multiple" class="testimonial-select widefat"
            style="min-height: 150px;">
            <?php foreach ($testimonials as $testimonial) : ?>
            <option value="<?php echo esc_attr($testimonial->ID); ?>"
                <?php echo in_array($testimonial->ID, $testimonial_ids) ? 'selected' : ''; ?>>
                <?php echo esc_html($testimonial->post_title); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <span class="description"><?php _e('Hold Ctrl/Cmd to select multiple testimonials', 'steget'); ?></span>
    </p>

    <p>
        <a href="<?php echo admin_url('post-new.php?post_type=testimonial'); ?>" class="button" target="_blank">
            <?php _e('Add New Testimonial', 'steget'); ?>
        </a>
    </p>
</div>
<?php
 }

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

 /**
  * Render charts template fields
  */
 function render_charts_template_fields($post) {
     $chart_type = get_post_meta($post->ID, 'module_chart_type', true) ?: 'bar';
     $chart_data = json_decode(get_post_meta($post->ID, 'module_chart_data', true), true) ?: [
         'labels' => ['', ''],
         'datasets' => [
             [
                 'label' => '',
                 'data' => [0, 0]
             ]
         ]
     ];
     ?>
<div id="charts_fields" class="template-fields">
    <p>
        <label for="chart_type"><strong><?php _e('Chart Type', 'steget'); ?>:</strong></label><br>
        <select name="chart_type" id="chart_type" class="widefat">
            <option value="bar" <?php selected($chart_type, 'bar'); ?>><?php _e('Bar Chart', 'steget'); ?></option>
            <option value="line" <?php selected($chart_type, 'line'); ?>><?php _e('Line Chart', 'steget'); ?></option>
            <option value="pie" <?php selected($chart_type, 'pie'); ?>><?php _e('Pie Chart', 'steget'); ?></option>
            <option value="doughnut" <?php selected($chart_type, 'doughnut'); ?>>
                <?php _e('Doughnut Chart', 'steget'); ?></option>
            <option value="radar" <?php selected($chart_type, 'radar'); ?>><?php _e('Radar Chart', 'steget'); ?>
            </option>
        </select>
    </p>

    <div class="chart-data-container">
        <h4><?php _e('Chart Data', 'steget'); ?></h4>

        <div class="chart-labels">
            <h5><?php _e('Labels', 'steget'); ?></h5>
            <div id="labels_container">
                <?php foreach ($chart_data['labels'] as $index => $label) : ?>
                <div class="label-row">
                    <input type="text" name="chart_label[]" value="<?php echo esc_attr($label); ?>"
                        placeholder="<?php _e('Label', 'steget'); ?>" class="widefat">
                    <button type="button" class="button remove-label"><?php _e('Remove', 'steget'); ?></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button add-label"><?php _e('Add Label', 'steget'); ?></button>
        </div>

        <div class="chart-datasets">
            <h5><?php _e('Datasets', 'steget'); ?></h5>
            <div id="datasets_container">
                <?php foreach ($chart_data['datasets'] as $datasetIndex => $dataset) : ?>
                <div class="dataset-container">
                    <h6><?php _e('Dataset', 'steget'); ?> #<?php echo $datasetIndex + 1; ?></h6>
                    <p>
                        <label><strong><?php _e('Dataset Label', 'steget'); ?>:</strong></label>
                        <input type="text" name="dataset_label[]" value="<?php echo esc_attr($dataset['label']); ?>"
                            class="widefat">
                    </p>

                    <div class="dataset-values">
                        <h6><?php _e('Values', 'steget'); ?></h6>
                        <div class="values-container" data-dataset="<?php echo $datasetIndex; ?>">
                            <?php foreach ($dataset['data'] as $valueIndex => $value) : ?>
                            <div class="value-row">
                                <input type="number" name="dataset_value[<?php echo $datasetIndex; ?>][]"
                                    value="<?php echo esc_attr($value); ?>" class="widefat">
                                <button type="button"
                                    class="button remove-value"><?php _e('Remove', 'steget'); ?></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button add-value"
                            data-dataset="<?php echo $datasetIndex; ?>"><?php _e('Add Value', 'steget'); ?></button>
                    </div>

                    <button type="button"
                        class="button remove-dataset"><?php _e('Remove Dataset', 'steget'); ?></button>
                    <hr>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button"
                class="button button-primary add-dataset"><?php _e('Add Dataset', 'steget'); ?></button>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add new label
        $('.add-label').on('click', function() {
            var template = `
                        <div class="label-row">
                            <input type="text" name="chart_label[]" value="" placeholder="<?php _e('Label', 'steget'); ?>" class="widefat">
                            <button type="button" class="button remove-label"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $('#labels_container').append(template);
        });

        // Remove label
        $(document).on('click', '.remove-label', function() {
            $(this).closest('.label-row').remove();
        });

        // Add new dataset
        $('.add-dataset').on('click', function() {
            var datasetCount = $('.dataset-container').length;
            var template = `
                        <div class="dataset-container">
                            <h6><?php _e('Dataset', 'steget'); ?> #${datasetCount + 1}</h6>
                            <p>
                                <label><strong><?php _e('Dataset Label', 'steget'); ?>:</strong></label>
                                <input type="text" name="dataset_label[]" value="" class="widefat">
                            </p>

                            <div class="dataset-values">
                                <h6><?php _e('Values', 'steget'); ?></h6>
                                <div class="values-container" data-dataset="${datasetCount}">
                                    <div class="value-row">
                                        <input type="number" name="dataset_value[${datasetCount}][]" value="0" class="widefat">
                                        <button type="button" class="button remove-value"><?php _e('Remove', 'steget'); ?></button>
                                    </div>
                                </div>
                                <button type="button" class="button add-value" data-dataset="${datasetCount}"><?php _e('Add Value', 'steget'); ?></button>
                            </div>

                            <button type="button" class="button remove-dataset"><?php _e('Remove Dataset', 'steget'); ?></button>
                            <hr>
                        </div>
                    `;
            $('#datasets_container').append(template);
        });

        // Remove dataset
        $(document).on('click', '.remove-dataset', function() {
            $(this).closest('.dataset-container').remove();

            // Renumber the datasets
            $('.dataset-container h6:first-child').each(function(index) {
                $(this).text('<?php _e('Dataset', 'steget'); ?> #' + (index + 1));
            });

            // Update dataset indices
            $('.values-container').each(function(index) {
                $(this).attr('data-dataset', index);
                $(this).find('input[type="number"]').each(function() {
                    $(this).attr('name', 'dataset_value[' + index + '][]');
                });
            });

            $('.add-value').each(function(index) {
                $(this).attr('data-dataset', index);
            });
        });

        // Add new value
        $(document).on('click', '.add-value', function() {
            var dataset = $(this).data('dataset');
            var template = `
                        <div class="value-row">
                            <input type="number" name="dataset_value[${dataset}][]" value="0" class="widefat">
                            <button type="button" class="button remove-value"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $(this).prev('.values-container').append(template);
        });

        // Remove value
        $(document).on('click', '.remove-value', function() {
            $(this).closest('.value-row').remove();
        });
    });
    </script>

    <style type="text/css">
    .label-row,
    .value-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .label-row .button,
    .value-row .button {
        margin-left: 10px;
    }

    .chart-labels,
    .chart-datasets {
        margin-bottom: 20px;
    }

    .dataset-container {
        background: #f9f9f9;
        padding: 10px;
        border: 1px solid #e5e5e5;
        margin-bottom: 15px;
    }

    .dataset-values {
        margin-top: 15px;
    }
    </style>
</div>
<?php
 }

 /**
  * Render sharing template fields
  */
 function render_sharing_template_fields($post) {
     $networks = json_decode(get_post_meta($post->ID, 'module_sharing_networks', true), true) ?: [
         'facebook' => true,
         'twitter' => true,
         'linkedin' => true,
         'email' => true
     ];
     ?>
<div id="sharing_fields" class="template-fields">
    <p><?php _e('Select social networks to display:', 'steget'); ?></p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_facebook"
                <?php checked(isset($networks['facebook']) && $networks['facebook']); ?>>
            <?php _e('Facebook', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_twitter"
                <?php checked(isset($networks['twitter']) && $networks['twitter']); ?>>
            <?php _e('Twitter', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_linkedin"
                <?php checked(isset($networks['linkedin']) && $networks['linkedin']); ?>>
            <?php _e('LinkedIn', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_pinterest"
                <?php checked(isset($networks['pinterest']) && $networks['pinterest']); ?>>
            <?php _e('Pinterest', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_email"
                <?php checked(isset($networks['email']) && $networks['email']); ?>>
            <?php _e('Email', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="sharing_network_whatsapp"
                <?php checked(isset($networks['whatsapp']) && $networks['whatsapp']); ?>>
            <?php _e('WhatsApp', 'steget'); ?>
        </label>
    </p>
</div>
<?php
 }

 /**
  * Render login template fields
  */
 function render_login_template_fields($post) {
     $login_settings = json_decode(get_post_meta($post->ID, 'module_login_settings', true), true) ?: [
         'redirect_url' => '',
         'show_register' => true,
         'show_lost_password' => true
     ];
     ?>
<div id="login_fields" class="template-fields">
    <p>
        <label for="login_redirect_url"><strong><?php _e('Redirect URL after login', 'steget'); ?>:</strong></label><br>
        <input type="url" name="login_redirect_url" id="login_redirect_url"
            value="<?php echo esc_url($login_settings['redirect_url']); ?>" class="widefat">
        <span class="description"><?php _e('Leave empty to redirect to the homepage', 'steget'); ?></span>
    </p>

    <p>
        <label>
            <input type="checkbox" name="login_show_register"
                <?php checked(isset($login_settings['show_register']) && $login_settings['show_register']); ?>>
            <?php _e('Show registration link', 'steget'); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="login_show_lost_password"
                <?php checked(isset($login_settings['show_lost_password']) && $login_settings['show_lost_password']); ?>>
            <?php _e('Show lost password link', 'steget'); ?>
        </label>
    </p>
</div>
<?php
 }

 /**
  * Render payment template fields
  */
 function render_payment_template_fields($post) {
     $payment_settings = json_decode(get_post_meta($post->ID, 'module_payment_settings', true), true) ?: [
         'payment_type' => 'stripe',
         'product_id' => '',
         'amount' => '',
         'currency' => 'SEK',
         'success_url' => '',
         'cancel_url' => ''
     ];
     ?>
<div id="payment_fields" class="template-fields">
    <p>
        <label for="payment_type"><strong><?php _e('Payment Gateway', 'steget'); ?>:</strong></label><br>
        <select name="payment_type" id="payment_type" class="widefat">
            <option value="stripe" <?php selected($payment_settings['payment_type'], 'stripe'); ?>>
                <?php _e('Stripe', 'steget'); ?></option>
            <option value="swish" <?php selected($payment_settings['payment_type'], 'swish'); ?>>
                <?php _e('Swish', 'steget'); ?></option>
            <option value="paypal" <?php selected($payment_settings['payment_type'], 'paypal'); ?>>
                <?php _e('PayPal', 'steget'); ?></option>
            <option value="klarna" <?php selected($payment_settings['payment_type'], 'klarna'); ?>>
                <?php _e('Klarna', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="payment_product_id"><strong><?php _e('Product ID', 'steget'); ?>:</strong></label><br>
        <input type="text" name="payment_product_id" id="payment_product_id"
            value="<?php echo esc_attr($payment_settings['product_id']); ?>" class="widefat">
        <span class="description"><?php _e('If using a specific product/service', 'steget'); ?></span>
    </p>

    <p>
        <label for="payment_amount"><strong><?php _e('Amount', 'steget'); ?>:</strong></label><br>
        <input type="number" name="payment_amount" id="payment_amount"
            value="<?php echo esc_attr($payment_settings['amount']); ?>" class="widefat">
        <span class="description"><?php _e('Leave empty if product-based pricing', 'steget'); ?></span>
    </p>

    <p>
        <label for="payment_currency"><strong><?php _e('Currency', 'steget'); ?>:</strong></label><br>
        <select name="payment_currency" id="payment_currency" class="widefat">
            <option value="SEK" <?php selected($payment_settings['currency'], 'SEK'); ?>>
                <?php _e('Swedish Krona (SEK)', 'steget'); ?></option>
            <option value="EUR" <?php selected($payment_settings['currency'], 'EUR'); ?>>
                <?php _e('Euro (EUR)', 'steget'); ?></option>
            <option value="USD" <?php selected($payment_settings['currency'], 'USD'); ?>>
                <?php _e('US Dollar (USD)', 'steget'); ?></option>
            <option value="GBP" <?php selected($payment_settings['currency'], 'GBP'); ?>>
                <?php _e('British Pound (GBP)', 'steget'); ?></option>
            <option value="DKK" <?php selected($payment_settings['currency'], 'DKK'); ?>>
                <?php _e('Danish Krone (DKK)', 'steget'); ?></option>
            <option value="NOK" <?php selected($payment_settings['currency'], 'NOK'); ?>>
                <?php _e('Norwegian Krone (NOK)', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="payment_success_url"><strong><?php _e('Success URL', 'steget'); ?>:</strong></label><br>
        <input type="url" name="payment_success_url" id="payment_success_url"
            value="<?php echo esc_url($payment_settings['success_url']); ?>" class="widefat">
        <span class="description"><?php _e('Redirect URL after successful payment', 'steget'); ?></span>
    </p>

    <p>
        <label for="payment_cancel_url"><strong><?php _e('Cancel URL', 'steget'); ?>:</strong></label><br>
        <input type="url" name="payment_cancel_url" id="payment_cancel_url"
            value="<?php echo esc_url($payment_settings['cancel_url']); ?>" class="widefat">
        <span class="description"><?php _e('Redirect URL if payment is canceled', 'steget'); ?></span>
    </p>
</div>
<?php
 }

 /**
  * Render calendar template fields
  */
 function render_calendar_template_fields($post) {
     $calendar_settings = json_decode(get_post_meta($post->ID, 'module_calendar_settings', true), true) ?: [
         'calendar_type' => 'date_picker',
         'min_date' => '',
         'max_date' => '',
         'disabled_dates' => [],
         'available_times' => []
     ];
     ?>
<div id="calendar_fields" class="template-fields">
    <p>
        <label for="calendar_type"><strong><?php _e('Calendar Type', 'steget'); ?>:</strong></label><br>
        <select name="calendar_type" id="calendar_type" class="widefat">
            <option value="date_picker" <?php selected($calendar_settings['calendar_type'], 'date_picker'); ?>>
                <?php _e('Date Picker', 'steget'); ?></option>
            <option value="date_range" <?php selected($calendar_settings['calendar_type'], 'date_range'); ?>>
                <?php _e('Date Range Picker', 'steget'); ?></option>
            <option value="booking" <?php selected($calendar_settings['calendar_type'], 'booking'); ?>>
                <?php _e('Booking Calendar', 'steget'); ?></option>
            <option value="event" <?php selected($calendar_settings['calendar_type'], 'event'); ?>>
                <?php _e('Event Calendar', 'steget'); ?></option>
        </select>
    </p>

    <p>
        <label for="calendar_min_date"><strong><?php _e('Minimum Date', 'steget'); ?>:</strong></label><br>
        <input type="date" name="calendar_min_date" id="calendar_min_date"
            value="<?php echo esc_attr($calendar_settings['min_date']); ?>" class="widefat">
        <span
            class="description"><?php _e('Earliest selectable date (leave empty for no restriction)', 'steget'); ?></span>
    </p>

    <p>
        <label for="calendar_max_date"><strong><?php _e('Maximum Date', 'steget'); ?>:</strong></label><br>
        <input type="date" name="calendar_max_date" id="calendar_max_date"
            value="<?php echo esc_attr($calendar_settings['max_date']); ?>" class="widefat">
        <span
            class="description"><?php _e('Latest selectable date (leave empty for no restriction)', 'steget'); ?></span>
    </p>

    <div id="calendar_disabled_dates">
        <h4><?php _e('Disabled Dates', 'steget'); ?></h4>
        <div id="disabled_dates_container">
            <?php foreach ($calendar_settings['disabled_dates'] as $index => $date) : ?>
            <div class="disabled-date-row">
                <input type="date" name="calendar_disabled_date[]" value="<?php echo esc_attr($date); ?>"
                    class="widefat">
                <button type="button" class="button remove-disabled-date"><?php _e('Remove', 'steget'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-disabled-date"><?php _e('Add Disabled Date', 'steget'); ?></button>
    </div>

    <div id="calendar_booking_times"
        class="<?php echo $calendar_settings['calendar_type'] === 'booking' ? '' : 'hidden'; ?>">
        <h4><?php _e('Available Times', 'steget'); ?></h4>
        <div id="available_times_container">
            <?php foreach ($calendar_settings['available_times'] as $index => $time) : ?>
            <div class="available-time-row">
                <input type="time" name="calendar_available_time[]" value="<?php echo esc_attr($time); ?>"
                    class="widefat">
                <button type="button" class="button remove-available-time"><?php _e('Remove', 'steget'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-available-time"><?php _e('Add Available Time', 'steget'); ?></button>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Show/hide booking times based on calendar type
        $('#calendar_type').on('change', function() {
            if ($(this).val() === 'booking') {
                $('#calendar_booking_times').removeClass('hidden');
            } else {
                $('#calendar_booking_times').addClass('hidden');
            }
        });

        // Add disabled date
        $('.add-disabled-date').on('click', function() {
            var template = `
                        <div class="disabled-date-row">
                            <input type="date" name="calendar_disabled_date[]" value="" class="widefat">
                            <button type="button" class="button remove-disabled-date"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $('#disabled_dates_container').append(template);
        });

        // Remove disabled date
        $(document).on('click', '.remove-disabled-date', function() {
            $(this).closest('.disabled-date-row').remove();
        });

        // Add available time
        $('.add-available-time').on('click', function() {
            var template = `
                        <div class="available-time-row">
                            <input type="time" name="calendar_available_time[]" value="" class="widefat">
                            <button type="button" class="button remove-available-time"><?php _e('Remove', 'steget'); ?></button>
                        </div>
                    `;
            $('#available_times_container').append(template);
        });

        // Remove available time
        $(document).on('click', '.remove-available-time', function() {
            $(this).closest('.available-time-row').remove();
        });
    });
    </script>

    <style type="text/css">
    .disabled-date-row,
    .available-time-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .disabled-date-row .button,
    .available-time-row .button {
        margin-left: 10px;
    }

    .hidden {
        display: none;
    }

    #calendar_disabled_dates,
    #calendar_booking_times {
        margin-top: 20px;
    }
    </style>
</div>
<?php
 }

 /**
  * Render video template fields
  */
 function render_video_template_fields($post) {
     $video_url = get_post_meta($post->ID, 'module_video_url', true);
     ?>
<div id="video_fields" class="template-fields">
    <p>
        <label for="video_url"><strong><?php _e('Video URL', 'steget'); ?>:</strong></label><br>
        <input type="url" name="video_url" id="video_url" value="<?php echo esc_url($video_url); ?>" class="widefat">
        <span class="description"><?php _e('YouTube, Vimeo, or direct video file URL', 'steget'); ?></span>
    </p>

    <div id="video_preview">
        <?php if ($video_url) : ?>
        <?php
                    // Check if YouTube video
                    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $video_url, $matches) ||
                        preg_match('/youtu\.be\/([^&]+)/', $video_url, $matches)) {
                        $youtube_id = $matches[1];
                        echo '<div class="video-preview-container">';
                        echo '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' . esc_attr($youtube_id) . '" frameborder="0" allowfullscreen></iframe>';
                        echo '</div>';
                    }
                    // Check if Vimeo video
                    elseif (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                        $vimeo_id = $matches[1];
                        echo '<div class="video-preview-container">';
                        echo '<iframe width="100%" height="200" src="https://player.vimeo.com/video/' . esc_attr($vimeo_id) . '" frameborder="0" allowfullscreen></iframe>';
                        echo '</div>';
                    }
                    // Direct video file
                    elseif (preg_match('/\.(mp4|webm|ogg)$/i', $video_url)) {
                        echo '<div class="video-preview-container">';
                        echo '<video width="100%" height="200" controls><source src="' . esc_url($video_url) . '" type="video/' . pathinfo($video_url, PATHINFO_EXTENSION) . '">Your browser does not support the video tag.</video>';
                        echo '</div>';
                    }
                    else {
                        echo '<p>' . __('Preview not available for this video URL format.', 'steget') . '</p>';
                    }
                    ?>
        <?php else : ?>
        <p><?php _e('Enter a video URL to see a preview.', 'steget'); ?></p>
        <?php endif; ?>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#video_url').on('change', function() {
            // Update preview on save
            $('#publish').click();
        });
    });
    </script>

    <style type="text/css">
    .video-preview-container {
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 10px;
        background: #f9f9f9;
    }
    </style>
</div>
<?php
 }

 /**
  * Render contact form template fields
  */
 function render_form_template_fields($post) {
     $form_id = get_post_meta($post->ID, 'module_form_id', true);
     // Get available forms (assuming Contact Form 7 is installed)
     $forms = [];
     if (class_exists('WPCF7_ContactForm')) {
         $forms = WPCF7_ContactForm::find();
     }
     ?>
<div id="form_fields" class="template-fields">
    <?php if (!empty($forms)) : ?>
    <p>
        <label for="form_id"><strong><?php _e('Select Form', 'steget'); ?>:</strong></label><br>
        <select name="form_id" id="form_id" class="widefat">
            <option value=""><?php _e('Select a form', 'steget'); ?></option>
            <?php foreach ($forms as $cf7_form) : ?>
            <option value="<?php echo esc_attr($cf7_form->id()); ?>" <?php selected($form_id, $cf7_form->id()); ?>>
                <?php echo esc_html($cf7_form->title()); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <a href="<?php echo admin_url('admin.php?page=wpcf7-new'); ?>" class="button" target="_blank">
            <?php _e('Create New Form', 'steget'); ?>
        </a>
    </p>
    <?php else : ?>
    <p>
        <?php
                    if (class_exists('WPCF7_ContactForm')) {
                        _e('No forms found. Please create a form first.', 'steget');
                    } else {
                        _e('Contact Form 7 plugin is not active. Please install and activate it to use form modules.', 'steget');
                    }
                    ?>
    </p>
    <?php if (!class_exists('WPCF7_ContactForm')) : ?>
    <p>
        <a href="<?php echo admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term'); ?>" class="button"
            target="_blank">
            <?php _e('Install Contact Form 7', 'steget'); ?>
        </a>
    </p>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php
 }

 /**
  * Render CTA template fields
  */
 function render_cta_template_fields($post) {
     // No additional fields needed as the main content and buttons are handled by the main editor and the buttons metabox
     ?>
<div id="cta_fields" class="template-fields">
    <p><?php _e('The Call to Action module uses the main content editor for text content and the Buttons section below for action buttons.', 'steget'); ?>
    </p>
</div>
<?php
 }

 /**
  * Render text with media template fields
  */
 function render_text_media_template_fields($post) {
     // No additional fields needed as the main content and featured image are handled by the main editor and featured image
     ?>
<div id="text_media_fields" class="template-fields">
    <p><?php _e('The Text with Media module uses the main content editor for text content and the Featured Image for media content.', 'steget'); ?>
    </p>
    <p><?php _e('You can configure the layout (text on left or right) in the Module Settings section.', 'steget'); ?>
    </p>
</div>

<?php
 }
 /**
 * Render module buttons meta box
 */
function render_module_buttons_meta_box($post) {
    $buttons = json_decode(get_post_meta($post->ID, 'module_buttons', true), true) ?: [];
    $button_styles = get_button_styles();
    ?>
<div id="module_buttons_container">
    <?php if (!empty($buttons)) : ?>
    <?php foreach ($buttons as $index => $button) : ?>
    <div class="button-item">
        <h4><?php _e('Button', 'steget'); ?> #<?php echo $index + 1; ?></h4>
        <p>
            <label><strong><?php _e('Button Text', 'steget'); ?>:</strong></label><br>
            <input type="text" name="button_text[]" value="<?php echo esc_attr($button['text']); ?>" class="widefat">
        </p>
        <p>
            <label><strong><?php _e('Button URL', 'steget'); ?>:</strong></label><br>
            <input type="url" name="button_url[]" value="<?php echo esc_url($button['url']); ?>" class="widefat">
        </p>
        <p>
            <label><strong><?php _e('Button Style', 'steget'); ?>:</strong></label><br>
            <select name="button_style[]" class="widefat">
                <?php foreach ($button_styles as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"
                    <?php selected(isset($button['style']) ? $button['style'] : 'primary', $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>
                <input type="checkbox" name="button_new_tab[<?php echo $index; ?>]"
                    <?php checked(isset($button['new_tab']) && $button['new_tab']); ?>>
                <?php _e('Open in new tab', 'steget'); ?>
            </label>
        </p>
        <button type="button" class="button remove-button"><?php _e('Remove Button', 'steget'); ?></button>
        <hr>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<button type="button" class="button button-primary add-button"><?php _e('Add Button', 'steget'); ?></button>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // More JavaScript code for button functionality...
});
</script>
<?php
}
/**
 * Render module preview meta box
 */
function render_module_preview_meta_box($post) {
    $template = get_post_meta($post->ID, 'module_template', true);
    ?>
<div class="module-preview">
    <?php if ($template) : ?>
    <p><?php _e('Preview will be available after saving.', 'steget'); ?></p>
    <?php if (isset($_GET['post'])) : ?>
    <p>
        <a href="<?php echo esc_url(get_preview_post_link($post->ID)); ?>" target="_blank"
            class="button"><?php _e('View Module Preview', 'steget'); ?></a>
    </p>
    <?php endif; ?>
    <?php else : ?>
    <p><?php _e('Please select a template type to enable preview.', 'steget'); ?></p>
    <?php endif; ?>
</div>
<?php
}

/**
 * Save module meta data
 */
function save_module_meta($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify nonce
    if (!isset($_POST['module_meta_nonce']) || !wp_verify_nonce($_POST['module_meta_nonce'], 'save_module_meta')) {
        return;
    }

    // Check permissions
    if ('module' === $_POST['post_type'] && !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save core module settings
    if (isset($_POST['module_template'])) {
        update_post_meta($post_id, 'module_template', sanitize_text_field($_POST['module_template']));
    }

    if (isset($_POST['module_layout'])) {
        update_post_meta($post_id, 'module_layout', sanitize_text_field($_POST['module_layout']));
    }

    if (isset($_POST['module_full_width'])) {
        update_post_meta($post_id, 'module_full_width', true);
    } else {
        update_post_meta($post_id, 'module_full_width', false);
    }

    if (isset($_POST['module_background_color'])) {
        update_post_meta($post_id, 'module_background_color', sanitize_text_field($_POST['module_background_color']));
    }

    // Save buttons
    if (isset($_POST['button_text']) && is_array($_POST['button_text'])) {
        $buttons = [];
        for ($i = 0; $i < count($_POST['button_text']); $i++) {
            if (!empty($_POST['button_text'][$i])) {
                $buttons[] = [
                    'text' => sanitize_text_field($_POST['button_text'][$i]),
                    'url' => esc_url_raw($_POST['button_url'][$i]),
                    'style' => sanitize_text_field($_POST['button_style'][$i]),
                    'new_tab' => isset($_POST['button_new_tab'][$i]),
                ];
            }
        }
        update_post_meta($post_id, 'module_buttons', json_encode($buttons));
    }

    // Save template-specific fields based on selected template
    $template = sanitize_text_field($_POST['module_template']);

    switch ($template) {
        case 'hero':
            $hero_settings = [
                'height' => sanitize_text_field($_POST['hero_height']),
                'overlay_opacity' => (float) $_POST['hero_overlay_opacity'],
                'text_color' => sanitize_text_field($_POST['hero_text_color'])
            ];
            update_post_meta($post_id, 'module_hero_settings', json_encode($hero_settings));
            break;

        case 'selling_points':
            $selling_points = [];
            if (isset($_POST['selling_point_title']) && is_array($_POST['selling_point_title'])) {
                for ($i = 0; $i < count($_POST['selling_point_title']); $i++) {
                    if (!empty($_POST['selling_point_title'][$i])) {
                        $selling_points[] = [
                            'title' => sanitize_text_field($_POST['selling_point_title'][$i]),
                            'description' => sanitize_textarea_field($_POST['selling_point_description'][$i]),
                            'icon' => sanitize_text_field($_POST['selling_point_icon'][$i])
                        ];
                    }
                }
            }
            update_post_meta($post_id, 'module_selling_points', json_encode($selling_points));
            break;

        case 'stats':
            $stats = [];
            if (isset($_POST['stat_value']) && is_array($_POST['stat_value'])) {
                for ($i = 0; $i < count($_POST['stat_value']); $i++) {
                    if (!empty($_POST['stat_value'][$i]) || !empty($_POST['stat_label'][$i])) {
                        $stats[] = [
                            'value' => sanitize_text_field($_POST['stat_value'][$i]),
                            'label' => sanitize_text_field($_POST['stat_label'][$i]),
                            'icon' => sanitize_text_field($_POST['stat_icon'][$i])
                        ];
                    }
                }
            }
            update_post_meta($post_id, 'module_stats', json_encode($stats));
            break;

        case 'testimonials':
            $testimonial_ids = isset($_POST['testimonial_ids']) ? array_map('intval', $_POST['testimonial_ids']) : [];
            update_post_meta($post_id, 'module_testimonials_ids', json_encode($testimonial_ids));
            break;

        case 'gallery':
            $gallery_ids = [];
            if (isset($_POST['gallery_ids']) && !empty($_POST['gallery_ids'])) {
                $gallery_ids = explode(',', sanitize_text_field($_POST['gallery_ids']));
                $gallery_ids = array_map('intval', $gallery_ids);
            }
            update_post_meta($post_id, 'module_gallery_ids', json_encode($gallery_ids));
            break;

        case 'faq':
            $faq_items = [];
            if (isset($_POST['faq_question']) && is_array($_POST['faq_question'])) {
                for ($i = 0; $i < count($_POST['faq_question']); $i++) {
                    if (!empty($_POST['faq_question'][$i])) {
                        $faq_items[] = [
                            'question' => sanitize_text_field($_POST['faq_question'][$i]),
                            'answer' => sanitize_textarea_field($_POST['faq_answer'][$i])
                        ];
                    }
                }
            }
            update_post_meta($post_id, 'module_faq_items', json_encode($faq_items));
            break;

        case 'tabbed_content':
            $tabs = [];
            if (isset($_POST['tab_title']) && is_array($_POST['tab_title'])) {
                for ($i = 0; $i < count($_POST['tab_title']); $i++) {
                    if (!empty($_POST['tab_title'][$i])) {
                        $tabs[] = [
                            'title' => sanitize_text_field($_POST['tab_title'][$i]),
                            'content' => sanitize_textarea_field($_POST['tab_content'][$i])
                        ];
                    }
                }
            }
            update_post_meta($post_id, 'module_tabbed_content', json_encode($tabs));
            break;

        case 'charts':
            // Save chart type
            update_post_meta($post_id, 'module_chart_type', sanitize_text_field($_POST['chart_type']));

            // Save chart data
            $labels = isset($_POST['chart_label']) ? array_map('sanitize_text_field', $_POST['chart_label']) : [];

            $datasets = [];
            if (isset($_POST['dataset_label']) && is_array($_POST['dataset_label'])) {
                for ($i = 0; $i < count($_POST['dataset_label']); $i++) {
                    $data = isset($_POST['dataset_value'][$i]) ? array_map('floatval', $_POST['dataset_value'][$i]) : [];

                    $datasets[] = [
                        'label' => sanitize_text_field($_POST['dataset_label'][$i]),
                        'data' => $data
                    ];
                }
            }

            $chart_data = [
                'labels' => $labels,
                'datasets' => $datasets
            ];

            update_post_meta($post_id, 'module_chart_data', json_encode($chart_data));
            break;

        case 'sharing':
            $networks = [
                'facebook' => isset($_POST['sharing_network_facebook']),
                'twitter' => isset($_POST['sharing_network_twitter']),
                'linkedin' => isset($_POST['sharing_network_linkedin']),
                'pinterest' => isset($_POST['sharing_network_pinterest']),
                'email' => isset($_POST['sharing_network_email']),
                'whatsapp' => isset($_POST['sharing_network_whatsapp'])
            ];
            update_post_meta($post_id, 'module_sharing_networks', json_encode($networks));
            break;

        case 'login':
            $login_settings = [
                'redirect_url' => esc_url_raw($_POST['login_redirect_url']),
                'show_register' => isset($_POST['login_show_register']),
                'show_lost_password' => isset($_POST['login_show_lost_password'])
            ];
            update_post_meta($post_id, 'module_login_settings', json_encode($login_settings));
            break;

        case 'payment':
            $payment_settings = [
                'payment_type' => sanitize_text_field($_POST['payment_type']),
                'product_id' => sanitize_text_field($_POST['payment_product_id']),
                'amount' => sanitize_text_field($_POST['payment_amount']),
                'currency' => sanitize_text_field($_POST['payment_currency']),
                'success_url' => esc_url_raw($_POST['payment_success_url']),
                'cancel_url' => esc_url_raw($_POST['payment_cancel_url'])
            ];
            update_post_meta($post_id, 'module_payment_settings', json_encode($payment_settings));
            break;

        case 'calendar':
            // Process disabled dates
            $disabled_dates = isset($_POST['calendar_disabled_date']) ?
                array_filter(array_map('sanitize_text_field', $_POST['calendar_disabled_date'])) : [];

            // Process available times
            $available_times = isset($_POST['calendar_available_time']) ?
                array_filter(array_map('sanitize_text_field', $_POST['calendar_available_time'])) : [];

            $calendar_settings = [
                'calendar_type' => sanitize_text_field($_POST['calendar_type']),
                'min_date' => sanitize_text_field($_POST['calendar_min_date']),
                'max_date' => sanitize_text_field($_POST['calendar_max_date']),
                'disabled_dates' => $disabled_dates,
                'available_times' => $available_times
            ];
            update_post_meta($post_id, 'module_calendar_settings', json_encode($calendar_settings));
            break;

        case 'video':
            update_post_meta($post_id, 'module_video_url', esc_url_raw($_POST['video_url']));
            break;

        case 'form':
            update_post_meta($post_id, 'module_form_id', sanitize_text_field($_POST['form_id']));
            break;
    }
}
add_action('save_post_module', 'save_module_meta');

/**
 * Add custom columns to the modules admin list
 */
function add_module_admin_columns($columns) {
    $new_columns = [];

    // Insert columns after title
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        if ($key === 'title') {
            $new_columns['module_template'] = __('Template', 'steget');
            $new_columns['module_category'] = __('Category', 'steget');
            $new_columns['module_placement'] = __('Placement', 'steget');
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
        case 'module_template':
            $template = get_post_meta($post_id, 'module_template', true);
            if ($template) {
                $templates = get_module_templates();
                echo isset($templates[$template]) ? esc_html($templates[$template]) : esc_html($template);
            } else {
                echo '—';
            }
            break;

        case 'module_category':
            $terms = get_the_terms($post_id, 'module_category');
            if (!empty($terms) && !is_wp_error($terms)) {
                $term_names = [];
                foreach ($terms as $term) {
                    $term_names[] = $term->name;
                }
                echo esc_html(implode(', ', $term_names));
            } else {
                echo '—';
            }
            break;

        case 'module_placement':
            $terms = get_the_terms($post_id, 'module_placement');
            if (!empty($terms) && !is_wp_error($terms)) {
                $term_names = [];
                foreach ($terms as $term) {
                    $term_names[] = $term->name;
                }
                echo esc_html(implode(', ', $term_names));
            } else {
                echo '—';
            }
            break;
    }
}
add_action('manage_module_posts_custom_column', 'populate_module_admin_columns', 10, 2);

/**
 * Add sortable columns
 */
function make_module_admin_columns_sortable($columns) {
    $columns['module_template'] = 'module_template';
    return $columns;
}
add_filter('manage_edit-module_sortable_columns', 'make_module_admin_columns_sortable');

/**
 * Add filter dropdowns
 */
function add_module_admin_filters() {
    global $typenow;

    // Only on the modules listing screen
    if ($typenow !== 'module') {
        return;
    }

    // Template filter
    $current_template = isset($_GET['module_template_filter']) ? sanitize_text_field($_GET['module_template_filter']) : '';
    $templates = get_module_templates();
    ?>
<select name="module_template_filter">
    <option value=""><?php _e('All Templates', 'steget'); ?></option>
    <?php foreach ($templates as $value => $label) : ?>
    <option value="<?php echo esc_attr($value); ?>" <?php selected($current_template, $value); ?>>
        <?php echo esc_html($label); ?>
    </option>
    <?php endforeach; ?>
</select>
<?php
}
add_action('restrict_manage_posts', 'add_module_admin_filters');

/**
 * Process admin filters
 */
function process_module_admin_filters($query) {
    global $pagenow, $typenow;

    // Only on the modules listing screen
    if ($pagenow !== 'edit.php' || $typenow !== 'module' || !is_admin()) {
        return $query;
    }

    // Template filter
    if (isset($_GET['module_template_filter']) && !empty($_GET['module_template_filter'])) {
        $query->query_vars['meta_key'] = 'module_template';
        $query->query_vars['meta_value'] = sanitize_text_field($_GET['module_template_filter']);
    }

    return $query;
}
add_filter('parse_query', 'process_module_admin_filters');
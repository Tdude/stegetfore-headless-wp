<?php
/**
 * Module data saving functionality
 * 
 * @package Steget
 */

// --- DRY Helper for saving repeatable JSON meta fields ---
function save_repeatable_json_meta($post_id, $field_prefix, $field_names, $meta_key) {
    $count = 0;
    foreach ($field_names as $field) {
        if (isset($_POST[$field_prefix . '_' . $field]) && is_array($_POST[$field_prefix . '_' . $field])) {
            $count = max($count, count($_POST[$field_prefix . '_' . $field]));
        }
    }
    $items = [];
    for ($i = 0; $i < $count; $i++) {
        $item = [];
        foreach ($field_names as $field) {
            $val = isset($_POST[$field_prefix . '_' . $field][$i]) ? $_POST[$field_prefix . '_' . $field][$i] : '';
            // Use appropriate sanitizer
            if (strpos($field, 'image') !== false || strpos($field, 'url') !== false) {
                $item[$field] = esc_url_raw($val);
            } elseif (strpos($field, 'content') !== false || strpos($field, 'description') !== false || strpos($field, 'answer') !== false) {
                $item[$field] = sanitize_textarea_field($val);
            } elseif (strpos($field, 'rating') !== false || strpos($field, 'position') !== false || strpos($field, 'count') !== false) {
                $item[$field] = intval($val);
            } else {
                $item[$field] = sanitize_text_field($val);
            }
        }
        // Only add if at least one field is not empty
        if (count(array_filter($item, function($v){ return $v !== '' && $v !== null; })) > 0) {
            $items[] = $item;
        }
    }
    update_post_meta($post_id, $meta_key, json_encode($items, JSON_UNESCAPED_UNICODE));
}

/**
 * Save module meta data
 */
function save_module_meta($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['module_meta_nonce'])) {
        return;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['module_meta_nonce'], 'save_module_meta')) {
        return;
    }

    // If this is an autosave, we don't want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Core module settings
    if (isset($_POST['module_template'])) {
        update_post_meta($post_id, 'module_template', sanitize_text_field($_POST['module_template']));
    }

    if (isset($_POST['module_layout'])) {
        update_post_meta($post_id, 'module_layout', sanitize_text_field($_POST['module_layout']));
    }

    update_post_meta($post_id, 'module_full_width', isset($_POST['module_full_width']));

    if (isset($_POST['module_background_color'])) {
        update_post_meta($post_id, 'module_background_color', sanitize_text_field($_POST['module_background_color']));
    }

    // Template-specific settings
    if (isset($_POST['module_template'])) {
        $template = sanitize_text_field($_POST['module_template']);
        
        switch ($template) {
            case 'hero':
                $hero_settings = [
                    'title' => sanitize_text_field($_POST['hero_title']),
                    'subtitle' => sanitize_textarea_field($_POST['hero_subtitle']),
                    'image' => esc_url_raw($_POST['hero_image']),
                    'overlay' => isset($_POST['hero_overlay']),
                    'overlay_opacity' => intval($_POST['hero_overlay_opacity']),
                    'text_color' => sanitize_hex_color($_POST['hero_text_color']),
                    'alignment' => sanitize_text_field($_POST['hero_alignment']),
                    'min_height' => intval($_POST['hero_min_height'])
                ];
                update_post_meta($post_id, 'module_hero_settings', json_encode($hero_settings, JSON_UNESCAPED_UNICODE));
                break;

            case 'selling_points':
                // Process selling points data
                $points_count = isset($_POST['selling_points_count']) ? intval($_POST['selling_points_count']) : 0;
                $points = [];
                
                for ($i = 0; $i < $points_count; $i++) {
                    if (isset($_POST['selling_point_title'][$i]) && !empty($_POST['selling_point_title'][$i])) {
                        $points[] = [
                            'title' => sanitize_text_field($_POST['selling_point_title'][$i]),
                            'description' => sanitize_textarea_field($_POST['selling_point_description'][$i]),
                            'icon' => sanitize_text_field($_POST['selling_point_icon'][$i]),
                            'image' => esc_url_raw($_POST['selling_point_image'][$i])
                        ];
                    }
                }
                
                $selling_points_settings = [
                    'title' => sanitize_text_field($_POST['selling_points_title']),
                    'subtitle' => sanitize_textarea_field($_POST['selling_points_subtitle']),
                    'layout' => sanitize_text_field($_POST['selling_points_layout']),
                    'points_per_row' => intval($_POST['selling_points_per_row']),
                    'points' => $points
                ];
                update_post_meta($post_id, 'module_selling_points_settings', json_encode($selling_points_settings, JSON_UNESCAPED_UNICODE));
                break;

            case 'stats':
                // Process stats data
                $stats_count = isset($_POST['stats_count']) ? intval($_POST['stats_count']) : 0;
                $stats = [];
                
                for ($i = 0; $i < $stats_count; $i++) {
                    if (isset($_POST['stat_value'][$i]) && !empty($_POST['stat_value'][$i])) {
                        $stats[] = [
                            'value' => sanitize_text_field($_POST['stat_value'][$i]),
                            'label' => sanitize_text_field($_POST['stat_label'][$i]),
                            'description' => sanitize_textarea_field($_POST['stat_description'][$i]),
                            'icon' => sanitize_text_field($_POST['stat_icon'][$i])
                        ];
                    }
                }
                
                $stats_settings = [
                    'title' => sanitize_text_field($_POST['stats_title']),
                    'subtitle' => sanitize_textarea_field($_POST['stats_subtitle']),
                    'background' => esc_url_raw($_POST['stats_background']),
                    'layout' => sanitize_text_field($_POST['stats_layout']),
                    'stats_per_row' => intval($_POST['stats_per_row']),
                    'stats' => $stats
                ];
                update_post_meta($post_id, 'module_stats_settings', json_encode($stats_settings, JSON_UNESCAPED_UNICODE));
                break;

            case 'testimonials':
                // DRY: Use helper for testimonials
                save_repeatable_json_meta(
                    $post_id,
                    'testimonial',
                    ['text', 'author', 'position', 'image', 'rating'],
                    'module_testimonials'
                );
                // Save settings (title, subtitle, layout, etc.) separately if needed
                $testimonials_settings = [
                    'title' => sanitize_text_field($_POST['testimonials_title']),
                    'subtitle' => sanitize_textarea_field($_POST['testimonials_subtitle']),
                    'layout' => sanitize_text_field($_POST['testimonials_layout']),
                    'display_type' => sanitize_text_field($_POST['testimonials_display']),
                    'show_ratings' => isset($_POST['testimonials_show_ratings'])
                ];
                update_post_meta($post_id, 'module_testimonials_settings', json_encode($testimonials_settings, JSON_UNESCAPED_UNICODE));
                break;

            case 'tabbed_content':
                // DRY: Use helper for tabbed content with image
                $tab_titles = isset($_POST['tab_title']) ? $_POST['tab_title'] : [];
                $tab_contents = isset($_POST['tab_content']) ? $_POST['tab_content'] : [];
                $tab_images = isset($_POST['tab_image']) ? $_POST['tab_image'] : [];
                $tab_image_aligns = isset($_POST['tab_image_align']) ? $_POST['tab_image_align'] : [];
                $tabs = [];
                $count = max(count($tab_titles), count($tab_contents), count($tab_images), count($tab_image_aligns));
                for ($i = 0; $i < $count; $i++) {
                    $title = isset($tab_titles[$i]) ? sanitize_text_field($tab_titles[$i]) : '';
                    $content = isset($tab_contents[$i]) ? sanitize_textarea_field($tab_contents[$i]) : '';
                    $image = isset($tab_images[$i]) ? esc_url_raw($tab_images[$i]) : '';
                    $imageAlign = isset($tab_image_aligns[$i]) ? sanitize_text_field($tab_image_aligns[$i]) : 'left';
                    if ($title !== '' || $content !== '' || $image !== '') {
                        $tabs[] = [
                            'title' => $title,
                            'content' => $content,
                            'image' => $image,
                            'imageAlign' => $imageAlign
                        ];
                    }
                }
                update_post_meta($post_id, 'module_tabbed_content', json_encode($tabs, JSON_UNESCAPED_UNICODE));
                // Save layout setting
                $layout = isset($_POST['tabbed_content_layout']) ? sanitize_text_field($_POST['tabbed_content_layout']) : '';
                update_post_meta($post_id, 'tabbed_content_layout', $layout);
                break;

            case 'featured_posts':
                $categories = isset($_POST['featured_posts_categories']) ? array_map('intval', $_POST['featured_posts_categories']) : [];
                
                $featured_posts_settings = [
                    'title' => sanitize_text_field($_POST['featured_posts_title']),
                    'subtitle' => sanitize_text_field($_POST['featured_posts_subtitle']),
                    'categories' => $categories,
                    'post_count' => intval($_POST['featured_posts_count']),
                    'display_style' => sanitize_text_field($_POST['featured_posts_display']),
                    'show_date' => isset($_POST['featured_posts_show_date']),
                    'show_excerpt' => isset($_POST['featured_posts_show_excerpt']),
                    'show_author' => isset($_POST['featured_posts_show_author']),
                    'layout_style' => sanitize_text_field($_POST['featured_posts_layout_style'])
                ];
                update_post_meta($post_id, 'module_featured_posts_settings', json_encode($featured_posts_settings, JSON_UNESCAPED_UNICODE));
                break;

            case 'faq':
                // FAQ module saving
                $faq_items = [];
                if (isset($_POST['faq_question']) && is_array($_POST['faq_question'])) {
                    $count = count($_POST['faq_question']);
                    for ($i = 0; $i < $count; $i++) {
                        $question = trim($_POST['faq_question'][$i]);
                        $answer = isset($_POST['faq_answer'][$i]) ? trim($_POST['faq_answer'][$i]) : '';
                        if ($question !== '' || $answer !== '') {
                            $faq_items[] = [
                                'question' => sanitize_text_field($question),
                                'answer' => sanitize_textarea_field($answer)
                            ];
                        }
                    }
                }
                update_post_meta($post_id, 'module_faq_items', json_encode($faq_items, JSON_UNESCAPED_UNICODE));
                break;

            // Add other template cases as needed
        }
    }

    // Save buttons
    if (isset($_POST['button_text']) && is_array($_POST['button_text'])) {
        $buttons = [];
        $count = count($_POST['button_text']);
        
        for ($i = 0; $i < $count; $i++) {
            if (!empty($_POST['button_text'][$i])) {
                $buttons[] = [
                    'text' => sanitize_text_field($_POST['button_text'][$i]),
                    'url' => esc_url_raw($_POST['button_url'][$i]),
                    'style' => sanitize_text_field($_POST['button_style'][$i]),
                    'size' => sanitize_text_field($_POST['button_size'][$i]),
                    'new_tab' => isset($_POST['button_new_tab'][$i])
                ];
            }
        }
        
        update_post_meta($post_id, 'module_buttons', json_encode($buttons, JSON_UNESCAPED_UNICODE));
    } else {
        update_post_meta($post_id, 'module_buttons', json_encode([], JSON_UNESCAPED_UNICODE));
    }
}
add_action('save_post_module', 'save_module_meta');

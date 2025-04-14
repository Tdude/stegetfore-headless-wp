<?php
/**
 * Template settings fields and handling for modules
 * 
 * @package Steget
 */

/**
 * Render template-specific settings meta box
 */
function render_module_template_settings_meta_box($post) {
    $template = get_post_meta($post->ID, 'module_template', true);
    
    if (!$template) {
        echo '<div class="module-message"><p>' . __('Please select a template type first.', 'steget') . '</p></div>';
        return;
    }
    
    // Render appropriate template fields based on template type
    switch ($template) {
        case 'hero':
            render_hero_template_fields($post);
            break;
        case 'selling-points':
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
        case 'tabbed-content':
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
        case 'video':
            render_video_template_fields($post);
            break;
        case 'form':
            render_form_template_fields($post);
            break;
        case 'cta':
            render_cta_template_fields($post);
            break;
        case 'text-media':
            render_text_media_template_fields($post);
            break;
        case 'featured-posts':
            render_featured_posts_template_fields($post);
            break;
        default:
            echo '<div class="module-message"><p>' . __('No template settings available for this template type.', 'steget') . '</p></div>';
    }
}

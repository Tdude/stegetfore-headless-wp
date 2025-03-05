<?php
/**
 * features/cta-api.php
*/
function get_cta_data($homepage_id) {
    return [
        'title' => get_post_meta($homepage_id, 'cta_title', true) ?: '',
        'description' => get_post_meta($homepage_id, 'cta_description', true) ?: '',
        'button_text' => get_post_meta($homepage_id, 'cta_button_text', true) ?: '',
        'button_url' => get_post_meta($homepage_id, 'cta_button_url', true) ?: '',
        'background_color' => get_post_meta($homepage_id, 'cta_background_color', true) ?: 'bg-primary',
    ];
}
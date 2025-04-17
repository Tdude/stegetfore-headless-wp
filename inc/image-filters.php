<?php
// Image Handling
if (!defined('ABSPATH')) exit;

function fix_image_urls($url) {
    // ...
}
add_filter('wp_get_attachment_url', 'fix_image_urls');
add_filter('wp_calculate_image_srcset', function($sources) {
    if (empty($sources)) return $sources;
    // ...
    return $sources;
});

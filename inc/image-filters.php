<?php
// Image Handling
if (!defined('ABSPATH')) exit;

function fix_image_urls($url) {
    // Convert localhost URLs to production
    if (strpos($url, 'localhost:8000') !== false) {
        $url = str_replace('http://localhost:8000', 'https://stegetfore.nu', $url);
    }
    return $url;
}
add_filter('wp_get_attachment_url', 'fix_image_urls');
add_filter('wp_calculate_image_srcset', function($sources) {
    if (empty($sources)) {
        return $sources;
    }
    foreach ($sources as &$source) {
        $source['url'] = fix_image_urls($source['url']);
    }
    return $sources;
});

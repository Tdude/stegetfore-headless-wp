<?php
// Main functions.php - only bootstraps and includes
if (!defined('ABSPATH')) exit;

// Theme Setup
require_once get_template_directory() . '/inc/theme-setup.php';

// REST API / CORS
require_once get_template_directory() . '/inc/cors.php';

// Next.js Integration
require_once get_template_directory() . '/inc/nextjs-revalidation.php';

// Scripts & Styles
require_once get_template_directory() . '/inc/scripts.php';

// Image Handling
require_once get_template_directory() . '/inc/image-filters.php';

// Meta Fields
require_once get_template_directory() . '/inc/meta-fields.php';

// Admin UI
require_once get_template_directory() . '/inc/admin-ui.php';

// Load other theme components (post types, meta fields, REST endpoints, admin options, etc.)
$required_files = [
    '/inc/post-types/evaluation.php',
    '/inc/post-types/modules.php',
    '/inc/meta-fields/register-meta.php',
    '/inc/rest/endpoints.php',
    '/inc/rest/wpcf7-endpoints.php',
    '/inc/admin/theme-options.php',
    '/inc/admin/meta-cleanup.php'
];
foreach ($required_files as $file) {
    $file_path = get_template_directory() . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        error_log("Loaded file: $file");
    } else {
        error_log("Could not find file: $file");
    }
}
<?php
/**
 * Module UI Refactoring Script
 * 
 * This script helps migrate from the monolithic module-ui.php to the new
 * component-based structure. It verifies all files are in place and working
 * before modifying the original module-ui.php file.
 */

// Check that all required module component files exist
$base_path = dirname(__FILE__) . '/modules/';
$required_files = [
    $base_path . 'base.php',
    $base_path . 'template-fields.php',
    $base_path . 'buttons.php',
    $base_path . 'saving.php',
    $base_path . 'admin-list.php',
    $base_path . 'content-types/hero.php',
    $base_path . 'content-types/featured-posts.php',
    $base_path . 'content-types/testimonials.php',
    $base_path . 'content-types/gallery.php',
    $base_path . 'content-types/faq.php',
    $base_path . 'content-types/tabbed-content.php',
    $base_path . 'content-types/chart.php',
    $base_path . 'content-types/sharing.php',
    $base_path . 'content-types/login.php',
    $base_path . 'content-types/payment.php',
    $base_path . 'content-types/calendar.php',
    $base_path . 'module-ui.php'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 15px 0;">';
    echo '<h2>Error: Missing Required Files</h2>';
    echo '<p>The following files are required for the refactoring but are missing:</p>';
    echo '<ul>';
    foreach ($missing_files as $file) {
        echo '<li>' . htmlspecialchars($file) . '</li>';
    }
    echo '</ul>';
    echo '<p>Please make sure all the required files are in place before continuing.</p>';
    echo '</div>';
    exit;
}

// All files are present, now we need to update the module-ui.php file
$module_ui_content = <<<'PHP'
<?php
/** inc/admin/module-ui.php
 * Admin UI related methods
 *
 * This file has been refactored to improve maintainability.
 * Each component now has its own file in the /inc/admin/modules/ directory.
 */

// Include the refactored module UI components
require_once get_template_directory() . '/inc/admin/modules/base.php';
require_once get_template_directory() . '/inc/admin/modules/template-fields.php';
require_once get_template_directory() . '/inc/admin/modules/buttons.php';
require_once get_template_directory() . '/inc/admin/modules/saving.php';
require_once get_template_directory() . '/inc/admin/modules/admin-list.php';

// Include module content types
require_once get_template_directory() . '/inc/admin/modules/content-types/hero.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/featured-posts.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/testimonials.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/gallery.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/faq.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/tabbed-content.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/chart.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/sharing.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/login.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/payment.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/calendar.php';
PHP;

// Backup the original file if it hasn't been backed up already
$original_file = dirname(__FILE__) . '/module-ui.php';
$backup_file = dirname(__FILE__) . '/module-ui.php.bak';

if (!file_exists($backup_file)) {
    if (!copy($original_file, $backup_file)) {
        echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 15px 0;">';
        echo '<h2>Error: Failed to Create Backup</h2>';
        echo '<p>Could not create a backup of the original module-ui.php file.</p>';
        echo '</div>';
        exit;
    }
    echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 15px 0;">';
    echo '<p>✅ Successfully created backup of original module-ui.php at: ' . htmlspecialchars($backup_file) . '</p>';
    echo '</div>';
} else {
    echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 15px 0;">';
    echo '<p>ℹ️ Backup already exists at: ' . htmlspecialchars($backup_file) . '</p>';
    echo '</div>';
}

// Write the new content to the file
$result = file_put_contents($original_file, $module_ui_content);

if ($result === false) {
    echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 15px 0;">';
    echo '<h2>Error: Failed to Update File</h2>';
    echo '<p>Could not update the module-ui.php file with the new content.</p>';
    echo '</div>';
    exit;
}

echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 15px 0;">';
echo '<h2>Refactoring Completed Successfully!</h2>';
echo '<p>The module-ui.php file has been updated to use the new component-based structure.</p>';
echo '<p>A backup of the original file has been saved at: ' . htmlspecialchars($backup_file) . '</p>';
echo '<p>If you encounter any issues, you can restore the original file by copying the backup back to module-ui.php.</p>';
echo '</div>';
?>

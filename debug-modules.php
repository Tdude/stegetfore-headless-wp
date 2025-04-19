<?php
/**
 * Debug script for Modulöversikt page modules
 * 
 * This script directly checks the database for the module data associated with the Modulöversikt page
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(__FILE__))) . '/wp-load.php';
require_once($wp_load_path);

// Configure error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "==== MODULE DEBUG FOR MODULÖVERSIKT PAGE ====\n\n";

// Find the Modulöversikt page
$moduloversikt_page = get_page_by_path('moduloversikt');

if (!$moduloversikt_page) {
    echo "ERROR: Modulöversikt page not found by slug!\n";
    
    // Try to find it by ID 488 which is known to be the Modulöversikt page
    $page_by_id = get_post(488);
    if ($page_by_id) {
        echo "Found page with ID 488: {$page_by_id->post_title}\n";
        $moduloversikt_page = $page_by_id;
    } else {
        echo "ERROR: Could not find page with ID 488 either!\n";
        die();
    }
}

echo "Found Modulöversikt page:\n";
echo "ID: {$moduloversikt_page->ID}\n";
echo "Title: {$moduloversikt_page->post_title}\n";
echo "Status: {$moduloversikt_page->post_status}\n";
echo "Template: " . get_page_template_slug($moduloversikt_page->ID) . "\n\n";

// Check if the page has any modules
$page_modules_meta = get_post_meta($moduloversikt_page->ID, 'page_modules', true);
echo "Raw page_modules meta:\n";
var_dump($page_modules_meta);
echo "\n\n";

// Decode JSON if it exists
$modules_data = [];
if (!empty($page_modules_meta)) {
    echo "Trying to decode JSON...\n";
    $decoded = json_decode($page_modules_meta, true);
    echo "Decoded JSON result:\n";
    var_dump($decoded);
    echo "\n\n";
    
    if (is_array($decoded)) {
        echo "Found " . count($decoded) . " module references in metadata\n\n";
        
        // Loop through each module reference
        foreach ($decoded as $index => $module_ref) {
            echo "Module at index $index:\n";
            var_dump($module_ref);
            
            if (isset($module_ref['id'])) {
                $module_id = $module_ref['id'];
                echo "Looking up module with ID: $module_id\n";
                
                $module_post = get_post($module_id);
                if ($module_post) {
                    echo "Found module: {$module_post->post_title} (status: {$module_post->post_status})\n";
                    
                    // Check if this module has the correct category
                    $categories = wp_get_post_terms($module_id, 'module_category', ['fields' => 'names']);
                    echo "Module categories: " . implode(', ', $categories) . "\n";
                } else {
                    echo "ERROR: Module with ID $module_id does not exist!\n";
                }
            } else {
                echo "ERROR: Module reference doesn't have an ID!\n";
            }
            echo "\n";
        }
    } else {
        echo "ERROR: Failed to decode JSON or result is not an array\n";
    }
} else {
    echo "ERROR: No page_modules meta found for this page!\n";
    
    // Check if there are modules assigned by category
    echo "\nChecking for modules with 'modul-oversikt' category:\n";
    $args = [
        'post_type' => 'module',
        'tax_query' => [
            [
                'taxonomy' => 'module_category',
                'field'    => 'slug',
                'terms'    => 'modul-oversikt',
            ],
        ],
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    
    $module_query = new WP_Query($args);
    if ($module_query->have_posts()) {
        echo "Found " . $module_query->post_count . " modules with 'modul-oversikt' category:\n";
        
        while ($module_query->have_posts()) {
            $module_query->the_post();
            echo "- " . get_the_title() . " (ID: " . get_the_ID() . ")\n";
        }
        
        wp_reset_postdata();
    } else {
        echo "No modules found with 'modul-oversikt' category\n";
    }
}

// Direct SQL query to check the postmeta table
echo "\n\nDirect SQL query to check for page_modules meta:\n";
global $wpdb;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'page_modules'",
        $moduloversikt_page->ID
    )
);

if ($results) {
    foreach ($results as $row) {
        echo "Meta ID: {$row->meta_id}\n";
        echo "Post ID: {$row->post_id}\n";
        echo "Meta Key: {$row->meta_key}\n";
        echo "Meta Value (first 200 chars): " . substr($row->meta_value, 0, 200) . "\n";
    }
} else {
    echo "No results found in database for page_modules meta!\n";
}

echo "</pre>";

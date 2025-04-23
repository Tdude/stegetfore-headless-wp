<?php
// File: inc/admin/meta-cleanup.php

/**
 * Aggressive recursive slash removal and Unicode decoding
 */
function deep_unescape_string($input)
{
    if (!is_string($input)) {
        return $input;
    }

    // First decode any Unicode escape sequences
    $unicode_decoded = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
        return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
    }, $input);

    // Now handle the excessive slashes - do multiple passes if needed
    $prev = '';
    $current = $unicode_decoded;

    // Keep unescaping until we reach a stable string (no more changes)
    while ($prev !== $current) {
        $prev = $current;
        $current = stripslashes($prev);
    }

    return $current;
}

/**
 * Deep processing for arrays and objects
 */
function deep_unescape_data($data)
{
    if (is_string($data)) {
        return deep_unescape_string($data);
    } elseif (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = deep_unescape_data($value);
        }
    }
    return $data;
}

/**
 * Advanced cleanup for WordPress meta data
 */
function aggressive_fix_slashed_meta_data()
{
    global $wpdb;

    // Get all meta keys that might contain JSON or text with slashes
    $meta_keys = [
        "'module_%'",
        "'%_points'",
        "'page_modules'",
        "'cta_%'",
        "'hero_%'",
        "'selling_points%'",
        "'%description%'",
        "'%content%'",
        "'%text%'"
    ];
    $meta_keys_str = implode(' OR meta_key LIKE ', $meta_keys);

    $results = $wpdb->get_results(
        "SELECT * FROM $wpdb->postmeta
         WHERE (meta_key LIKE " . implode(' OR meta_key LIKE ', $meta_keys) . ")"
    );

    $count = 0;
    foreach ($results as $meta) {
        $original_value = $meta->meta_value;

        // First try to detect if this is JSON
        $is_json = false;
        $json_decoded = null;

        // Check if it might be a JSON string (even with slashes)
        if (
            preg_match('/^\s*[\[{]/', $original_value) ||
            preg_match('/[\]}]\s*$/', $original_value) ||
            strpos($original_value, '\\') !== false
        ) {

            // Try to decode as JSON
            $unslashed = stripslashes($original_value);
            $json_decoded = json_decode($unslashed, true);

            if ($json_decoded !== null) {
                $is_json = true;
            } else {
                // Try aggressive unescaping and then JSON decode
                $deeply_unslashed = deep_unescape_string($original_value);
                $json_decoded = json_decode($deeply_unslashed, true);
                if ($json_decoded !== null) {
                    $is_json = true;
                }
            }
        }

        // Fix based on content type
        if ($is_json && is_array($json_decoded)) {
            // Process each value in the JSON
            $clean_data = deep_unescape_data($json_decoded);
            $new_value = wp_json_encode($clean_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            // Treat as regular text
            $new_value = deep_unescape_string($original_value);
        }

        // Only update if we've made changes
        if ($new_value !== $original_value) {
            $wpdb->update(
                $wpdb->postmeta,
                ['meta_value' => $new_value],
                ['meta_id' => $meta->meta_id]
            );
            $count++;
        }
    }

    return $count;
}

/**
 * Add the admin page
 */
function add_fix_meta_admin_page()
{
    add_management_page(
        'Fix Meta Slashes',
        'Fix Meta Slashes',
        'manage_options',
        'fix-meta-slashes',
        'render_fix_meta_page'
    );
}
add_action('admin_menu', 'add_fix_meta_admin_page');

/**
 * Render the admin page
 */
function render_fix_meta_page()
{
    $message = '';

    if (isset($_POST['fix_meta_nonce']) && wp_verify_nonce($_POST['fix_meta_nonce'], 'fix_meta_action')) {
        $count = aggressive_fix_slashed_meta_data();
        $message = "<div class='notice notice-success'><p>Fixed $count items with excessive slashes.</p></div>";
    }

    ?>
<div class="wrap">
    <h1>Fix Meta Slashes (Careful! Backup first)</h1>
    <?php echo $message; ?>
    <p>This utility will fix meta data that has excessive backslashes and Unicode escape sequences. In some cases it
        might break your json, depending of other special characters and how deeply nested the backslashes are. Not
        good, but it worked for me. Go ahead and make it better!</p>
    <p>You get rid of backslashes buildup in your WP API json, like
    <pre>Hjelo I\\\\\'m World.</pre> speaking in a Russian accent :)
    </p>
    <form method="post">
        <?php wp_nonce_field('fix_meta_action', 'fix_meta_nonce'); ?>
        <p><input type="submit" class="button button-primary" value="Fix Meta Data"></p>
    </form>
</div>
<?php
}

// --- MIGRATION: Standardize module_buttons to always be a JSON array of objects ---
function migrate_module_buttons_to_array_of_objects() {
    global $wpdb;
    $meta_key = 'module_buttons';
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
        $meta_key
    ));
    $fixed = 0;
    foreach ($results as $row) {
        $val = $row->meta_value;
        $decoded = json_decode($val, true);
        // Only migrate if it's an array of JSON strings
        if (is_array($decoded) && isset($decoded[0]) && is_string($decoded[0])) {
            $new = array_map(function($btn) {
                return is_string($btn) ? json_decode($btn, true) : $btn;
            }, $decoded);
            // Only update if all items are arrays (objects)
            if (count($new) && is_array($new[0])) {
                update_post_meta($row->post_id, $meta_key, json_encode($new));
                $fixed++;
            }
        }
    }
    return $fixed;
}

// Add to admin page
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Migrate Module Buttons',
        'Migrate Module Buttons',
        'manage_options',
        'migrate-module-buttons',
        function() {
            if (isset($_POST['migrate_module_buttons'])) {
                $fixed = migrate_module_buttons_to_array_of_objects();
                echo '<div class="notice notice-success"><p>Migrated ' . intval($fixed) . ' module_buttons meta entries.</p></div>';
            }
            echo '<div class="wrap"><h1>Migrate Module Buttons</h1>';
            echo '<form method="post"><input type="hidden" name="migrate_module_buttons" value="1" />';
            echo '<button class="button button-primary">Run Migration</button>';
            echo '</form></div>';
        }
    );
});
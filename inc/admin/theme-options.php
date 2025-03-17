<?php
/*
 * inc/admin/theme-options.php
 */
function headless_theme_options_page()
{
    add_menu_page(
        'Theme Options',
        'Theme Options',
        'manage_options',
        'headless-theme-options',
        'render_theme_options_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'headless_theme_options_page');

function render_theme_options_page()
{
    if (isset($_POST['headless_mode'])) {
        update_option('headless_mode_enabled', $_POST['headless_mode'] === 'on');
    }

    $headless_mode = get_option('headless_mode_enabled');
    ?>
<div class="wrap">
    <h1>Theme Options</h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row">Headless Mode</th>
                <td>
                    <label>
                        <input type="checkbox" name="headless_mode" <?php checked($headless_mode); ?>>
                        Enable headless mode
                    </label>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}


// Display the template choice in the admin bar
function display_template_choice_in_admin_bar($wp_admin_bar)
{
    if (is_admin() && get_current_screen()->post_type === 'page' && isset($_GET['post'])) {
        $post_id = intval($_GET['post']);
        $template_choice = get_post_meta($post_id, '_template_choice', true);

        if ($template_choice) {
            $wp_admin_bar->add_node(array(
                'id' => 'template',
                'title' => 'Template: ' . ucfirst(str_replace('-', ' ', $template_choice)),
                'parent' => 'top-secondary',
            ));
        }
    }
}
add_action('admin_bar_menu', 'display_template_choice_in_admin_bar', 999);



// This is TEMPORARY. Glory is forever...
function fix_slashed_meta_data()
{
    global $wpdb;

    // Get all meta with potentially escaped JSON
    $meta_keys = ["'module_%'", "'%_points'", "'page_modules'"];
    $meta_keys_str = implode(' OR meta_key LIKE ', $meta_keys);

    $results = $wpdb->get_results(
        "SELECT * FROM $wpdb->postmeta
         WHERE (meta_key LIKE $meta_keys_str)
         AND meta_value LIKE '%\\\\%'"
    );

    $count = 0;
    foreach ($results as $meta) {
        // Check if it looks like JSON with extra slashes
        if (strpos($meta->meta_value, '\\') !== false) {
            $unslashed = stripslashes($meta->meta_value);

            // Try to decode to verify it's valid JSON
            $decoded = json_decode($unslashed, true);
            if ($decoded !== null) {
                // Re-encode properly
                $fixed_value = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE);

                // Update the value
                $wpdb->update(
                    $wpdb->postmeta,
                    ['meta_value' => $fixed_value],
                    ['meta_id' => $meta->meta_id]
                );
                $count++;
            }
        }
    }

    return $count;
}

// Add to admin as a utility
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

function render_fix_meta_page()
{
    $message = '';

    if (isset($_POST['fix_meta_nonce']) && wp_verify_nonce($_POST['fix_meta_nonce'], 'fix_meta_action')) {
        $count = fix_slashed_meta_data();
        $message = "<div class='notice notice-success'><p>Fixed $count items with excessive slashes.</p></div>";
    }

    ?>
<div class="wrap">
    <h1>Fix Meta Slashes</h1>
    <?php echo $message; ?>
    <p>This utility will fix meta data that has excessive backslashes due to multiple json_encode calls.</p>
    <form method="post">
        <?php wp_nonce_field('fix_meta_action', 'fix_meta_nonce'); ?>
        <p><input type="submit" class="button button-primary" value="Fix Meta Data"></p>
    </form>
</div>
<?php
}
<?php
/*
 * inc/admin/theme-options.php
 *
 * */
function headless_theme_options_page() {
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

function render_theme_options_page() {
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
function display_template_choice_in_admin_bar($wp_admin_bar) {
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
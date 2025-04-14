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
    
    if (isset($_POST['blog_layout_style'])) {
        update_option('blog_layout_style', sanitize_text_field($_POST['blog_layout_style']));
    }

    $headless_mode = get_option('headless_mode_enabled');
    $blog_layout_style = get_option('blog_layout_style', 'traditional');
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
            <tr>
                <th scope="row">Blog Listing Layout</th>
                <td>
                    <select name="blog_layout_style" class="regular-text">
                        <option value="traditional" <?php selected($blog_layout_style, 'traditional'); ?>>Traditional Grid</option>
                        <option value="magazine" <?php selected($blog_layout_style, 'magazine'); ?>>Magazine Layout</option>
                    </select>
                    <p class="description">
                        Choose how posts are displayed on the blog listing page.<br>
                        Traditional Grid: All post cards have equal size.<br>
                        Magazine Layout: First post takes 2/3 width with two smaller posts stacked in the remaining space.
                    </p>
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
<?php
/*
* inc/admin/theme-options.php
*/
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



// Startpage admin stuff
function homepage_meta_boxes() {
    add_meta_box(
        'homepage_hero_section',
        'Hero Section',
        'render_hero_metabox',
        'page',
        'normal',
        'high'
    );

    // Add more meta boxes for other sections
}
add_action('add_meta_boxes', 'homepage_meta_boxes');

function render_hero_metabox($post) {
    // Only show for homepage
    if ($post->ID != get_option('page_on_front')) {
        echo '<p>These settings only apply to the homepage.</p>';
        return;
    }

    $hero_title = get_post_meta($post->ID, 'hero_title', true);
    $hero_intro = get_post_meta($post->ID, 'hero_intro', true);
    $hero_image_id = get_post_meta($post->ID, 'hero_image_id', true);
    $cta_buttons = json_decode(get_post_meta($post->ID, 'hero_cta_buttons', true), true) ?: [];

    // Render form fields
    ?>
<div class="hero-section-fields">
    <p>
        <label for="hero_title">Hero Title:</label>
        <input type="text" id="hero_title" name="hero_title" value="<?php echo esc_attr($hero_title); ?>"
            class="widefat">
    </p>
    <!-- More fields here -->
</div>
<?php

    // Add nonce for security
    wp_nonce_field('save_homepage_meta', 'homepage_meta_nonce');
}

function save_homepage_meta($post_id) {
    // Security checks, permissions, etc.

    if (isset($_POST['hero_title'])) {
        update_post_meta($post_id, 'hero_title', sanitize_text_field($_POST['hero_title']));
    }

    // Handle repeatable fields (CTA buttons)
    if (isset($_POST['cta_button_text']) && is_array($_POST['cta_button_text'])) {
        $buttons = [];
        for ($i = 0; $i < count($_POST['cta_button_text']); $i++) {
            if (!empty($_POST['cta_button_text'][$i])) {
                $buttons[] = [
                    'text' => sanitize_text_field($_POST['cta_button_text'][$i]),
                    'url' => esc_url_raw($_POST['cta_button_url'][$i]),
                    'style' => sanitize_text_field($_POST['cta_button_style'][$i]),
                ];
            }
        }
        update_post_meta($post_id, 'hero_cta_buttons', json_encode($buttons));
    }

    // Save other fields similarly
}
add_action('save_post', 'save_homepage_meta');
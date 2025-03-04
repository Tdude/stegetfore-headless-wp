<?php
/*
* inc/admin/theme-options.php
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add theme options page to the admin menu
 */
function steget_add_theme_options_page() {
    add_theme_page(
        'Theme Options',
        'Theme Options',
        'manage_options',
        'steget-theme-options',
        'steget_render_theme_options_page'
    );
}
add_action('admin_menu', 'steget_add_theme_options_page');



/**
 * Render the theme options page
 */
function steget_render_theme_options_page() {
    ?>
<div class="wrap">
    <h1><?php echo get_admin_page_title(); ?></h1>

    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <a href="#homepage-tab" class="nav-tab nav-tab-active">Hemsida</a>
        <a href="#general-tab" class="nav-tab">Allmänt</a>
        <!-- Other tabs -->
    </h2>

    <form method="post" action="options.php">
        <?php
            settings_fields('steget_theme_options');
            do_settings_sections('steget_theme_options');
            ?>

        <div id="homepage-tab" class="tab-content">
            <?php steget_render_homepage_tab(); ?>
        </div>

        <div id="general-tab" class="tab-content" style="display:none;">
            <?php steget_render_general_tab(); ?>
        </div>

        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching logic
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');

        // Update tabs
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Update content
        $('.tab-content').hide();
        $(target).show();
    });
});
</script>
<?php
}

/**
 * Render the homepage options tab
 */
function steget_render_homepage_tab() {
    // Hero section - use the theme options version
    steget_render_hero_section_options();

    // Featured posts section
    steget_render_featured_posts_section();

    // New sections
    steget_render_selling_points_section();
    steget_render_stats_section();
    steget_render_gallery_section();

    // CTA section
    steget_render_cta_section();
}

/**
 * Render the general options tab
 */
function steget_render_general_tab() {
    // General settings content
    echo('PIRUM PARUM TEST');
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
        'steget_render_hero_section',
        'page',
        'normal',
        'high'
    );

    // Add more meta boxes for other sections
}
add_action('add_meta_boxes', 'homepage_meta_boxes');

/**
 * Render hero section for theme options
 */
function steget_render_hero_section_options() {
    $hero_title = get_option('steget_hero_title', '');
    $hero_subtitle = get_option('steget_hero_subtitle', '');
    $hero_image = get_option('steget_hero_image', '');
    $hero_buttons = get_option('steget_hero_buttons', array());

    ?>
<div class="steget-admin-block">
    <h3>Hero Section</h3>

    <table class="form-table">
        <tr>
            <th scope="row">Title</th>
            <td>
                <input type="text" name="steget_hero_title" value="<?php echo esc_attr($hero_title); ?>"
                    class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Subtitle</th>
            <td>
                <textarea name="steget_hero_subtitle" class="large-text"
                    rows="3"><?php echo esc_textarea($hero_subtitle); ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">Image URL</th>
            <td>
                <input type="text" name="steget_hero_image" value="<?php echo esc_attr($hero_image); ?>"
                    class="regular-text" />
                <button type="button" class="button upload-image">Upload</button>
            </td>
        </tr>
    </table>

    <!-- Hero buttons could go here -->
</div>
<?php
}

/**
 * Render hero section for meta box
 */
function steget_render_hero_section($post) {
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
    <p>
        <label for="hero_intro">Hero Intro:</label>
        <textarea id="hero_intro" name="hero_intro" class="widefat"><?php echo esc_textarea($hero_intro); ?></textarea>
    </p>
    <p>
        <label for="hero_image_id">Hero Image:</label>
        <input type="hidden" id="hero_image_id" name="hero_image_id" value="<?php echo esc_attr($hero_image_id); ?>">
        <button class="button button-primary upload-hero-image">Upload Image</button>
        <?php if ($hero_image_id) : ?>
        <img src="<?php echo wp_get_attachment_url($hero_image_id); ?>" alt="Hero Image"
            style="max-width: 100px; margin-top: 10px;">
        <?php endif; ?>
    </p>
    <h3>CTA Buttons</h3>
    <div id="cta_buttons_wrapper">
        <?php if (!empty($cta_buttons)) : ?>
        <?php foreach ($cta_buttons as $index => $button) : ?>
        <div class="cta-button-field">
            <input type="text" name="cta_button_text[]" value="<?php echo esc_attr($button['text']); ?>"
                placeholder="Button Text">
            <input type="url" name="cta_button_url[]" value="<?php echo esc_url($button['url']); ?>"
                placeholder="Button URL">
            <select name="cta_button_style[]">
                <option value="primary" <?php selected($button['style'], 'primary'); ?>>Primary</option>
                <option value="secondary" <?php selected($button['style'], 'secondary'); ?>>Secondary</option>
            </select>
            <button class="button button-danger remove-cta-button">Remove</button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <button class="button button-primary add-cta-button">Add Button</button>
    </div>
</div>
<?php
    // Add nonce for security
    wp_nonce_field('save_homepage_meta', 'homepage_meta_nonce');
}


function save_homepage_meta($post_id) {
    // Security checks, permissions, etc.
    if (!isset($_POST['homepage_meta_nonce']) || !wp_verify_nonce($_POST['homepage_meta_nonce'], 'save_homepage_meta')) {
        return;
    }

    if (isset($_POST['hero_title'])) {
        update_post_meta($post_id, 'hero_title', sanitize_text_field($_POST['hero_title']));
    }

    if (isset($_POST['hero_intro'])) {
        update_post_meta($post_id, 'hero_intro', sanitize_textarea_field($_POST['hero_intro']));
    }

    if (isset($_POST['hero_image_id'])) {
        update_post_meta($post_id, 'hero_image_id', sanitize_text_field($_POST['hero_image_id']));
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
}
add_action('save_post', 'save_homepage_meta');

/**
 * Register hero section settings
 */
function steget_register_hero_settings() {
    register_setting('steget_theme_options', 'steget_hero_title', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_hero_subtitle', 'sanitize_text_field');
    register_setting('steget_theme_options', 'steget_hero_image', 'esc_url_raw');
    register_setting('steget_theme_options', 'steget_hero_buttons', 'steget_sanitize_hero_buttons');
}
add_action('admin_init', 'steget_register_hero_settings');

/**
 * Sanitize hero buttons array
 */
function steget_sanitize_hero_buttons($input) {
    if (!is_array($input)) {
        return array();
    }

    $sanitized_input = array();

    foreach ($input as $button) {
        if (empty($button['text'])) {
            continue;
        }

        $sanitized_button = array(
            'text' => sanitize_text_field($button['text']),
            'url' => esc_url_raw($button['url']),
            'style' => in_array($button['style'], array('primary', 'secondary', 'outline'))
                ? $button['style'] : 'primary'
        );

        $sanitized_input[] = $sanitized_button;
    }

    return $sanitized_input;
}

/**
 * Add placeholder functions for the other sections so they don't cause errors
 */
function steget_render_featured_posts_section() {
    // Placeholder until you create the actual function
    echo '<div class="steget-admin-block"><h3>Featured Posts Section</h3><p>This section will be implemented soon.</p></div>';
}

function steget_render_cta_section() {
    // Placeholder until you create the actual function
    echo '<div class="steget-admin-block"><h3>CTA Section</h3><p>This section will be implemented soon.</p></div>';
}
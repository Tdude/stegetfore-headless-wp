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
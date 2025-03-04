<?php
/** inc/admin/module-enhancements.php
 *
 * Add CSS for the admin interface
 */
function add_module_admin_css() {
    global $typenow;

    if ($typenow === 'module') {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
}
add_action('admin_enqueue_scripts', 'add_module_admin_css');

/**
 * Display default content for each template type
 */
function module_template_default_content($content, $post) {
    if ($post->post_type !== 'module' || $content !== '') {
        return $content;
    }

    if (isset($_GET['template'])) {
        $template = sanitize_text_field($_GET['template']);

        switch ($template) {
            case 'hero':
                return '<h1>Hero Title</h1><p>This is a hero section with a compelling introduction to catch your visitor\'s attention.</p>';

            case 'selling_points':
                return '<h2>Our Key Benefits</h2><p>Discover why our solutions stand out from the competition.</p>';

            case 'stats':
                return '<h2>Our Numbers Speak</h2><p>See the impact we\'ve made through these key statistics.</p>';

            case 'testimonials':
                return '<h2>What Our Clients Say</h2><p>Read testimonials from our satisfied customers.</p>';

            case 'gallery':
                return '<h2>Image Gallery</h2><p>Explore our visual showcase highlighting our work.</p>';

            case 'faq':
                return '<h2>Frequently Asked Questions</h2><p>Find answers to common questions about our services.</p>';

            case 'tabbed_content':
                return '<h2>Tabbed Content</h2><p>Navigate through different sections of information in an organized way.</p>';

            case 'charts':
                return '<h2>Data Visualization</h2><p>Understand the numbers through clear and intuitive charts.</p>';

            case 'sharing':
                return '<h2>Share This Content</h2><p>Spread the word about our content through your favorite social networks.</p>';

            case 'login':
                return '<h2>Member Login</h2><p>Access your account to get personalized services and information.</p>';

            case 'payment':
                return '<h2>Secure Payment</h2><p>Complete your transaction with our secure payment system.</p>';

            case 'calendar':
                return '<h2>Schedule & Events</h2><p>View upcoming events or book appointments through our calendar system.</p>';

            case 'cta':
                return '<h2>Ready to Get Started?</h2><p>Take the next step towards achieving your goals with our solutions.</p>';

            case 'text_media':
                return '<h2>Featured Content</h2><p>Learn more about our services through this detailed overview with accompanying media.</p>';

            case 'video':
                return '<h2>Watch Our Video</h2><p>Get a visual introduction to our products and services.</p>';

            case 'form':
                return '<h2>Contact Us</h2><p>Fill out the form below and we\'ll get back to you shortly.</p>';
        }
    }

    return $content;
}
add_filter('default_content', 'module_template_default_content', 10, 2);

/**
 * Add modules submenu for easier management
 */
function add_modules_submenus() {
    add_submenu_page(
        'edit.php?post_type=module',
        __('All Templates', 'steget'),
        __('All Templates', 'steget'),
        'edit_posts',
        'edit.php?post_type=module'
    );

    // Add submenu items for each template type
    $templates = get_module_templates();

    foreach ($templates as $template_key => $template_name) {
        add_submenu_page(
            'edit.php?post_type=module',
            $template_name,
            $template_name,
            'edit_posts',
            'edit.php?post_type=module&module_template_filter=' . $template_key
        );
    }

    // Add categories and placements submenus
    add_submenu_page(
        'edit.php?post_type=module',
        __('Categories', 'steget'),
        __('Categories', 'steget'),
        'manage_categories',
        'edit-tags.php?taxonomy=module_category&post_type=module'
    );

    add_submenu_page(
        'edit.php?post_type=module',
        __('Placements', 'steget'),
        __('Placements', 'steget'),
        'manage_categories',
        'edit-tags.php?taxonomy=module_placement&post_type=module'
    );
}
add_action('admin_menu', 'add_modules_submenus');

/**
 * Add template specific admin body classes
 */
function add_module_admin_body_class($classes) {
    global $post, $typenow;

    if ($typenow === 'module' && is_admin() && isset($_GET['post'])) {
        $template = get_post_meta($_GET['post'], 'module_template', true);
        if ($template) {
            $classes .= ' module-template-' . $template;
        }
    }

    return $classes;
}
add_filter('admin_body_class', 'add_module_admin_body_class');

/**
 * Add a create module dropdown menu in the admin bar
 */
function add_new_module_admin_bar_menu($admin_bar) {
    if (!current_user_can('edit_posts')) {
        return;
    }

    $admin_bar->add_menu([
        'id'    => 'new-module',
        'title' => __('Module', 'steget'),
        'parent' => 'new-content',
        'href'  => admin_url('post-new.php?post_type=module')
    ]);

    $templates = get_module_templates();

    foreach ($templates as $template_key => $template_name) {
        $admin_bar->add_menu([
            'id'    => 'new-module-' . $template_key,
            'title' => $template_name,
            'parent' => 'new-module',
            'href'  => admin_url('post-new.php?post_type=module&template=' . $template_key)
        ]);
    }
}
add_action('admin_bar_menu', 'add_new_module_admin_bar_menu', 80);

/**
 * Set template when creating a new module from admin bar
 */
function set_module_template_on_create() {
    global $pagenow, $typenow;

    if ($pagenow === 'post-new.php' && $typenow === 'module' && isset($_GET['template'])) {
        add_action('admin_footer', 'auto_select_module_template');
    }
}
add_action('admin_init', 'set_module_template_on_create');

/**
 * Auto-select template using JavaScript
 */
function auto_select_module_template() {
    if (!isset($_GET['template'])) {
        return;
    }

    $template = sanitize_text_field($_GET['template']);
    ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#module_template').val('<?php echo esc_js($template); ?>').trigger('change');
});
</script>
<?php
}
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
                return '<h1>Hero Titel</h1><p>Det här är dafaulttexten till en hero.</p>';

            case 'selling_points':
                return '<h2>Fördelar med osss</h2><p>Detta är kort som man kan lista tre i bredd.</p>';

            case 'stats':
                return '<h2>Statistik</h2><p>Här kan man presentera kul siffror.</p>';

            case 'testimonials':
                return '<h2>Vad våra klienter säger</h2><p>Läs recensioner från våra nöjdaste kunder.</p>';

            case 'gallery':
                return '<h2>Galleri</h2><p>Här kan man presentera tidigare arbete på ett snyggt sätt.</p>';

            case 'faq':
                return '<h2>Vanliga frågor</h2><p>En vanlig FAQ som kan vara med kollapsbara svar.</p>';

            case 'tabbed_content':
                return '<h2>Flikar med innehåll</h2><p>Navigera med flikar där du kan presentera en massa innehåll utan att scrolla.</p>';

            case 'charts':
                return '<h2>Datavisualiseringar</h2><p>Grafer och grejs kan det vara här.</p>';

            case 'sharing':
                return '<h2>Dela detta innehåll</h2><p>Länkar till sociala medier kan vara här.</p>';

            case 'login':
                return '<h2>Login</h2><p>Sajten kan bli mer personlig och man kan ta betalt för innehåll.</p>';

            case 'payment':
                return '<h2>Säker betalning</h2><p>Ett transaktionssytem kan man ha på flera sidor.</p>';

            case 'calendar':
                return '<h2>Kalender och event</h2><p>Ett kalendersystem kan vara bra för att visa när man är bokad.</p>';

            case 'cta':
                return '<h2>Starta nu!</h2><p>En knapp med uppmaning trivs bra där man vill sälja något.</p>';

            case 'text_media':
                return '<h2>I fokus</h2><p>Välj ut en kategori med poster och några av inläggen kan hamna på tex. startsidan.</p>';

            case 'video':
                return '<h2>Video</h2><p>Ibland kan det vara bra att presentera något genom en vijå.</p>';

            case 'form':
                return '<h2>Kontakt Us</h2><p>Ett kontaktformulär så besökaren kan höra av sig.</p>';
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
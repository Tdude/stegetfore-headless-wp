<?php
/*
 * templates/fallback/index.php
 *
 * */
if (get_option('headless_mode_enabled')) {
    wp_redirect(get_option('headless_frontend_url'));
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header>
        <?php if (has_custom_logo()): ?>
            <?php the_custom_logo(); ?>
        <?php else: ?>
            <h1><?php bloginfo('name'); ?></h1>
        <?php endif; ?>
    </header>

    <main>
        <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <article>
                <h2><?php the_title(); ?></h2>
                <?php the_content(); ?>
            </article>
        <?php endwhile; endif; ?>
    </main>

    <?php wp_footer(); ?>
</body>
</html>

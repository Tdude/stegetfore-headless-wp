<?php
/**
 * Minimal index file
 */

// If this file is accessed directly, show a message
if (!defined('ABSPATH')) {
    echo 'Direct access not allowed';
    exit;
}

// Basic HTML structure
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="content">
        <h1>Headless Theme Active</h1>
        <p>If you see this message, the theme is working correctly.</p>
    </div>
    <?php wp_footer(); ?>
</body>

</html>
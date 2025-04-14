<?php
/**
 * Module UI main file - includes all module components
 * 
 * @package Steget
 */

// Base module functionality
require_once get_template_directory() . '/inc/admin/modules/base.php';

// Template fields handlers
require_once get_template_directory() . '/inc/admin/modules/template-fields.php';

// Content types
require_once get_template_directory() . '/inc/admin/modules/content-types/hero.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/featured-posts.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/testimonials.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/gallery.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/faq.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/tabbed-content.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/chart.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/sharing.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/login.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/payment.php';
require_once get_template_directory() . '/inc/admin/modules/content-types/calendar.php';

// Other module components
require_once get_template_directory() . '/inc/admin/modules/buttons.php';
require_once get_template_directory() . '/inc/admin/modules/saving.php';
require_once get_template_directory() . '/inc/admin/modules/admin-list.php';

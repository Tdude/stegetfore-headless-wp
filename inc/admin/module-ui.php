<?php
/** inc/admin/module-ui.php
 * Admin UI related methods
 *
 * This file has been refactored to improve maintainability.
 * Each component now has its own file in the /inc/admin/modules/ directory.
 */

// Include the refactored module UI components
require_once get_template_directory() . '/inc/admin/modules/base.php';
require_once get_template_directory() . '/inc/admin/modules/template-fields.php';
require_once get_template_directory() . '/inc/admin/modules/buttons.php';
require_once get_template_directory() . '/inc/admin/modules/saving.php';
require_once get_template_directory() . '/inc/admin/modules/admin-list.php';

// Include module content types
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
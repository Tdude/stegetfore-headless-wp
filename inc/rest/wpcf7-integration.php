<?php
/**
 * Contact Form 7 Frontend Integration for Headless WordPress
 * /inc/rest/wpcf7-integration.php
 * Handles:
 * - Adding WPCF7 form IDs to REST API responses
 * - Custom headless WPCF7 shortcode
 * - Removing WPCF7 markup from content when accessed via REST
 * - Ensuring proper UTF-8 encoding for responses
 */

if (!defined('ABSPATH')) exit;

/**
 * Modifies REST API responses to include CF7 form IDs and clean up content
 */
add_filter('rest_prepare_page', function ($response, $post, $request) {
    $data = $response->get_data();

    // Process only if we have content
    if (isset($data['content']['rendered'])) {
        // Check for CF7 shortcode
        if (has_shortcode($post->post_content, 'contact-form-7') || strpos($data['content']['rendered'], 'class="wpcf7') !== false) {
            // Extract form ID
            $form_id = null;

            // Try to get from data attribute or shortcode
            if (preg_match('/data-wpcf7-id="(\d+)"/', $data['content']['rendered'], $matches)) {
                $form_id = $matches[1];
            } elseif (preg_match('/\[contact-form-7\s+id="(\d+)"/', $post->post_content, $matches)) {
                $form_id = $matches[1];
            }

            // If form ID found, add it to the response
            if ($form_id) {
                // Add it as a direct property in the API response
                $data['cf7_form_id'] = $form_id;

                // Remove the CF7 HTML from the content
                $data['content']['rendered'] = preg_replace('/<div[^>]*class="[^"]*wpcf7[^"]*"[^>]*>[\s\S]*?<\/div>(?:\s*<div[^>]*class="[^"]*wpcf7-response-output[^"]*"[^>]*>[\s\S]*?<\/div>)?/s', '', $data['content']['rendered']);

                // Also remove any shortcodes
                $data['content']['rendered'] = str_replace('[contact-form-7 id="' . $form_id . '"', '', $data['content']['rendered']);
            }

            $response->set_data($data);
        }
    }

    return $response;
}, 10, 3);

/**
 * Register CF7 form ID in REST API for easier access
 */
add_action('rest_api_init', function () {
    register_rest_field('page', 'cf7_form_id', array(
        'get_callback' => function ($post) {
            $content = get_post_field('post_content', $post['id']);

            // Check for CF7 shortcode
            if (has_shortcode($content, 'contact-form-7')) {
                $pattern = '/\[contact-form-7\s+id="(\d+)"/';
                if (preg_match($pattern, $content, $matches)) {
                    return $matches[1];
                }
            }

            return null;
        },
        'schema' => array(
            'type' => 'string',
            'description' => 'Contact Form 7 ID if present on the page',
        ),
    ));
});

/**
 * Add support for our custom headless CF7 shortcode
 */
add_shortcode('headless-cf7', function ($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);

    if (empty($atts['id'])) {
        return '';
    }

    // Just return a marker div that React can use to inject the form
    return sprintf(
        '<div id="headless-cf7-%s" class="headless-cf7-placeholder" data-form-id="%s"></div>',
        esc_attr($atts['id']),
        esc_attr($atts['id'])
    );
});

/**
 * Ensure proper encoding for API responses
 */
add_action('init', function () {
    // Set internal encoding to UTF-8
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
    }
});

/**
 * Ensure UTF-8 headers for REST API
 */
add_action('rest_api_init', function () {
    // Add UTF-8 header to all REST responses
    add_filter('rest_pre_serve_request', function ($served, $result) {
        header('Content-Type: application/json; charset=utf-8');
        return $served;
    }, 10, 2);
});
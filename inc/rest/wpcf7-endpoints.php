<?php
/** inc/rest/wpcf7-endpoints.php
 * Enhanced REST API endpoints for Contact Form 7
 */

// Register the custom WPCF7 endpoints
function register_wpcf7_endpoints() {
    // Get form structure endpoint
    register_rest_route('steget/v1', '/cf7/form/(?P<id>[\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_cf7_form_data',
        'permission_callback' => '__return_true',
    ));

    // Submit form endpoint
    register_rest_route('steget/v1', '/cf7/submit/(?P<id>[\w-]+)', array(
        'methods' => 'POST',
        'callback' => 'submit_cf7_form',
        'permission_callback' => '__return_true',
    ));

    // List all available forms endpoint
    register_rest_route('steget/v1', '/cf7/forms', array(
        'methods' => 'GET',
        'callback' => 'list_cf7_forms',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_wpcf7_endpoints', 10);

/**
 * List all available Contact Form 7 forms
 */
function list_cf7_forms() {
    if (!class_exists('WPCF7_ContactForm')) {
        return new WP_Error(
            'cf7_not_active',
            'Contact Form 7 plugin is not active',
            array('status' => 500)
        );
    }

    $forms = WPCF7_ContactForm::find();
    $forms_data = array();

    foreach ($forms as $form) {
        $forms_data[] = array(
            'id' => $form->id(),
            'title' => $form->title(),
            'shortcode' => sprintf('[contact-form-7 id="%d" title="%s"]', $form->id(), $form->title()),
        );
    }

    return rest_ensure_response($forms_data);
}

/**
 * Get Contact Form 7 form fields and structure
 */
function get_cf7_form_data($request) {
    $form_id = $request['id'];
    error_log("Attempting to fetch CF7 form with ID: " . $form_id);

    // Try direct lookup first
    $form = wpcf7_contact_form($form_id);

    // If not found and ID looks like a hash prefix, try lookup by meta
    if (!$form && !is_numeric($form_id)) {
        global $wpdb;

        // Look for forms with _hash meta value starting with the requested ID
        $numeric_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_hash' AND meta_value LIKE %s",
            $form_id . '%'
        ));

        if ($numeric_id) {
            error_log("Found numeric ID: " . $numeric_id . " for hash prefix: " . $form_id);
            $form = wpcf7_contact_form($numeric_id);
        } else {
            // Fallback to more exhaustive search if needed
            $all_forms = WPCF7_ContactForm::find();
            foreach ($all_forms as $cf7_form) {
                if ($form_id === $cf7_form->id() || $form_id === (string)$cf7_form->id()) {
                    $form = $cf7_form;
                    error_log("Found form through exhaustive search: " . $cf7_form->id());
                    break;
                }
            }
        }
    }

    // Return error if form still not found
    if (!$form) {
        error_log("Form not found with ID: " . $form_id);
        return new WP_Error(
            'form_not_found',
            'The requested form was not found',
            array('status' => 404)
        );
    }

    // Get form properties
    $properties = $form->get_properties();

    // Parse the form to extract fields
    $tags = $form->scan_form_tags();

    $form_fields = array();
    // Process each tag
    foreach ($tags as $tag) {
        // Skip non-input fields like submit buttons
        if (!in_array($tag->basetype, array('text', 'email', 'url', 'tel', 'textarea', 'select', 'checkbox', 'radio', 'file'))) {
            continue;
        }

        // Get field attributes
        $field = array(
            'id' => $tag->name,
            'type' => $tag->type,
            'basetype' => $tag->basetype,
            'name' => $tag->name,
            'required' => (strpos($tag->type, '*') !== false),
            'raw_values' => $tag->raw_values,
            'values' => $tag->values,
            'labels' => array(),
            'placeholder' => '', // Initialize placeholder
            'options' => $tag->options, // Include any options set on the field
        );

        // Extract placeholder from options if it exists
        foreach ($tag->options as $option) {
            if (strpos($option, 'placeholder:') === 0) {
                $field['placeholder'] = substr($option, 12); // Remove 'placeholder:' prefix
            }
        }

        // Extract labels from the form structure
        // This is a more complex approach to find labels associated with fields
        $form_content = $properties['form'];

        // Try to find label based on field name pattern in the form content
        $pattern = '/<label[^>]*>\s*(.*?)\s*<.*?(?:name="' . preg_quote($tag->name, '/') . '"|data-name="' . preg_quote($tag->name, '/') . '").*?<\/label>/s';
        if (preg_match($pattern, $form_content, $matches)) {
            // Clean up the label - remove any HTML tags and trim
            $label = trim(strip_tags($matches[1]));
            if (!empty($label)) {
                $field['labels'][] = $label;
            }
        }

        // If no label found by regex, use fallback approaches
        if (empty($field['labels'])) {
            // Map fields to their known labels based on name
            switch($tag->name) {
                case 'your-name':
                    $field['labels'][] = 'Your Name';
                    break;
                case 'your-email':
                    $field['labels'][] = 'Your Email';
                    break;
                case 'your-message':
                    $field['labels'][] = 'Message';
                    break;
                default:
                    // For any new fields, format the field name
                    $field['labels'][] = ucfirst(str_replace(array('-', '_'), ' ', $tag->name));
            }
        }

        $form_fields[] = $field;
    }

    // Prepare response data
    $data = array(
        'id' => $form_id,
        'title' => $form->title(),
        'fields' => $form_fields,
        'additional_settings' => $properties['additional_settings'],
        'locale' => $form->locale(),  // Add locale information
        'messages' => $properties['messages'], // Add form messages for validation
    );

    return rest_ensure_response($data);
}

/**
 * Consolidated CF7 form submission handler
 */
function submit_cf7_form($request) {
    error_log('=== CF7 SUBMISSION START ===');

    try {
        // Check if CF7 is active
        if (!class_exists('WPCF7_ContactForm')) {
            return new WP_Error(
                'cf7_not_active',
                'Contact Form 7 plugin is not active',
                ['status' => 500]
            );
        }

        // Get form ID
        $form_id = $request['id'];
        error_log('Form ID: ' . $form_id);

        // Get the form
        $form = wpcf7_contact_form($form_id);
        if (!$form) {
            error_log('Form not found: ' . $form_id);
            return new WP_Error(
                'form_not_found',
                'The requested form was not found',
                ['status' => 404]
            );
        }

        // Get form data from multiple possible sources
        $form_data = [];

        // 1. Try parameters in the URL
        $params = $request->get_params();
        foreach ($params as $key => $value) {
            if (!in_array($key, ['id', 'route', 'rest_route'])) {
                $form_data[$key] = $value;
            }
        }

        // 2. Try body parameters
        if (empty($form_data)) {
            $body_params = $request->get_body_params();
            if (!empty($body_params)) {
                $form_data = $body_params;
            }
        }

        // 3. Try JSON data
        if (empty($form_data)) {
            $content_type = $request->get_content_type();
            if (!empty($content_type) && strpos($content_type['value'], 'application/json') !== false) {
                $json_data = json_decode($request->get_body(), true);
                if ($json_data) {
                    $form_data = $json_data;
                }
            }
        }

        // 4. Try raw body parsing
        if (empty($form_data)) {
            $body = $request->get_body();
            if (!empty($body)) {
                parse_str($body, $parsed_body);
                if (!empty($parsed_body)) {
                    $form_data = $parsed_body;
                }
            }
        }

        // 5. Check files
        $files = $request->get_file_params();
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $form_data[$key] = $file;
            }
        }

        error_log('Form data collected: ' . print_r($form_data, true));

        if (empty($form_data)) {
            error_log('No form data found in request');
            return new WP_Error(
                'no_data',
                'No form data received',
                ['status' => 400]
            );
        }

        // Submit the form
        $result = $form->submit($form_data);

        if ($result) {
            error_log('Form submission successful');
            return [
                'status' => 'mail_sent',
                'message' => $form->message('mail_sent_ok'),
            ];
        } else {
            error_log('Form submission failed');
            return [
                'status' => 'mail_failed',
                'message' => $form->message('mail_sent_ng'),
            ];
        }

    } catch (Exception $e) {
        error_log('EXCEPTION: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());

        return new WP_Error(
            'exception',
            'Exception: ' . $e->getMessage(),
            ['status' => 500]
        );
    } finally {
        error_log('=== CF7 SUBMISSION END ===');
    }

    // Fallback response if none of the above paths return
    return [
        'status' => 'mail_failed',
        'message' => 'An unexpected error occurred processing the form'
    ];
}
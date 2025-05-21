<?php
/** inc/rest/wpcf7-endpoints.php
 * Enhanced REST API endpoints for Contact Form 7
 *
 *  Register custom WPCF7 endpoints
 */
function register_wpcf7_endpoints() {
    // Get form structure endpoint
    register_rest_route('steget/v1', '/cf7/form/(?P<id>[\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_cf7_form_data_custom',
        'permission_callback' => '__return_true',
    ));

    // Submit form endpoint
    register_rest_route('steget/v1', '/cf7/submit/(?P<id>[\w-]+)', array(
        'methods' => 'POST',
        'callback' => 'submit_cf7_form_custom',
        'permission_callback' => '__return_true',
    ));

    // List all available forms endpoint
    register_rest_route('steget/v1', '/cf7/forms', array(
        'methods' => 'GET',
        'callback' => 'list_cf7_forms_custom',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_wpcf7_endpoints', 10);

/**
 * List all available Contact Form 7 forms
 */
function list_cf7_forms_custom() {
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
function get_cf7_form_data_custom($request) {
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
 * Improved CF7 form submission handler with detailed logging
 */
function submit_cf7_form_custom($request) {
    $form_id = $request['id'];
    error_log('CF7 REST: submit_cf7_form_custom called for form ID: ' . $form_id);
    error_log('CF7 REST: POST data: ' . print_r($request->get_params(), true));

    $form = wpcf7_contact_form($form_id);
    if (!$form) {
        error_log('CF7 REST: No form found for ID: ' . $form_id);
        return new WP_Error(
            'cf7_form_not_found',
            'Contact Form 7 form not found',
            array('status' => 404)
        );
    }

    // Get the form
    error_log('CF7 REST: Form found: ' . $form->name() . ' (ID: ' . $form->id() . ')');

    // Get form data from multiple possible sources
    $form_data = [];

    // 1. Try JSON data first (if Content-Type is application/json)
    $content_type = $request->get_content_type();
    if (!empty($content_type) && strpos($content_type['value'], 'application/json') !== false) {
        $json_data = json_decode($request->get_body(), true);
        if ($json_data) {
            error_log('CF7 REST: Parsed JSON data: ' . json_encode($json_data));
            $form_data = $json_data;
        }
    }

    // 2. Try body parameters (application/x-www-form-urlencoded)
    if (empty($form_data)) {
        $body_params = $request->get_body_params();
        if (!empty($body_params)) {
            error_log('CF7 REST: Found body params: ' . json_encode($body_params));
            $form_data = $body_params;
        }
    }

    // 3. Try parameters in the URL
    if (empty($form_data)) {
        $params = $request->get_params();
        foreach ($params as $key => $value) {
            if (!in_array($key, ['id', 'route', 'rest_route'])) {
                $form_data[$key] = $value;
            }
        }
        if (!empty($form_data)) {
            error_log('CF7 REST: Found URL params: ' . json_encode($form_data));
        }
    }

    // 4. Try raw body parsing
    if (empty($form_data) && !empty($request->get_body())) {
        parse_str($request->get_body(), $parsed_body);
        if (!empty($parsed_body)) {
            error_log('CF7 REST: Parsed raw body: ' . json_encode($parsed_body));
            $form_data = $parsed_body;
        }
    }

    // 5. Check files
    $files = $request->get_file_params();
    if (!empty($files)) {
        error_log('CF7 REST: Found files: ' . json_encode(array_keys($files)));
        foreach ($files as $key => $file) {
            $form_data[$key] = $file;
        }
    }

    // 6. Debug: Check $_POST directly
    if (!empty($_POST)) {
        error_log('CF7 REST: Raw $_POST data: ' . json_encode($_POST));
    }

    error_log('CF7 REST: Final form data for submission: ' . json_encode($form_data));

    if (empty($form_data)) {
        error_log('CF7 REST: No form data found in request');
        return new WP_Error(
            'no_data',
            'No form data received',
            ['status' => 400]
        );
    }

    // Log form properties before submission
    $form_properties = $form->get_properties();
    error_log('CF7 REST: Form mail settings: ' . json_encode($form_properties['mail']));

    // Inject form data into $_POST so CF7 can find it (fixes REST validation issues)
    foreach ($form_data as $key => $value) {
        $_POST[$key] = $value;
    }

    // Create a CF7 submission object
    WPCF7_Submission::get_instance([
        'skip_mail' => false,
    ]);

    // Submit the form
    error_log('CF7 REST: Submitting form...');
    $result = $form->submit($form_data);
    error_log('CF7 REST: Submission result: ' . json_encode($result));

    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        error_log('CF7 REST: Submission created');

        // Get validation errors if any
        $validation_errors = $submission->get_invalid_fields();
        if (!empty($validation_errors)) {
            error_log('CF7 REST: Validation errors: ' . json_encode($validation_errors));

            return [
                'status' => 'validation_failed',
                'message' => $form->message('validation_error'),
                'errors' => $validation_errors
            ];
        }

        // Check if mail was sent
        $mail_sent = $submission->get_status() === 'mail_sent';
        error_log('CF7 REST: Mail sent status: ' . ($mail_sent ? 'TRUE' : 'FALSE'));

        if ($mail_sent) {
            return [
                'status' => 'mail_sent',
                'message' => $form->message('mail_sent_ok'),
            ];
        } else {
            $error_message = $form->message('mail_sent_ng');
            error_log('CF7 REST: Mail sending failed. Error: ' . $error_message);

            return [
                'status' => 'mail_failed',
                'message' => $error_message,
            ];
        }
    } else {
        error_log('CF7 REST: No submission instance found');

        return [
            'status' => 'mail_failed',
            'message' => 'The submission process failed.',
        ];
    }

    // Fallback response if none of the above paths return
    return [
        'status' => 'mail_failed',
        'message' => 'An unexpected error occurred processing the form'
    ];
}

// Add hook to enable CORS for API requests
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');

    add_filter('rest_pre_serve_request', function($value) {
        // header('Access-Control-Allow-Origin: *'); // Commented out to allow global CORS settings to take precedence
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

        return $value;
    });
}, 15);
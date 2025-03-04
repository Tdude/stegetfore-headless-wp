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
                    $field['labels'][] = 'Ditt namn';
                    break;
                case 'your-email':
                    $field['labels'][] = 'Din e-post';
                    break;
                case 'your-message':
                    $field['labels'][] = 'Meddelande';
                    break;
                default:
                    // For any new fields, format the field name
                    $field['labels'][] = ucfirst(str_replace('-', ' ', $tag->name));
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
 * Submit Contact Form 7 form with enhanced debugging
 */
function submit_cf7_form($request) {
    // Enable error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_log('=== START CF7 SUBMISSION DEBUG ===');

    try {
        // Check if CF7 is active
        error_log('Checking if CF7 class exists...');
        if (!class_exists('WPCF7_ContactForm')) {
            error_log('WPCF7_ContactForm class not found!');
            return new WP_Error(
                'cf7_not_active',
                'Contact Form 7 plugin is not active',
                array('status' => 500)
            );
        }

        // Get form ID
        $form_id = $request['id'];
        error_log('Form ID: ' . $form_id);

        // Load the form
        error_log('Loading form...');
        $form = wpcf7_contact_form($form_id);

        if (!$form) {
            error_log('Form not found with ID: ' . $form_id);
            return new WP_Error(
                'form_not_found',
                'The requested form was not found',
                array('status' => 404)
            );
        }

        error_log('Form loaded successfully: ' . $form->name());

        // Get all parameters from the request
        $params = $request->get_params();
        error_log('Request parameters: ' . print_r($params, true));

        // Extract form data (excluding route parameters)
        $form_data = array();
        foreach ($params as $key => $value) {
            if ($key !== 'id' && $key !== 'route' && $key !== 'rest_route') {
                $form_data[$key] = $value;
            }
        }

        error_log('Extracted form data: ' . print_r($form_data, true));

        // Check if we have any form data
        if (empty($form_data)) {
            error_log('No form data found in request. Checking request body...');

            // Try to get data from request body
            $body = $request->get_body();
            error_log('Raw request body: ' . $body);

            if (!empty($body)) {
                parse_str($body, $parsed_body);
                error_log('Parsed body: ' . print_r($parsed_body, true));

                if (!empty($parsed_body)) {
                    $form_data = $parsed_body;
                }
            }

            // Check files
            $files = $request->get_file_params();
            if (!empty($files)) {
                error_log('Files in request: ' . print_r($files, true));
            }
        }

        // If we still have no data, check if it's JSON
        if (empty($form_data) && strpos($request->get_content_type()['value'], 'application/json') !== false) {
            error_log('Attempting to parse JSON body...');
            $json_data = json_decode($request->get_body(), true);
            if ($json_data) {
                error_log('JSON data: ' . print_r($json_data, true));
                $form_data = $json_data;
            }
        }

        // Last resort - check $_POST directly
        if (empty($form_data) && !empty($_POST)) {
            error_log('Using $_POST data: ' . print_r($_POST, true));
            $form_data = $_POST;
        }

        // If still no data, that's an error
        if (empty($form_data)) {
            error_log('ERROR: No form data found in request!');
            return new WP_Error(
                'no_data',
                'No form data received',
                array('status' => 400)
            );
        }

        // Submit the form - try direct method first
        error_log('Attempting to submit form using direct method...');

        // Method 1: Try direct submission
        $result = $form->submit($form_data);
        error_log('Direct submission result: ' . print_r($result, true));

        // If that didn't work, try the WPCF7_Submission approach
        if (empty($result) || (isset($result['status']) && $result['status'] === 'validation_failed')) {
            error_log('Validation failed or empty result. Trying WPCF7_Submission...');

            // Method 2: Use submission class
            $submission = WPCF7_Submission::get_instance();

            /*
            if (!$submission) {
                error_log('Creating new WPCF7_Submission instance...');

                // Need to set up environment for submission
                $_POST = $form_data;
                $_SERVER['REQUEST_METHOD'] = 'POST';

                $submission_args = array(
                    'form' => $form,
                );

                $submission = new WPCF7_Submission($submission_args);
                error_log('New submission instance created');
            } else {
                error_log('Found existing submission instance');
            }
            */

            // Check submission result
            if ($submission->is_valid()) {
                error_log('Submission is valid!');
                $result = array(
                    'status' => 'mail_sent',
                    'message' => $form->message('mail_sent_ok'),
                );
            } else {
                error_log('Submission is invalid');
                $result = array(
                    'status' => 'validation_failed',
                    'message' => $form->message('validation_error'),
                    'invalid_fields' => $submission->get_invalid_fields(),
                );
            }
        }

        // Ensure we have a valid result
        if (empty($result) || !is_array($result)) {
            error_log('Empty or invalid result, creating default error response');
            $result = array(
                'status' => 'mail_failed',
                'message' => 'Error processing form submission',
            );
        }

        // Format the response
        $response = array(
            'status' => isset($result['status']) ? $result['status'] : 'mail_failed',
            'message' => isset($result['message']) ? $result['message'] : 'Error processing form',
        );

        // Add validation errors if present
        if (isset($result['invalid_fields']) && !empty($result['invalid_fields'])) {
            $invalid_fields = array();
            foreach ($result['invalid_fields'] as $field_name => $field_error) {
                $invalid_fields[] = array(
                    'field' => $field_name,
                    'message' => isset($field_error['reason']) ? $field_error['reason'] : 'Invalid input',
                );
            }
            $response['invalidFields'] = $invalid_fields;
        }

        error_log('Final response: ' . print_r($response, true));
        error_log('=== END CF7 SUBMISSION DEBUG ===');

        return rest_ensure_response($response);

    } catch (Exception $e) {
        error_log('EXCEPTION: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        return new WP_Error(
            'exception',
            'Exception: ' . $e->getMessage(),
            array('status' => 500)
        );
    }
}

/**
 * Register a simpler alternative endpoint for CF7 submission
 */
function register_simple_cf7_submit_endpoint() {
    register_rest_route('steget/v1', '/cf7/simple-submit/(?P<id>[\w-]+)', array(
        'methods' => 'POST',
        'callback' => 'simple_submit_cf7_form',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_simple_cf7_submit_endpoint', 10);

/**
 * A simplified approach to CF7 form submission with proper mail template handling
 */
function simple_submit_cf7_form($request) {
    error_log('=== SIMPLE CF7 SUBMISSION ===');

    // Check if CF7 is active
    if (!class_exists('WPCF7_ContactForm')) {
        error_log('CF7 not active');
        return new WP_Error(
            'cf7_not_active',
            'Contact Form 7 plugin is not active',
            array('status' => 500)
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
            array('status' => 404)
        );
    }

    // Get form data from various possible sources
    $form_data = array();

    // 1. Try to get data from URL parameters
    $params = $request->get_params();
    foreach ($params as $key => $value) {
        if ($key !== 'id' && $key !== 'route' && $key !== 'rest_route') {
            $form_data[$key] = $value;
        }
    }

    // 2. If not found in URL params, try POST data
    if (empty($form_data)) {
        $post_data = $request->get_body_params();
        if (!empty($post_data)) {
            $form_data = $post_data;
        }
    }

    // 3. Check if it's JSON
    if (empty($form_data)) {
        $content_type = $request->get_content_type();
        if (!empty($content_type) && strpos($content_type['value'], 'application/json') !== false) {
            $json_data = json_decode($request->get_body(), true);
            if ($json_data) {
                $form_data = $json_data;
            }
        }
    }

    // 4. Try parsing the body
    if (empty($form_data)) {
        $body = $request->get_body();
        if (!empty($body)) {
            parse_str($body, $parsed_body);
            if (!empty($parsed_body)) {
                $form_data = $parsed_body;
            }
        }
    }

    // Log what we found
    error_log('Form data: ' . print_r($form_data, true));

    // If no data found, return error
    if (empty($form_data)) {
        return new WP_Error(
            'no_data',
            'No form data received',
            array('status' => 400)
        );
    }

    // Basic validation for required fields
    $invalid_fields = array();
    $tags = $form->scan_form_tags();

    foreach ($tags as $tag) {
        // Skip non-input fields
        if (!in_array($tag->basetype, array('text', 'email', 'url', 'tel', 'textarea', 'select', 'checkbox', 'radio', 'file'))) {
            continue;
        }

        // Check if field is required (has * in type)
        $is_required = (strpos($tag->type, '*') !== false);

        if ($is_required) {
            // Check if the required field is empty
            if (!isset($form_data[$tag->name]) || empty($form_data[$tag->name])) {
                $invalid_fields[$tag->name] = array(
                    'reason' => 'Fyll i detta fält.'
                );
            }
        }

        // Special validation for email fields
        if ($tag->basetype === 'email' && !empty($form_data[$tag->name])) {
            if (!filter_var($form_data[$tag->name], FILTER_VALIDATE_EMAIL)) {
                $invalid_fields[$tag->name] = array(
                    'reason' => 'Ange en giltig e-postadress.'
                );
            }
        }
    }

    // If we have validation errors, return them
    if (!empty($invalid_fields)) {
        $invalid_fields_response = array();
        foreach ($invalid_fields as $field => $error) {
            $invalid_fields_response[] = array(
                'field' => $field,
                'message' => $error['reason']
            );
        }

        return rest_ensure_response(array(
            'status' => 'validation_failed',
            'message' => 'Ett eller flera fält har ett fel. Kontrollera och försök igen.',
            'invalidFields' => $invalid_fields_response
        ));
    }

    // Get mail props from the form
    $mail_props = $form->prop('mail');
    $mail_2_props = $form->prop('mail_2');

    // Create a submission-like data structure for mail processing
    $submission_data = array();
    foreach ($form_data as $key => $value) {
        $submission_data[$key] = $value;
    }

    // Process special mail tags
    $special_mail_tags = array(
        '_site_title' => get_bloginfo('name'),
        '_site_description' => get_bloginfo('description'),
        '_site_url' => get_bloginfo('url'),
        '_site_admin_email' => get_bloginfo('admin_email'),
        '_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
        '_user_ip' => $_SERVER['REMOTE_ADDR'],
        '_remote_ip' => $_SERVER['REMOTE_ADDR'],
        '_date' => date_i18n(get_option('date_format')),
        '_time' => date_i18n(get_option('time_format')),
        '_post_id' => 0, // Could set this if needed
        '_post_title' => '', // Could set this if needed
        '_post_url' => '', // Could set this if needed
        '_post_author' => '', // Could set this if needed
        '_post_author_email' => '', // Could set this if needed
    );

    // Process mail template to replace both form fields and special tags
    $process_mail_template = function($template) use ($form_data, $special_mail_tags) {
        // First replace regular form fields
        foreach ($form_data as $key => $value) {
            $template = str_replace("[$key]", $value, $template);
        }

        // Then replace special mail tags
        foreach ($special_mail_tags as $tag => $value) {
            $template = str_replace("[$tag]", $value, $template);
        }

        // Also try to replace the default subject if it exists
        if (strpos($template, '[your-subject]') !== false && !isset($form_data['your-subject'])) {
            $default_subject = "Meddelande från " . get_bloginfo('name');
            $template = str_replace('[your-subject]', $default_subject, $template);
        }

        return $template;
    };

    // Process the main mail
    $to = $process_mail_template($mail_props['recipient']);
    $subject = $process_mail_template($mail_props['subject']);
    $body = $process_mail_template($mail_props['body']);
    $headers = array();

    // From header
    if (!empty($mail_props['sender'])) {
        $sender = $process_mail_template($mail_props['sender']);
        $headers[] = 'From: ' . $sender;
    }

    // Reply-To header (usually the user's email)
    if (!empty($form_data['your-email']) && filter_var($form_data['your-email'], FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $form_data['your-email'];
    }

    // Content type header
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    // Add any additional headers
    if (!empty($mail_props['additional_headers'])) {
        $additional_headers = explode("\n", $process_mail_template($mail_props['additional_headers']));
        foreach ($additional_headers as $header) {
            if (!empty($header)) {
                $headers[] = trim($header);
            }
        }
    }

    // Log mail details for debugging
    error_log('Sending mail to: ' . $to);
    error_log('Subject: ' . $subject);
    error_log('Headers: ' . print_r($headers, true));
    error_log('Body: ' . $body);

    // Send the mail
    $mail_sent = wp_mail($to, $subject, $body, $headers);
    error_log('Primary email sent: ' . ($mail_sent ? 'YES' : 'NO'));

    // Process and send auto-responder mail if configured
    $mail_2_sent = false;
    if (!empty($mail_2_props) && $mail_2_props['active'] && !empty($form_data['your-email'])) {
        $to_2 = $form_data['your-email'];
        $subject_2 = $process_mail_template($mail_2_props['subject']);
        $body_2 = $process_mail_template($mail_2_props['body']);
        $headers_2 = array();

        // From header for auto-responder
        if (!empty($mail_2_props['sender'])) {
            $sender_2 = $process_mail_template($mail_2_props['sender']);
            $headers_2[] = 'From: ' . $sender_2;
        }

        // Content type header
        $headers_2[] = 'Content-Type: text/html; charset=UTF-8';

        // Add any additional headers for the auto-responder
        if (!empty($mail_2_props['additional_headers'])) {
            $additional_headers_2 = explode("\n", $process_mail_template($mail_2_props['additional_headers']));
            foreach ($additional_headers_2 as $header) {
                if (!empty($header)) {
                    $headers_2[] = trim($header);
                }
            }
        }

        // Log auto-responder details
        error_log('Sending auto-responder to: ' . $to_2);
        error_log('Subject: ' . $subject_2);

        // Send the auto-responder
        $mail_2_sent = wp_mail($to_2, $subject_2, $body_2, $headers_2);
        error_log('Auto-responder sent: ' . ($mail_2_sent ? 'YES' : 'NO'));
    }

    // Return the appropriate response
    if ($mail_sent) {
        return rest_ensure_response(array(
            'status' => 'mail_sent',
            'message' => 'Tack för ditt meddelande. Det har skickats.',
            'mail2_sent' => $mail_2_sent
        ));
    } else {
        return rest_ensure_response(array(
            'status' => 'mail_failed',
            'message' => 'Det var ett fel vid försöket att skicka ditt meddelande. Försök igen senare.'
        ));
    }
}
<?php
/** inc/rest/wpcf7-endpoints.php
 * Custom REST API endpoints for Contact Form 7
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
}
add_action('rest_api_init', 'register_wpcf7_endpoints');

/**
 * Get Contact Form 7 form fields and structure
 */
function get_cf7_form_data($request) {
    $form_id = $request['id'];

    // Check if the form exists
    $form = wpcf7_contact_form($form_id);
    if (!$form) {
        return new WP_Error(
            'form_not_found',
            'The requested form was not found',
            array('status' => 404)
        );
    }

    // Get form properties
    $properties = $form->get_properties();
    $form_fields = array();

    // Parse the form to extract fields
    $tag_types = array('text', 'email', 'url', 'tel', 'textarea', 'select', 'checkbox', 'radio', 'file');
    $tags = $form->scan_form_tags();

    foreach ($tags as $tag) {
        // Only process actual input fields
        if (!in_array($tag['type'], $tag_types)) {
            continue;
        }

        // Get field attributes
        $field = array(
            'id' => $tag['name'],
            'type' => $tag['type'],
            'basetype' => $tag['basetype'],
            'name' => $tag['name'],
            'required' => $tag['required'],
            'placeholder' => isset($tag['options']['placeholder']) ? $tag['options']['placeholder'][0] : '',
            'raw_values' => $tag['raw_values'],
            'values' => $tag['values'],
            'labels' => array(),
        );

        // Extract label from form HTML (this is a bit hacky but necessary)
        $pattern = '/\<label[^>]*>(.*?)' . preg_quote($tag['name'], '/') . '.*?\<\/label\>/s';
        preg_match($pattern, $properties['form'], $label_matches);

        if (!empty($label_matches)) {
            $label = strip_tags($label_matches[0]);
            $label = preg_replace('/\s+/', ' ', $label);
            $label = trim(str_replace($tag['name'], '', $label));
            $field['labels'][] = $label;
        } else {
            // Try an alternative approach for labels
            preg_match('/\<label[^>]*>(.*?)\<\/label\>/s', $properties['form'], $general_label_matches);
            if (!empty($general_label_matches) && strpos($general_label_matches[0], $tag['name']) !== false) {
                $label = strip_tags($general_label_matches[0]);
                $label = preg_replace('/\s+/', ' ', $label);
                $field['labels'][] = trim($label);
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
    );

    return rest_ensure_response($data);
}

/**
 * Submit Contact Form 7 form
 */
function submit_cf7_form($request) {
    // Check if CF7 is active in WP
    if (!class_exists('WPCF7_ContactForm')) {
        return new WP_Error(
            'cf7_not_active',
            'Contact Form 7 plugin is not active',
            array('status' => 500)
        );
    }

    $form_id = $request['id'];
    $form = wpcf7_contact_form($form_id);

    if (!$form) {
        return new WP_Error(
            'form_not_found',
            'The requested form was not found',
            array('status' => 404)
        );
    }

    // Get posted data
    $data = $request->get_params();

    // Submit the form using CF7's built-in method
    $result = $form->submit();

    // Format the response
    $response = array(
        'status' => $result['status'],
        'message' => $result['message'],
    );

    if (isset($result['invalid_fields']) && !empty($result['invalid_fields'])) {
        $response['invalidFields'] = array_map(
            function($field_name, $field_error) {
                return array(
                    'field' => $field_name,
                    'message' => $field_error['reason'],
                );
            },
            array_keys($result['invalid_fields']),
            array_values($result['invalid_fields'])
        );
    }

    return rest_ensure_response($response);
}
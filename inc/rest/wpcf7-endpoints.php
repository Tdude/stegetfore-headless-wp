<?php
/** inc/rest/wpcf7-endpoints.php
 * Custom REST API endpoints for Contact Form 7
 */
function register_wpcf7_endpoints() {
    // Get form structure endpoint
    register_rest_route('headless/v1', '/cf7/form/(?P<id>[\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_cf7_form_data',
        'permission_callback' => '__return_true',
    ));

    // Submit form endpoint
    register_rest_route('headless/v1', '/cf7/submit/(?P<id>[\w-]+)', array(
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
        preg_match('/\<label\>(.*?)' . $tag['name'] . '.*?\<\/label\>/s', $properties['form'], $label_matches);
        if (!empty($label_matches)) {
            $label = strip_tags($label_matches[0]);
            $label = preg_replace('/\s+/', ' ', $label);
            $label = trim(str_replace($tag['name'], '', $label));
            $field['labels'][] = $label;
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

    // Get form submission data
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) {
        // Create a submission
        $submission = WPCF7_Submission::get_instance(
            array(
                'skip_mail' => false,
            )
        );
    }

    // Process the submission
    $result = $form->submit();

    // Format the response
    $response = array(
        'status' => $result['status'],
        'message' => $result['message'],
    );

    // Add validation errors if any
    if (isset($result['invalid_fields']) && !empty($result['invalid_fields'])) {
        $response['invalidFields'] = array();

        foreach ($result['invalid_fields'] as $field_name => $field_error) {
            $response['invalidFields'][] = array(
                'field' => $field_name,
                'message' => $field_error['reason'],
            );
        }
    }

    return rest_ensure_response($response);
}
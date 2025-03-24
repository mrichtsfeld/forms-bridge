<?php

function forms_bridge_mailchimp_merge_fields(
    $payload,
    $bridge,
    $known_fields = ['email_address', 'status', 'language']
) {
    $payload['merge_fields'] = $payload['merge_fields'] ?? [];

    foreach ($payload as $field => $value) {
        if (!in_array($field, $known_fields)) {
            $payload['merge_fields'][strtoupper($field)] = $value;
            unset($payload[$field]);
        }
    }

    return $payload;
}

return [
    'title' => __('MailChimp merge fields', 'forms-bridge'),
    'description' => __(
        'Formats the submission payload and put all non mailchimp subscription standard fields into an associative array named "merge_fields".',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_mailchimp_merge_fields',
    'input' => ['email_address', 'list_id', 'status'],
    'output' => ['email_address', 'list_id', 'status', 'merge_fields'],
];

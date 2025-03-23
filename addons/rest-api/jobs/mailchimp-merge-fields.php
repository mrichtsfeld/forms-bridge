<?php

function forms_bridge_mailchimp_merge_fields($payload, $bridge)
{
    $with_merge_fields = [
        'status' => 'pending',
        'merge_fields' => (array) ($payload['merge_fields'] ?? []),
    ];

    foreach ($payload as $field => $value) {
        if ($field === 'email_address') {
            $with_merge_fields[$field] = filter_var(
                $value,
                FILTER_SANITIZE_EMAIL
            );
        } elseif ($field === 'list_id' && $value) {
            $with_merge_fields = strval((int) $value);
        } elseif ($field === 'status') {
            $with_merge_fields[$field] = in_array($value, [
                'subscribed',
                'unsubscribed',
                'cleaned',
                'pending',
                'transactional',
            ])
                ? $value
                : 'pending';
        } else {
            $with_merge_fields['merge_fields'][
                strtoupper($field)
            ] = (string) $value;
        }
    }

    $with_merge_fields['language'] =
        $with_merge_fields['language'] ?? get_locale();

    return $with_merge_fields;
}

return [
    'title' => __('MailChimp payload with merge fields', 'forms-bridge'),
    'description' => __(
        'Formats the submission payload and put all non mailchimp subscription standard fields into an associative array named "merge_fields".',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_mailchimp_merge_fields',
    'input' => ['email_address*', 'list_id*', 'status'],
    'output' => [
        'email_address',
        'status',
        'list_id',
        'language',
        'merged_fields',
    ],
];

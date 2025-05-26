<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Audience subscription', 'forms-bridge'),
    'description' => __('Subscribe a new user to an audience', 'forms-bridge'),
    'method' => 'forms_bridge_mailchimp_audience_subscription',
    'input' => [
        [
            'name' => 'list_id',
            'required' => true,
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'email_address',
            'required' => true,
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'status',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'email_type',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'interests',
            'schema' => [
                'type' => 'object',
                'properties' => [],
                'additionalProperties' => true,
            ],
        ],
        [
            'name' => 'language',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'vip',
            'schema' => ['type' => 'boolean'],
        ],
        [
            'name' => 'location',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'latitude' => ['type' => 'number'],
                    'longitude' => ['type' => 'number'],
                ],
                'additionalProperties' => false,
            ],
        ],
        [
            'name' => 'marketing_permissions',
            'schema' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'marketing_permission_id' => ['type' => 'string'],
                        'enabled' => ['type' => 'boolean'],
                    ],
                ],
                'additionalItems' => true,
            ],
        ],
        [
            'name' => 'ip_signup',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'timestamp_signup',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'ip_opt',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'timestamp_opt',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'tags',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'additionalItems' => true,
            ],
        ],
    ],
    'output' => [
        [
            'name' => 'email_address',
            'schema' => ['type' => 'string'],
        ],
    ],
];

function forms_bridge_mailchimp_audience_subscription($payload, $bridge)
{
    $contact = [
        'email_address' => $payload['email_address'],
    ];

    $contact_fields = [
        'status',
        'email_type',
        'interests',
        'language',
        'vip',
        'location',
        'marketing_permissions',
        'ip_signup',
        'ip_opt',
        'timestamp_opt',
        'tags',
        'merge_fields',
    ];

    foreach ($contact_fields as $field) {
        if (isset($payload[$field])) {
            $contact[$field] = $payload[$field];
        }
    }

    $response = $bridge
        ->patch([
            'endpoint' => "/3.0/lists/{$payload['list_id']}/members",
            'method' => 'POST',
            'name' => 'mailchimp-subscribe-member-to-list',
        ])
        ->submit($contact);

    if (is_wp_error($response)) {
        return $response;
    }

    $payload['email_address'] = $response['data']['email_address'];
    return $payload;
}

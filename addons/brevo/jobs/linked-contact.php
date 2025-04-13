<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Linked contact', 'forms-bridge'),
    'description' => __(
        'Creates a new contact and inserts its ID in the linkedContactsIds array field of the payload',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_brevo_linked_contact',
    'input' => [
        [
            'name' => 'email',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'ext_id',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'attributes',
            'schema' => [
                'type' => 'object',
                'properties' => [],
                'additionalProperties' => true,
            ],
        ],
        [
            'name' => 'emailBlacklisted',
            'schema' => ['type' => 'boolean'],
        ],
        [
            'name' => 'smsBlacklisted',
            'schema' => ['type' => 'boolean'],
        ],
        [
            'name' => 'listIds',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'additionalItems' => true,
            ],
        ],
        [
            'name' => 'updateEnabled',
            'schema' => ['type' => 'boolean'],
        ],
        [
            'name' => 'smtpBlacklistSender',
            'schema' => ['type' => 'boolean'],
        ],
    ],
    'output' => [
        [
            'name' => 'linkedContactsIds',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'additionalItems' => true,
            ],
        ],
    ],
];

function forms_bridge_brevo_linked_contact($payload, $bridge)
{
    $contact = forms_bridge_brevo_create_contact($payload, $bridge);

    if (is_wp_error($contact)) {
        return $contact;
    }

    $payload['linkedContactsIds'] = $payload['linkedContactsIds'] ?? [];
    $payload['linkedContactsIds'][] = $contact['id'];

    return $payload;
}

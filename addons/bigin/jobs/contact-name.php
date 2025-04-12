<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_zoho_bigin_contact_name($payload, $bridge)
{
    $contact = forms_bridge_zoho_bigin_create_contact($payload, $bridge);

    if (is_wp_error($contact)) {
        return $contact;
    }

    $payload['Contact_Name'] = [
        'id' => $contact['id'],
    ];

    return $payload;
}

return [
    'title' => __('Bigin contact name', 'forms-bridge'),
    'description' => __(
        'Search for a contact by email or creates a new if it does\'t exists and replace the name by the ID on the payload',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_zoho_bigin_contact_name',
    'input' => [
        [
            'name' => 'Email',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'First_Name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'Last_Name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'Phone',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Title',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Account_Name',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ],
        [
            'name' => 'Description',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'Contact_Name',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ],
        [
            'name' => 'Account_Name',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ],
    ],
];

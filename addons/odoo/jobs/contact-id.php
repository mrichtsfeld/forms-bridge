<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_contact_id_by_email($payload, $bridge)
{
    $query = [['email', '=', $payload['email']], ['is_company', '=', false]];

    if (isset($payload['parent_id'])) {
        $query[] = ['parent_id', '=', $payload['parent_id']];
    }

    $response = $bridge
        ->patch([
            'name' => 'odoo-rpc-search-contact-by-email',
            'template' => null,
            'method' => 'search',
            'model' => 'res.partner',
        ])
        ->submit($query);

    if (is_wp_error($response)) {
        $contact = [
            'is_company' => false,
            'name' => $payload['contact_name'],
            'email' => $payload['email'],
        ];

        $contact_fields = ['phone', 'function', 'parent_id'];

        foreach ($contact_fields as $field) {
            if (isset($payload[$field])) {
                $contact[$field] = $payload[$field];
            }
        }

        $response = $bridge
            ->patch([
                'name' => 'odoo-rpc-create-contact',
                'template' => null,
                'model' => 'res.partner',
            ])
            ->submit($contact);

        if (is_wp_error($response)) {
            return $response;
        }

        $contact_id = $response['data']['result'];
    } else {
        $contact_id = $response['data']['result'][0];
    }

    $payload['partner_id'] = $contact_id;
    return $payload;
}

return [
    'title' => __('Contact ID', 'forms-bridge'),
    'description' => __(
        'Search for a  partner by email or creates a new and sets its ID as the payload partner_id',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_contact_id_by_email',
    'input' => [
        [
            'name' => 'email',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'contact_name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'parent_id',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'phone',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'function',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'partner_id',
            'schema' => ['type' => 'integer'],
        ],
    ],
];

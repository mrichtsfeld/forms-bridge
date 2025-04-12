<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_zoho_bigin_account_name($payload, $bridge)
{
    $account = forms_bridge_zoho_bigin_create_account($payload, $bridge);

    if (is_wp_error($account)) {
        return $account;
    }

    $payload['Account_Name'] = [
        'id' => $account['id'],
    ];

    return $payload;
}

return [
    'title' => __('Bigin account name', 'forms-bridge'),
    'description' => __(
        'Search for an account by name or creates a new if it does\'t exists and replace the name by the ID on the payload',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_zoho_bigin_account_name',
    'input' => [
        [
            'name' => 'Account_Name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'Billing_Street',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Billing_Code',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Billing_City',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Billing_State',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Billing_Country',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Description',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
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

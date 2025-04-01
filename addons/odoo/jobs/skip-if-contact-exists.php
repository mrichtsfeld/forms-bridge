<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_skip_if_contact_exists($payload, $bridge)
{
    $response = $bridge
        ->patch([
            'name' => 'odoo-rpc-search-contact-by-email',
            'template' => null,
            'method' => 'search',
        ])
        ->submit([
            ['email', '=', $payload['email']],
            ['is_company', '=', false],
        ]);

    if (!is_wp_error($response)) {
        return;
    }

    return $payload;
}

return [
    'title' => __('Skip if contact exists', 'forms-bridge'),
    'description' => __(
        'Search contacts by email and skip submission if it exists',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_skip_if_contact_exists',
    'input' => [
        [
            'name' => 'email',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'email',
            'schema' => ['type' => 'string'],
        ],
    ],
];

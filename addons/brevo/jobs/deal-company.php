<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Deal company', 'forms-bridge'),
    'description' => __(
        'Creates a new company and adds its ID to the linkedCompaniesIds deal payload field',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_brevo_deal_company',
    'input' => [
        [
            'name' => 'name',
            'schema' => ['type' => 'string'],
            'required' => true,
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
            'name' => 'countryCode',
            'schema' => ['type' => 'integer'],
        ],
        [
            'name' => 'linkedContactsIds',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'additionalItems' => true,
            ],
        ],
    ],
    'output' => [
        [
            'name' => 'linkedCompaniesIds',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'additionalItems' => true,
            ],
        ],
    ],
];

function forms_bridge_brevo_deal_company($payload, $bridge)
{
    $company = forms_bridge_brevo_create_company($payload, $bridge);

    if (is_wp_error($company)) {
        return $company;
    }

    $payload['linkedCompaniesIds'] = $payload['linkedCompaniesIds'] ?? [];
    $payload['linkedCompaniesIds'][] = $company['id'];

    return $payload;
}

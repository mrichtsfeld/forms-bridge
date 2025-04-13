<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Linked company', 'forms-bridge'),
    'description' => __(
        'Creates a new company and inserts its ID in the linkedCompaniesIds array field of the payload',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_brevo_linked_company',
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

function forms_bridge_brevo_linked_company($payload, $bridge)
{
    $company = forms_bridge_brevo_create_company($payload, $bridge);

    if (is_wp_error($company)) {
        return $company;
    }

    $payload['linkedCompaniesIds'] = $payload['linkedCompaniesIds'] ?? [];
    $payload['linkedCompaniesIds'][] = $company['id'];

    return $payload;
}

<?php

if (!defined('ABSPATH')) {
    exit();
}

global $forms_bridge_country_phone_codes;

return [
    'title' => __('Deals', 'forms-bridge'),
    'description' => __(
        'Creates a company and associate it with a deal',
        'forms-bridge'
    ),
    'fields' => [
        [
            'ref' => '#backend',
            'name' => 'base_url',
            'label' => __('Brevo API URL', 'forms-bridge'),
            'type' => 'string',
            'value' => 'https://api.brevo.com',
        ],
        [
            'ref' => '#backend',
            'name' => 'name',
            'label' => __(
                'Label of the Brevo API backend connection',
                'forms-bridge'
            ),
            'type' => 'string',
            'default' => 'Brevo API',
        ],
        [
            'ref' => '#bridge',
            'name' => 'method',
            'label' => __('HTTP method', 'forms-bridge'),
            'type' => 'string',
            'value' => 'POST',
        ],
        [
            'ref' => '#bridge',
            'name' => 'endpoint',
            'label' => __('Endpoint', 'forms-bridge'),
            'type' => 'string',
            'value' => '/v3/crm/deals',
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'deal_name',
            'label' => __('Deal name', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'deal_owner',
            'label' => __('Owner email', 'forms-bridge'),
            'description' => __(
                'Email of the owner user of the deal',
                'forms-bridge'
            ),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'pipeline',
            'label' => __('Pipeline', 'forms-bridge'),
            'type' => 'string',
        ],
        [
            'ref' => '#backend/headers[]',
            'name' => 'api-key',
            'label' => __('Brevo API Key', 'forms-bridge'),
            'description' => __(
                'You can get it from "SMTP & API" > "API Keys" page from your dashboard',
                'forms-bridge'
            ),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('Deals', 'forms-bridge'),
        ],
    ],
    'form' => [
        'title' => __('Deals', 'forms-bridge'),
        'fields' => [
            [
                'name' => 'email',
                'label' => __('Your email', 'forms-bridge'),
                'type' => 'email',
                'required' => true,
            ],
            [
                'name' => 'fname',
                'label' => __('Your first name', 'forms-bridge'),
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'lname',
                'label' => __('Your last name', 'forms-bridge'),
                'type' => 'text',
            ],
        ],
    ],
    'bridge' => [
        'method' => 'POST',
        'endpoint' => '/v3/crm/deals',
        'custom_fields' => [
            [
                'name' => 'attributes.LANGUAGE',
                'value' => '$locale',
            ],
        ],
        'mutations' => [
            [
                [
                    'from' => 'fname',
                    'to' => 'attributes.FNAME',
                    'cast' => 'string',
                ],
                [
                    'from' => 'lname',
                    'to' => 'attributes.LNAME',
                    'cast' => 'string',
                ],
            ],
            [
                [
                    'from' => 'deal_name',
                    'to' => 'name',
                    'cast' => 'string',
                ],
                [
                    'from' => 'pipeline',
                    'to' => 'attributes.pipeline',
                    'cast' => 'string',
                ],
                [
                    'from' => 'deal_owner',
                    'to' => 'attributes.deal_owner',
                    'cast' => 'string',
                ],
            ],
        ],
        'workflow' => ['brevo-linked-contact'],
    ],
    'backend' => [
        'base_url' => 'https://api.brevo.com',
        'headers' => [
            [
                'name' => 'Accept',
                'value' => 'application/json',
            ],
        ],
    ],
];

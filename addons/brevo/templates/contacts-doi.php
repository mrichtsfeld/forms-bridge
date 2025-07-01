<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Subscription DOI', 'forms-bridge'),
    'description' => __(
        'Subscription form template. The resulting bridge will convert form submissions into new list subscriptions with a double opt-in confirmation check.',
        'forms-bridge'
    ),
    'fields' => [
        [
            'ref' => '#bridge',
            'name' => 'endpoint',
            'value' => '/v3/contacts/doubleOptinConfirmation',
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'includeListIds',
            'label' => __('Segments', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'templateId',
            'label' => __('Double opt-in template', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'redirectionUrl',
            'label' => __('Redirection URL', 'forms-bridge'),
            'type' => 'string',
            'description' => __(
                'URL of the web page that user will be redirected to after clicking on the double opt in URL',
                'forms-bridge'
            ),
            'required' => true,
        ],
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('Subscription DOI', 'forms-bridge'),
        ],
    ],
    'form' => [
        'title' => __('Subscription DOI', 'forms-bridge'),
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
    'backend' => [
        'base_url' => 'https://api.brevo.com',
        'headers' => [
            [
                'name' => 'Accept',
                'value' => 'application/json',
            ],
        ],
    ],
    'bridge' => [
        'method' => 'POST',
        'endpoint' => '/v3/contacts/doubleOptinConfirmation',
        'custom_fields' => [
            [
                'name' => 'attributes.LANGUAGE',
                'value' => '$locale',
            ],
        ],
        'mutations' => [
            [
                [
                    'from' => 'templateId',
                    'to' => 'templateId',
                    'cast' => 'integer',
                ],
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
        ],
    ],
];

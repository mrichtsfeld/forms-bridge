<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Contacts', 'forms-bridge'),
    'description' => __(
        'Contact form template. The resulting bridge will convert form submissions into contacts.',
        'forms-bridge'
    ),
    'fields' => [
        [
            'ref' => '#bridge',
            'name' => 'endpoint',
            'value' => '/api/index.php/contacts',
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'no_email',
            'label' => __('Subscrive to email', 'forms-bridge'),
            'type' => 'boolean',
            'default' => true,
        ],
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('Contacts', 'forms-bridge'),
        ],
    ],
    'form' => [
        'title' => __('Contacts', 'forms-bridge'),
        'fields' => [
            [
                'name' => 'firstname',
                'label' => __('First name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'lastname',
                'label' => __('Last name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'email',
                'label' => __('Email', 'forms-bridge'),
                'type' => 'email',
                'required' => true,
            ],
        ],
    ],
    'bridge' => [
        'endpoint' => '/api/index.php/contacts',
        'custom_fields' => [
            [
                'name' => 'status',
                'value' => '1',
            ],
        ],
        'mutations' => [
            [
                [
                    'from' => 'no_email',
                    'to' => 'no_email',
                    'cast' => 'string',
                ],
            ],
        ],
        'workflow' => ['skip-if-contact-exists'],
    ],
];

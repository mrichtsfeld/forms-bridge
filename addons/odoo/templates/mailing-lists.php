<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Mailing Lists', 'forms-bridge'),
    'fields' => [
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('Mailing Lists', 'forms-bridge'),
        ],
        [
            'ref' => '#form/fields[]',
            'name' => 'list_ids',
            'label' => __('List IDs', 'forms-bridge'),
            'description' => __('List IDs separated by commas', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
    ],
    'bridge' => [
        'model' => 'mailing.contact',
        'workflow' => ['odoo-mailing-list-ids', 'mailing-contact'],
    ],
    'form' => [
        'fields' => [
            [
                'name' => 'list_ids',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'label' => __('First name', 'forms-bridge'),
                'name' => 'first_name',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Last name', 'forms-bridge'),
                'name' => 'last_name',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Email', 'forms-bridge'),
                'name' => 'email',
                'type' => 'email',
                'required' => true,
            ],
        ],
    ],
];

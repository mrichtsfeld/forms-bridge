<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_payload',
    function ($payload, $bridge) {
        if ($bridge->template === 'odoo-mailing-lists') {
            $payload['list_ids'] = array_map(function ($list_id) {
                return (int) $list_id;
            }, explode(',', $payload['list_ids']));

            $payload[
                'name'
            ] = "{$payload['first_name']} {$payload['last_name']}";

            unset($payload['lang']);
        }

        return $payload;
    },
    10,
    2
);

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

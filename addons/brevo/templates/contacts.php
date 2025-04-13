<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_template_data',
    function ($data, $template_name) {
        if ($template_name === 'brevo-contacts') {
            $index = array_search(
                'listIds',
                array_column($data['bridge']['custom_fields'], 'name')
            );

            if ($index !== false) {
                $field = $data['bridge']['custom_fields'][$index];

                $list_ids = array_map('intval', explode(',', $field['value']));

                for ($i = 0; $i < count($list_ids); $i++) {
                    $data['bridge']['custom_fields'][] = [
                        'name' => "listIds[{$i}]",
                        'value' => $list_ids[$i],
                    ];

                    $data['bridge']['mutations'][0][] = [
                        'from' => "listIds[{$i}]",
                        'to' => "listIds[{$i}]",
                        'cast' => 'integer',
                    ];
                }

                array_splice($data['bridge']['custom_fields'], $index, 1);
                $data['bridge']['custom_fields'] = array_values(
                    $data['bridge']['custom_fields']
                );
            }
        }

        return $data;
    },
    10,
    2
);

return [
    'title' => __('Contacts', 'forms-bridge'),
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
            'value' => '/v3/contacts',
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
            'default' => __('Contacts', 'forms-bridge'),
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'listIds',
            'label' => __('Segment IDs', 'forms-bridge'),
            'type' => 'string',
            'description' => __(
                'List IDs separated by commas. Leave it empty if you don\'t want to subscrive contact to any list',
                'forms-bridge'
            ),
        ],
    ],
    'form' => [
        'title' => __('Contacts', 'forms-bridge'),
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
        'base_url' => 'https://api.brevo.com/v3/',
        'headers' => [
            [
                'name' => 'Accept',
                'value' => 'application/json',
            ],
        ],
    ],
    'bridge' => [
        'method' => 'POST',
        'endpoint' => '/v3/contacts',
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
        ],
    ],
];

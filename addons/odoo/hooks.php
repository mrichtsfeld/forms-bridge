<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_template_schema',
    function ($schema, $api) {
        if ($api !== 'odoo') {
            return $schema;
        }

        $schema['properties']['bridge']['properties']['endpoint'] = [
            'type' => 'string',
        ];

        $schema['properties']['bridge']['required'][] = 'endpoint';

        $schema['properties']['credential'] = [
            'type' => 'object',
            'description' => __(
                'Database credentials to perform RPC authentications',
                'forms-bridge'
            ),
            'properties' => [
                'name' => ['type' => 'string'],
                'database' => ['type' => 'string'],
                'user' => ['type' => 'string'],
                'password' => ['type' => 'string'],
            ],
            'required' => ['name', 'user', 'database', 'password'],
        ];

        return $schema;
    },
    10,
    2
);

add_filter(
    'forms_bridge_template_defaults',
    function ($defaults, $api, $schema) {
        if ($api !== 'odoo') {
            return $defaults;
        }

        return wpct_plugin_merge_object(
            [
                'fields' => [
                    [
                        'ref' => '#credential',
                        'name' => 'name',
                        'label' => __('Name', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'database',
                        'label' => __('Database', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'user',
                        'label' => __('User', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'password',
                        'label' => __('Password', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#backend',
                        'name' => 'name',
                        'default' => 'Odoo',
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'endpoint',
                        'label' => __('Model', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
                'bridge' => [
                    'name' => '',
                    'form_id' => '',
                    'backend' => '',
                    'credential' => '',
                    'endpoint' => '',
                ],
                'backend' => [
                    'name' => 'Odoo',
                    'headers' => [
                        [
                            'name' => 'Accept',
                            'value' => 'application/json',
                        ],
                    ],
                ],
                'credential' => [
                    'name' => '',
                    'database' => '',
                    'user' => '',
                    'password' => '',
                ],
            ],
            $defaults,
            $schema
        );
    },
    10,
    3
);

add_filter(
    'forms_bridge_template_data',
    function ($data, $template_id) {
        if (strpos($template_id, 'odoo-') !== 0) {
            return $data;
        }

        $custom_field_names = array_column(
            $data['bridge']['custom_fields'],
            'name'
        );

        $index = array_search('tag_ids', $custom_field_names);
        if ($index !== false) {
            $field = $data['bridge']['custom_fields'][$index];
            $tags = $field['value'] ?? [];

            for ($i = 0; $i < count($tags); $i++) {
                $data['bridge']['custom_fields'][] = [
                    'name' => "tag_ids[{$i}]",
                    'value' => $tags[$i],
                ];

                $data['bridge']['mutations'][0][] = [
                    'from' => "tag_ids[{$i}]",
                    'to' => "tag_ids[{$i}]",
                    'cast' => 'integer',
                ];
            }

            array_splice($data['bridge']['custom_fields'], $index, 1);
        }

        $index = array_search('list_ids', $custom_field_names);
        if ($index !== false) {
            $field = $data['bridge']['custom_fields'][$index];

            for ($i = 0; $i < count($field['value']); $i++) {
                $data['bridge']['custom_fields'][] = [
                    'name' => "list_ids[{$i}]",
                    'value' => $field['value'][$i],
                ];

                $data['bridge']['mutations'][0][] = [
                    'from' => "list_ids[{$i}]",
                    'to' => "list_ids[{$i}]",
                    'cast' => 'integer',
                ];
            }

            array_splice($data['bridge']['custom_fields'], $index, 1);
        }

        return $data;
    },
    10,
    2
);

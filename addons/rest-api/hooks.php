<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_template_schema',
    function ($schema, $api) {
        if ($api !== 'rest-api') {
            return $schema;
        }

        $schema['properties']['bridge']['properties'] = array_merge(
            $schema['properties']['bridge']['properties'],
            [
                'endpoint' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => '/',
                ],
                'method' => [
                    'type' => 'string',
                    'enum' => \FORMS_BRIDGE\Rest_Form_Bridge::allowed_methods,
                    'default' => 'POST',
                ],
            ]
        );

        $schema['properties']['bridge']['required'][] = 'endpoint';
        $schema['properties']['bridge']['required'][] = 'method';

        return $schema;
    },
    10,
    2
);

add_filter(
    'forms_bridge_template_defaults',
    function ($defaults, $api, $schema) {
        if ($api !== 'rest-api') {
            return $defaults;
        }

        return wpct_plugin_merge_object(
            [
                'fields' => [
                    [
                        'ref' => '#bridge',
                        'name' => 'endpoint',
                        'label' => __('Endpoint', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'method',
                        'label' => __('Method', 'forms-bridge'),
                        'type' => 'options',
                        'options' => array_map(function ($method) {
                            return ['value' => $method, 'label' => $method];
                        }, \FORMS_BRIDGE\Rest_Form_Bridge::allowed_methods),
                        'required' => true,
                        'default' => 'POST',
                    ],
                ],
                'bridge' => [
                    'endpoint' => '',
                    'method' => 'POST',
                ],
            ],
            $defaults,
            $schema
        );
    },
    10,
    3
);

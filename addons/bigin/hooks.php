<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_template_schema',
    function ($schema, $addon) {
        if ($addon !== 'bigin') {
            return $schema;
        }

        return apply_filters('forms_bridge_template_schema', $schema, 'zoho');
    },
    10,
    2
);

add_filter(
    'forms_bridge_template_defaults',
    function ($defaults, $schema, $api) {
        if ($api !== 'bigin') {
            return $defaults;
        }

        $defaults = apply_filters(
            'forms_bridge_template_defaults',
            $defaults,
            $schema,
            'zoho'
        );

        return wpct_plugin_merge_object(
            [
                'fields' => [
                    [
                        'ref' => '#bridge',
                        'name' => 'scope',
                        'value' => 'ZohoBigin.modules.ALL',
                    ],
                ],
                'bridge' => [
                    'backend' => 'Zoho API',
                    'scope' => 'ZohoBigin.modules.ALL',
                ],
            ],
            $defaults,
            $schema
        );
    },
    20,
    3
);

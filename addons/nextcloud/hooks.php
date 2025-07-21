<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_bridge_schema',
    function ($schema, $addon) {
        if ($addon !== 'nextcloud') {
            return $schema;
        }

        $schema['properties']['endpoint']['name'] = __(
            'Filepath',
            'forms-bridge'
        );
        $schema['properties']['endpoint']['description'] = __(
            'Path to the CSV file from the root of your nextcloud file system directory',
            'forms-bridge'
        );
        $schema['properties']['endpoint']['pattern'] = '.+\.csv$';

        $schema['properties']['method']['enum'] = ['PUT'];
        $schema['properties']['method']['default'] = 'PUT';

        $schema['required'][] = 'credential';

        return $schema;
    },
    10,
    2
);

add_filter(
    'forms_bridge_template_defaults',
    function ($defaults, $addon, $schema) {
        if ($addon !== 'nextcloud') {
            return $defaults;
        }

        return wpct_plugin_merge_object(
            [
                'fields' => [
                    [
                        'ref' => '#backend',
                        'name' => 'name',
                        'default' => 'Nextcloud',
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'endpoint',
                        'label' => __('Filepath', 'forms-bridge'),
                        'type' => 'text',
                        'required' => true,
                        'pattern' => '.+.csv$',
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'method',
                        'label' => __('Method', 'forms-bridge'),
                        'type' => 'text',
                        'value' => 'PUT',
                        'required' => true,
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'endpoint',
                        'label' => __('Filepath', 'forms-bridge'),
                        'pattern' => '.+\.csv$',
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'name',
                        'label' => __('Name', 'forms-bridge'),
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'schema',
                        'label' => __('Authentication', 'forms-bridge'),
                        'type' => 'text',
                        'value' => 'Basic',
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'client_id',
                        'label' => __('User login', 'forms-bridge'),
                        'description' => __(
                            'Either, a user name or email',
                            'forms-bridge'
                        ),
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'ref' => '#credential',
                        'name' => 'client_secret',
                        'label' => __('Password', 'forms-bridge'),
                        'type' => 'text',
                        'required' => true,
                    ],
                ],
                'bridge' => [
                    'backend' => 'Nextcloud',
                ],
                'backend' => [
                    'name' => 'Nextcloud',
                    'headers' => [
                        [
                            'name' => 'Content-Type',
                            'value' => 'application/octet-stream',
                        ],
                    ],
                ],
                'credential' => [
                    'schema' => 'Basic',
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
        if (strpos($template_id, 'nextcloud-') !== 0) {
            return $data;
        }

        if (!preg_match('/\.csv$/i', $data['bridge']['endpoint'])) {
            $data['bridge']['endpoint'] .= '.csv';
        }

        return $data;
    },
    10,
    2
);

add_filter(
    'forms_bridge_credential_schema',
    function ($schema, $addon) {
        if ($addon == 'nextcloud') {
            unset($schema['properties']['realm']);

            $schema['properties']['schema']['value'] = 'Basic';
            $schema['properties']['client_id']['name'] = __(
                'User login',
                'forms-bridge'
            );
            $schema['properties']['client_secret']['name'] = __(
                'Password',
                'forms-bridge'
            );
        }

        return $schema;
    },
    10,
    2
);

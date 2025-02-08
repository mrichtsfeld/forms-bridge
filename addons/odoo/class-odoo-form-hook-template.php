<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Odoo_Form_Hook_Template extends Form_Hook_Template
{
    /**
     * Handles the template default values.
     *
     * @var array
     */
    protected static $default = [
        'fields' => [
            [
                'ref' => '#database',
                'name' => 'name',
                'label' => 'Name',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#database',
                'name' => 'backend',
                'label' => 'Backend',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#database',
                'name' => 'user',
                'label' => 'User',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#database',
                'name' => 'password',
                'label' => 'Password',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#hook',
                'name' => 'database',
                'label' => 'Database',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#hook',
                'name' => 'model',
                'label' => 'Model',
                'type' => 'string',
                'required' => true,
            ],
        ],
        'hook' => [
            'name' => '',
            'backend' => 'Odoo',
            'form_id' => '',
            'database' => '',
            'model' => '',
        ],
        'backend' => [
            'name' => 'Odoo',
        ],
        'database' => [
            'name' => '',
            'backend' => 'Odoo',
            'user' => '',
            'password' => '',
        ],
    ];

    /**
     * Store template attribute values, validates config data and binds the
     * instance to custom forms bridge template hooks.
     *
     * @param string $file Source file path of the template config.
     * @param array $config Template config data.
     */
    public function __construct($file, $config)
    {
        $this->api = 'odoo';

        add_filter(
            'forms_bridge_template_schema',
            function ($schema, $template_name) {
                if ($template_name === $this->name) {
                    $schema = $this->extend_schema($schema);
                }

                return $schema;
            },
            10,
            2
        );

        add_filter(
            'forms_bridge_template_data',
            function ($data, $name) {
                if ($name === $this->name) {
                    $result = $this->create_database($data['database']);

                    if (!$result) {
                        throw new Form_Hook_Template_Exception(
                            'database_creation_error',
                            __(
                                'Forms bridge can\'t create the database',
                                'forms-bridge'
                            )
                        );
                    }
                }

                return $data;
            },
            10,
            2
        );

        parent::__construct($file, $config);
    }

    /**
     * Extends the common schema and adds custom properties.
     *
     * @param array $schema Common template data schema.
     *
     * @return array
     */
    private function extend_schema($schema)
    {
        $schema['database'] = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'user' => ['type' => 'string'],
                'password' => ['type' => 'string'],
                'backend' => ['type' => 'string'],
            ],
            'required' => ['name', 'user', 'password', 'backend'],
            'additionalProperties' => false,
        ];

        $schema['hook']['properties'] = array_merge(
            $schema['hook']['properties'],
            [
                'database' => ['type' => 'string'],
                'model' => ['type' => 'string'],
            ]
        );

        $schema['hook']['required'][] = 'database';
        $schema['hook']['required'][] = 'model';

        return $schema;
    }

    private function create_database($data)
    {
        $setting = Forms_Bridge::setting($this->api);
        $setting_data = $setting->data();

        $name_conflict = array_search(
            $data['name'],
            array_column($setting_data['databases'], 'name')
        );

        if ($name_conflict) {
            return;
        }

        $setting_data['databases'][] = $data;
        $setting_data = apply_filters(
            'wpct_validate_setting',
            $setting_data,
            $setting
        );

        $is_valid =
            array_search(
                $data['name'],
                array_column($setting_data['databases'], 'name')
            ) !== false;

        if (!$is_valid) {
            return;
        }

        do_action('forms_bridge_before_template_database', $data, $this->name);

        $setting->databases = $setting_data['databases'];

        do_action('forms_bridge_template_database', $data, $this->name);

        return true;
    }
}

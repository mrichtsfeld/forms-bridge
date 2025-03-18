<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Dolibarr_Form_Bridge_Template extends Form_Bridge_Template
{
    private $api_key_data = null;

    /**
     * Handles the template default values.
     *
     * @var array
     */
    protected static $default = [
        'fields' => [
            [
                'ref' => '#api_key',
                'name' => 'name',
                'label' => 'Key name',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#api_key',
                'name' => 'key',
                'label' => 'Key value',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#bridge',
                'name' => 'endpoint',
                'label' => 'Endpoint',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#backend',
                'name' => 'name',
                'label' => 'Name',
                'type' => 'string',
                'required' => true,
                'default' => 'Dolibarr',
            ],
            [
                'ref' => '#backend',
                'name' => 'base_url',
                'label' => 'Base URL',
                'type' => 'string',
                'required' => true,
            ],
        ],
        'bridge' => [
            'backend' => '',
            'form_id' => '',
            'endpoint' => '',
            'api_key' => '',
        ],
        'backend' => [
            'name' => 'Dolibarr',
            'headers' => [
                [
                    'name' => 'Content-Type',
                    'value' => 'application/json',
                ],
                [
                    'name' => 'Accept',
                    'value' => 'application/json',
                ],
            ],
        ],
        'api_key' => [
            'name' => '',
            'backend' => 'Dolibarr',
            'key' => '',
        ],
    ];

    /**
     * Sets the template api, extends the common schema and inherits the parent's
     * constructor.
     *
     * @param string $file Source file path of the template config.
     * @param array $config Template config data.
     */
    public function __construct($file, $config, $api)
    {
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

        parent::__construct($file, $config, $api);

        add_filter(
            'forms_bridge_template_data',
            function ($data, $template_name) {
                if ($template_name === $this->name) {
                    $this->api_key_data = array_merge($data['api_key'], [
                        'backend' => $data['backend']['name'],
                    ]);

                    $data['bridge']['api_key'] = $data['api_key']['name'];
                }

                return $data;
            },
            10,
            2
        );

        add_action(
            'forms_bridge_before_template_bridge',
            function ($data, $template_name) {
                if ($template_name === $this->name && $this->api_key_data) {
                    $api_key_exists = $this->api_key_exists(
                        $this->api_key_data['name']
                    );

                    if ($api_key_exists) {
                        return;
                    }

                    $result = $this->create_api_key($this->api_key_data);

                    if (!$result) {
                        throw new Form_Bridge_Template_Exception(
                            'api_key_creation_error',
                            __(
                                'Forms Bridge can\'t create the api key',
                                'forms-bridge'
                            )
                        );
                    }
                }
            },
            10,
            2
        );
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
        $schema['api_key'] = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'backend' => ['type' => 'string'],
                'key' => ['type' => 'string'],
            ],
            'required' => ['name', 'backend', 'key'],
            'additionalProperties' => false,
        ];

        $schema['bridge']['properties'] = array_merge(
            $schema['bridge']['properties'],
            [
                'api_key' => ['type' => 'string'],
                'endpoint' => ['type' => 'string'],
            ]
        );

        $schema['bridge']['required'][] = 'api_key';
        $schema['bridge']['required'][] = 'endpoint';

        return $schema;
    }

    /**
     * Checks if an API key with the given name exists on the settings store.
     *
     * @param string $name API key name.
     *
     * @return boolean
     */
    private function api_key_exists($name)
    {
        $setting = Forms_Bridge::setting($this->api);
        $api_keys = $setting->api_keys ?: [];

        return array_search($name, array_column($api_keys, 'name')) !== false;
    }

    /**
     * Stores the API key data on the settings store.
     *
     * @param array $data API key data.
     *
     * @return boolean Creation result.
     */
    private function create_api_key($data)
    {
        $setting = Forms_Bridge::setting($this->api);
        $api_keys = $setting->api_keys;

        do_action('forms_bridge_before_template_api_key', $data, $this->name);

        $setting->api_keys = array_merge($api_keys, [$data]);
        $setting->flush();

        $is_valid = $this->api_key_exists($data['name']);

        if (!$is_valid) {
            return false;
        }

        do_action('forms_bridge_template_api_key', $data, $this->name);

        return true;
    }
}

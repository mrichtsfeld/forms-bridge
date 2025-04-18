<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Rest_Form_Bridge_Template extends Form_Bridge_Template
{
    /**
     * Template default config getter.
     *
     * @return array
     */
    protected static function defaults()
    {
        return [
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
                    'options' => [
                        [
                            'value' => 'GET',
                            'label' => 'GET',
                        ],
                        [
                            'value' => 'POST',
                            'label' => 'POST',
                        ],
                        [
                            'value' => 'PUT',
                            'label' => 'PUT',
                        ],
                        [
                            'value' => 'DELETE',
                            'label' => 'DELETE',
                        ],
                    ],
                    'required' => true,
                    'default' => 'POST',
                ],
            ],
            'bridge' => [
                'endpoint' => '',
                'method' => 'POST',
            ],
        ];
    }

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
                    if (!empty($data['backend']['name'])) {
                        $data['bridge']['backend'] = $data['backend']['name'];
                    }
                }

                return $data;
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
    protected function extend_schema($schema)
    {
        $schema['bridge']['properties'] = array_merge(
            $schema['bridge']['properties'],
            [
                'backend' => ['type' => 'string'],
                'endpoint' => ['type' => 'string'],
                'method' => [
                    'type' => 'string',
                    'enum' => ['GET', 'POST', 'PUT', 'DELETE'],
                ],
            ]
        );

        $schema['bridge']['required'][] = 'backend';
        $schema['bridge']['required'][] = 'endpoint';
        $schema['bridge']['required'][] = 'method';

        return $schema;
    }
}

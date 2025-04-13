<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Mailchimp_Form_Bridge_Template extends Rest_Form_Bridge_Template
{
    /**
     * Handles the template default values.
     *
     * @var array
     */
    protected static $default = [
        'fields' => [
            [
                'ref' => '#bridge',
                'name' => 'endpoint',
                'label' => 'Endpoint',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#bridge',
                'name' => 'method',
                'label' => 'Method',
                'type' => 'string',
                'required' => true,
                'value' => 'POST',
            ],
            [
                'ref' => '#backend',
                'name' => 'name',
                'label' => 'Label of the MailChimp API backend connection',
                'type' => 'string',
                'required' => true,
                'default' => 'MailChimp API',
            ],
            [
                'ref' => '#backend',
                'name' => 'base_url',
                'label' => 'Base URL',
                'type' => 'string',
                'value' => 'https://{dc}.api.mailchimp.com',
            ],
            [
                'ref' => '#backend/headers[]',
                'name' => 'datacenter',
                'label' => 'Datacenter',
                'description' =>
                    'First part of the URL of your mailchimp account or last part of your API key',
                'required' => true,
                'type' => 'options',
                'options' => [
                    [
                        'label' => 'us1',
                        'value' => 'us1',
                    ],
                    [
                        'label' => 'us2',
                        'value' => 'us2',
                    ],
                    [
                        'label' => 'us3',
                        'value' => 'us3',
                    ],
                    [
                        'label' => 'us4',
                        'value' => 'us4',
                    ],
                    [
                        'label' => 'us5',
                        'value' => 'us5',
                    ],
                    [
                        'label' => 'us6',
                        'value' => 'us6',
                    ],
                    [
                        'label' => 'us7',
                        'value' => 'us7',
                    ],
                    [
                        'label' => 'us8',
                        'value' => 'us8',
                    ],
                    [
                        'label' => 'us9',
                        'value' => 'us9',
                    ],
                    [
                        'label' => 'us10',
                        'value' => 'us10',
                    ],
                    [
                        'label' => 'us11',
                        'value' => 'us11',
                    ],
                    [
                        'label' => 'us12',
                        'value' => 'us12',
                    ],
                    [
                        'label' => 'us13',
                        'value' => 'us13',
                    ],
                ],
            ],
            [
                'ref' => '#backend/headers[]',
                'name' => 'api-key',
                'label' => 'API key',
                'description' =>
                    'Get it from your <a href="https://us1.admin.mailchimp.com/account/api/" target="_blank">account</a>',
                'type' => 'string',
                'required' => true,
            ],
        ],
        'bridge' => [
            'backend' => '',
            'endpoint' => '',
            'method' => 'POST',
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

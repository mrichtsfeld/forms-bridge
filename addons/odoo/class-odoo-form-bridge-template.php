<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Odoo_Form_Bridge_Template extends Form_Bridge_Template
{
    /**
     * Handles the template api name.
     *
     * @var string
     */
    protected $api = 'odoo';

    /**
     * Template default config getter.
     *
     * @return array
     */
    protected static function defaults()
    {
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
            parent::defaults(),
            self::schema()
        );
    }

    /**
     * Extends the common schema and adds custom properties.
     *
     * @param array $schema Common template data schema.
     *
     * @return array
     */
    public static function schema()
    {
        $schema = parent::schema();

        $schema['properties']['bridge']['properties']['endpoint'] = [
            'type' => 'string',
        ];
        $schema['properties']['bridge']['required'][] = 'endpoint';

        $schema['properties']['credential']['properties']['user'] = [
            'type' => 'string',
        ];
        $schema['properties']['credential']['required'][] = 'user';

        $schema['properties']['credential']['properties']['database'] = [
            'type' => 'string',
        ];
        $schema['properties']['credential']['required'][] = 'database';

        $schema['properties']['credential']['properties']['password'] = [
            'type' => 'string',
        ];
        $schema['properties']['credential']['required'][] = 'password';

        return $schema;
    }

    public function use($fields, $integration)
    {
        add_filter(
            'forms_bridge_template_data',
            function ($data, $template_id) {
                if ($template_id !== $this->id) {
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
    }
}

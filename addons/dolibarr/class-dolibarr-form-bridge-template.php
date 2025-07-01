<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Dolibarr_Form_Bridge_Template extends Rest_Form_Bridge_Template
{
    /**
     * Handles the template api name.
     *
     * @var string
     */
    protected $api = 'dolibarr';

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
                        'ref' => '#backend',
                        'name' => 'name',
                        'default' => 'Dolibarr',
                    ],
                    [
                        'ref' => '#backend/headers[]',
                        'name' => 'DOLAPIKEY',
                        'label' => __('API key', 'forms-bridge'),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'method',
                        'value' => 'POST',
                    ],
                ],
                'bridge' => [
                    'endpoint' => '',
                    'method' => 'POST',
                ],
                'backend' => [
                    'name' => 'Dolibarr',
                    'headers' => [
                        [
                            'name' => 'Accept',
                            'value' => 'application/json',
                        ],
                    ],
                ],
            ],
            parent::defaults(),
            self::schema()
        );
    }

    public function use($fields, $integration)
    {
        add_filter(
            'forms_bridge_template_data',
            function ($data, $template_id) {
                if ($template_id !== $this->id) {
                    return $data;
                }

                $index = array_search(
                    'no_email',
                    array_column($data['bridge']['custom_fields'], 'name')
                );

                if ($index !== false) {
                    $field = &$data['bridge']['custom_fields'][$index];
                    $field['value'] = $field['value'] ? '0' : '1';
                }

                return $data;
            },
            10,
            2
        );
    }
}

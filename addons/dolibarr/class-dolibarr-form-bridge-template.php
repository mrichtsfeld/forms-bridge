<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Dolibarr_Form_Bridge_Template extends Rest_Form_Bridge_Template
{
    /**
     * Template default config getter.
     *
     * @return array
     */
    protected static function defaults()
    {
        return forms_bridge_merge_object(
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
            self::$schema
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
        $schema = parent::extend_schema($schema);
        $schema['bridge']['properties']['method']['enum'] = ['POST'];
        return $schema;
    }
}

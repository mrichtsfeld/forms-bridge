<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Brevo_Form_Bridge_Template extends Rest_Form_Bridge_Template
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
                'default' => 'POST',
            ],
            [
                'ref' => '#backend',
                'name' => 'name',
                'label' => 'Name',
                'type' => 'string',
                'required' => true,
                'default' => 'Brevo API',
            ],
            [
                'ref' => '#backend',
                'name' => 'base_url',
                'label' => 'Base URL',
                'type' => 'string',
                'value' => 'https://api.brevo.com',
            ],
            [
                'ref' => '#backend/headers[]',
                'name' => 'api-key',
                'label' => 'API Key',
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
}

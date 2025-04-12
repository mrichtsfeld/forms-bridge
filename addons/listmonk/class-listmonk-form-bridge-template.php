<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Listmonk_Form_Bridge_Template extends Rest_Form_Bridge_Template
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
            ],
            [
                'ref' => '#backend',
                'name' => 'base_url',
                'label' => 'Base URL',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#backend/headers[]',
                'name' => 'api_user',
                'label' => 'API user',
                'description' =>
                    'You have to generate an API user on your listmonk instance. See the <a href="https://listmonk.app/docs/roles-and-permissions/#api-users">documentation</a> for more information',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#backend/headers[]',
                'name' => 'token',
                'label' => 'API user token',
                'description' =>
                    'Token of the API user. The token will be shown only once on user creation time, be sure to copy its value and store it in a save place',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#bridge/custom_fields[]',
                'name' => 'lists',
                'label' => 'Mailing lists',
                'description' =>
                    'Select, at least, one list that users will subscribe to',
                'type' => 'string',
                'required' => true,
            ],
        ],
        'bridge' => [
            'backend' => 'Listmonk',
            'endpoint' => '',
            'method' => 'POST',
        ],
        'backend' => [
            'name' => 'Listmonk',
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
    ];
}

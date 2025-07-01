<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Listmonk_Form_Bridge_Template extends Rest_Form_Bridge_Template
{
    /**
     * Handles the template api name.
     *
     * @var string
     */
    protected $api = 'listmonk';

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
                        'description' => __(
                            'Label of the Listmonk API backend connection',
                            'forms-bridge'
                        ),
                        'default' => 'Listmonk API',
                    ],
                    [
                        'ref' => '#backend/headers[]',
                        'name' => 'user',
                        'label' => __('API user', 'forms-bridge'),
                        'description' => __(
                            'You have to generate an API user on your listmonk instance. See the <a href="https://listmonk.app/docs/roles-and-permissions/#api-users">documentation</a> for more information',
                            'forms-bridge'
                        ),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#backend/headers[]',
                        'name' => 'token',
                        'label' => __('API token', 'forms-bridge'),
                        'description' => __(
                            'Token of the API user. The token will be shown only once on user creation time, be sure to copy its value and store it in a save place',
                            'forms-bridge'
                        ),
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'ref' => '#bridge',
                        'name' => 'method',
                        'default' => 'POST',
                    ],
                    [
                        'ref' => '#bridge/custom_fields[]',
                        'name' => 'lists',
                        'label' => __('Mailing lists', 'forms-bridge'),
                        'description' => __(
                            'Select, at least, one list that users will subscribe to',
                            'forms-bridge'
                        ),
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
                'bridge' => [
                    'backend' => 'Listmonk API',
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
                if ($template_id === $this->id) {
                    $index = array_search(
                        'lists',
                        array_column($data['bridge']['custom_fields'], 'name')
                    );

                    if ($index !== false) {
                        $field = &$data['bridge']['custom_fields'][$index];
                        if (is_array($field['value'])) {
                            for ($i = 0; $i < count($field['value']); $i++) {
                                $data['bridge']['custom_fields'][] = [
                                    'name' => "lists[{$i}]",
                                    'value' => (int) $field['value'][$i],
                                ];

                                $data['bridge']['mutations'][0][] = [
                                    'from' => "lists[{$i}]",
                                    'to' => "lists[{$i}]",
                                    'cast' => 'integer',
                                ];
                            }

                            array_splice(
                                $data['bridge']['custom_fields'],
                                $index,
                                1
                            );
                            $data['bridge']['custom_fields'] = array_values(
                                $data['bridge']['custom_fields']
                            );
                        }
                    }

                    $header_names = array_column(
                        $data['backend']['headers'],
                        'name'
                    );
                    $user_index = array_search('user', $header_names);
                    $token_index = array_search('token', $header_names);

                    if ($user_index !== false && $token_index !== false) {
                        $user =
                            $data['backend']['headers'][$user_index]['value'];
                        $token =
                            $data['backend']['headers'][$token_index]['value'];

                        $headers = [];
                        foreach ($data['backend']['headers'] as $header) {
                            if (
                                !in_array(
                                    $header['name'],
                                    ['user', 'token'],
                                    true
                                )
                            ) {
                                $headers[] = $header;
                            }
                        }

                        $headers[] = [
                            'name' => 'Authorization',
                            'value' => "token {$user}:{$token}",
                        ];

                        $data['backend']['headers'] = $headers;
                    }
                }

                return $data;
            },
            10,
            2
        );

        return parent::use($fields, $integration);
    }
}

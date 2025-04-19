<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the Mailchimp API.
 */
class Mailchimp_Form_Bridge extends Rest_Form_Bridge
{
    protected $api = 'mailchimp';

    /**
     * Gets bridge's default body encoding schema.
     *
     * @return string|null
     */
    protected function content_type()
    {
        return 'application/json';
    }

    /**
     * Performs an http request to backend's REST API.
     *
     * @param array $payload Payload data.
     * @param array $attachments Submission's attached files.
     *
     * @return array|WP_Error Http request response.
     */
    protected function do_submit($payload, $attachments = [])
    {
        add_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Mailchimp_Form_Bridge::basic_auth',
            10,
            1
        );

        $response = parent::do_submit($payload, $attachments);

        if (is_wp_error($response)) {
            $a = 1;
        }

        return $response;
    }

    protected function api_fields()
    {
        if (strstr($this->endpoint, '/lists/') !== false) {
            $fields = [
                [
                    'name' => 'email_address',
                    'schema' => ['type' => 'string'],
                    'required' => true,
                ],
                [
                    'name' => 'status',
                    'schema' => ['type' => 'string'],
                    'required' => true,
                ],
                [
                    'name' => 'email_type',
                    'schema' => ['type' => 'string'],
                ],
                [
                    'name' => 'interests',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
                [
                    'name' => 'language',
                    'schema' => ['type' => 'string'],
                ],
                [
                    'name' => 'vip',
                    'schema' => ['type' => 'boolean'],
                ],
                [
                    'name' => 'location',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'latitude' => ['type' => 'number'],
                            'longitude' => ['type' => 'number'],
                        ],
                    ],
                ],
                [
                    'name' => 'marketing_permissions',
                    'schema' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'marketing_permission_id' => [
                                    'type' => 'string',
                                ],
                                'enabled' => ['type' => 'boolean'],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'ip_signup',
                    'schema' => ['type' => 'string'],
                ],
                [
                    'name' => 'ip_opt',
                    'schema' => ['type' => 'string'],
                ],
                [
                    'name' => 'timestamp_opt',
                    'schema' => ['type' => 'string'],
                ],
                [
                    'name' => 'tags',
                    'schema' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
                [
                    'name' => 'merge_fields',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ];

            $fields_endpoint = str_replace(
                '/members',
                '/merge-fields',
                $this->endpoint
            );

            $response = $this->patch([
                'name' => 'mailchimp-get-merge-fields',
                'endpoint' => $fields_endpoint,
                'method' => 'GET',
            ])->submit([]);

            if (is_wp_error($response)) {
                return [];
            }

            foreach ($response['data']['merge_fields'] as $field) {
                $fields[] = [
                    'name' => 'merge_fields.' . $field['tag'],
                    'schema' => ['type' => 'string'],
                ];
            }

            return $fields;
        }

        return [];
    }

    public static function basic_auth($args)
    {
        if (isset($args['headers']['Api-Key'])) {
            $basic_auth =
                'Basic ' .
                base64_encode('forms-bridge:' . $args['headers']['Api-Key']);
            unset($args['headers']['Api-Key']);
            $args['headers']['Authorization'] = $basic_auth;
        }

        remove_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Mailchimp_Form_Bridge::basic_auth',
            10,
            1
        );

        return $args;
    }
}

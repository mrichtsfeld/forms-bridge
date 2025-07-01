<?php

namespace FORMS_BRIDGE;

use TypeError;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the Mailchimp API.
 */
class Mailchimp_Form_Bridge extends Rest_Form_Bridge
{
    /**
     * Handles bridge class API name.
     *
     * @var string
     */
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
        $response = parent::do_submit($payload, $attachments);

        if (is_wp_error($response)) {
            $error_response = $response->get_error_data()['response'] ?? null;

            $code = $error_response['response']['code'] ?? null;
            if ($code !== 400) {
                return $response;
            }

            try {
                $body = json_decode($error_response['body'] ?? '', true);
                $title = $body['title'] ?? null;
            } catch (TypeError) {
                return $response;
            }

            if ($title === 'Member Exists') {
                if (
                    !preg_match(
                        '/(?<=lists\/).+(?=\/members)/',
                        $this->endpoint,
                        $matches
                    )
                ) {
                    return $response;
                }

                $list_id = $matches[0];

                $search_response = $this->patch([
                    'name' => 'mailchimp-search-member',
                    'method' => 'GET',
                    'endpoint' => '/3.0/search-members',
                ])->submit([
                    'fiels' => 'exact_matches.members.id',
                    'list_id' => $list_id,
                    'query' => $payload['email_address'],
                ]);

                if (is_wp_error($search_response)) {
                    return $response;
                }

                $member_id =
                    $search_response['data']['exact_matches']['members'][0][
                        'id'
                    ] ?? null;

                if (!$member_id) {
                    return $response;
                }

                $update_endpoint = "/3.0/lists/{$list_id}/members/{$member_id}";
                if (
                    strstr($this->endpoint, 'skip_merge_validation') !== false
                ) {
                    $update_endpoint .= '?skip_merge_validation=true';
                }

                $update_response = $this->patch([
                    'name' => 'mailchimp-update-subscription',
                    'method' => 'PUT',
                    'endpoint' => $update_endpoint,
                ])->submit($payload);

                if (is_wp_error($update_response)) {
                    return $response;
                }

                return $update_response;
            }
        }

        return $response;
    }

    protected function endpoint_schema()
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
            ])->submit();

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
}

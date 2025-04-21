<?php

namespace FORMS_BRIDGE;

use WP_Error;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the FinanCoop REST API.
 */
class Finan_Coop_Form_Bridge extends Rest_Form_Bridge
{
    /**
     * Handles bridge class API name.
     *
     * @var string
     */
    protected $api = 'financoop';

    /**
     * Handles the array of accepted HTTP header names of the bridge API.
     *
     * @var array<string>
     */
    protected static $api_headers = [
        'Accept',
        'Content-Type',
        'X-Odoo-Db',
        'X-Odoo-Username',
        'X-Odoo-Api-Key',
    ];

    /**
     * Handles allowed HTTP method.
     *
     * @var array
     */
    public const allowed_methods = ['POST'];

    /**
     * Returns json as static bridge content type.
     *
     * @return string.
     */
    protected function content_type()
    {
        return 'application/json';
    }

    /**
     * Performs an http request to Odoo REST API.
     *
     * @param array $payload Payload data.
     * @param array $attachments Submission's attached files.
     *
     * @return array|WP_Error Http request response.
     */
    protected function do_submit($payload, $attachments = [])
    {
        if (isset($payload['lang']) && $payload['lang'] === 'ca') {
            $payload['lang'] = 'ca_ES';
        }

        if (!empty($payload)) {
            $payload = [
                'jsonrpc' => '2.0',
                'params' => $payload,
            ];
        }

        $response = parent::do_submit($payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if (isset($response['data']['error'])) {
            return new WP_Error(
                'financoop_rpc_error',
                $response['data']['error']['message'],
                $response['data']['error']['data']
            );
        }

        if (isset($response['data']['result']['error'])) {
            return new WP_Error(
                'financoop_api_error',
                $response['data']['result']['error']['message'],
                $response['data']['result']['error']['data']
            );
        }

        return $response;
    }

    /**
     * Bridge's endpoint fields schema getter.
     *
     * @return array
     */
    protected function api_schema()
    {
        if (
            !preg_match(
                '/\/api\/campaign\/\d+\/([a-z_]+)$/',
                $this->endpoint,
                $matches
            )
        ) {
            return [];
        }

        $source = $matches[1];

        $common_schema = [
            [
                'name' => 'vat',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'firstname',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'lastname',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'email',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'address',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'zip_code',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'phone',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'lang',
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'country_code',
                'schema' => ['type' => 'string'],
            ],
        ];

        switch ($source) {
            case 'subscription_request':
                return array_merge(
                    [
                        [
                            'name' => 'ordered_parts',
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'type',
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    $common_schema
                );
                break;
            case 'donation_request':
                return array_merge(
                    [
                        [
                            'name' => 'donation_amount',
                            'schema' => ['type' => 'integer'],
                        ],
                        // [
                        //     'name' => 'tax_receipt_option',
                        //     'schema' => ['type' => 'string'],
                        // ],
                    ],
                    $common_schema
                );
                break;
            case 'loan_request':
                return array_merge(
                    [
                        [
                            'name' => 'loan_amount',
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    $common_schema
                );
                break;
        }
    }
}

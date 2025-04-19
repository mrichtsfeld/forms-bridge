<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once 'class-zoho-form-bridge.php';
require_once 'class-zoho-form-bridge-template.php';

require_once 'api-functions.php';

/**
 * Zoho Addon class.
 */
class Zoho_Addon extends Addon
{
    /**
     * Handles the addon name.
     *
     * @var string
     */
    protected static $name = 'Zoho';

    /**
     * Handles the addon's API name.
     *
     * @var string
     */
    protected static $api = 'zoho';

    /**
     * Handles the addon's custom bridge class.
     *
     * @var string
     */
    protected static $bridge_class = '\FORMS_BRIDGE\Zoho_Form_Bridge';

    /**
     * Handles the addon's custom form bridge template class.
     *
     * @var string
     */
    protected static $bridge_template_class = '\FORMS_BRIDGE\Zoho_Form_Bridge_Template';

    /**
     * Addon constructor. Inherits from the abstract addon and initializes
     * custom hooks.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);
        static::custom_hooks();
    }

    /**
     * Addon custom hooks.
     */
    protected static function custom_hooks()
    {
        add_filter(
            'forms_bridge_zoho_credentials',
            static function ($credentials) {
                if (!wp_is_numeric_array($credentials)) {
                    $credentials = [];
                }

                return array_merge(
                    $credentials,
                    self::setting()->credentials ?: []
                );
            },
            10,
            1
        );

        add_filter(
            'forms_bridge_zoho_credential',
            static function ($credential, $name) {
                if ($credential) {
                    return $credential;
                }

                $credentials = self::setting()->credentials ?: [];
                foreach ($credentials as $credential) {
                    if ($credential['name'] === $name) {
                        return $credential;
                    }
                }
            },
            10,
            2
        );
    }

    /**
     * Registers the setting and its fields.
     *
     * @return array Addon's settings configuration.
     */
    protected static function setting_config()
    {
        return [
            static::$api,
            self::merge_setting_config([
                'credentials' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                            'organization_id' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                            'client_id' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                            'client_secret' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                        ],
                        'required' => [
                            'name',
                            'organization_id',
                            'client_id',
                            'client_secret',
                        ],
                    ],
                ],
                'bridges' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'endpoint' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                            'scope' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                        ],
                        'required' => ['credential', 'endpoint', 'scope'],
                    ],
                ],
            ]),
            [
                'credentials' => [],
                'bridges' => [],
            ],
        ];
    }

    /**
     * Apply settings' data validations before db updates.
     *
     * @param array $data Setting data.
     *
     * @return array Validated setting data.
     */
    protected static function validate_setting($data, $setting)
    {
        $data['bridges'] = self::validate_bridges(
            $data['bridges'],
            $data['credentials']
        );

        return $data;
    }

    /**
     * Validate bridge settings. Filters bridges with inconsistencies with
     * current store state.
     *
     * @param array $bridges Array with bridge configurations.
     * @param array $credentials Array with credentials data.
     *
     * @return array Array with valid bridge configurations.
     */
    private static function validate_bridges($bridges, $credentials)
    {
        if (!wp_is_numeric_array($bridges)) {
            return [];
        }

        $credentials = array_map(function ($credential) {
            return $credential['name'];
        }, $credentials);

        $uniques = [];
        $validated = [];
        foreach ($bridges as $bridge) {
            $bridge = self::validate_bridge($bridge, $uniques);

            if (!$bridge) {
                continue;
            }

            if (!in_array($bridge['credential'] ?? null, $credentials)) {
                $bridge['credential'] = '';
            }

            $bridge['scope'] = $bridge['scope'] ?? '';
            $bridge['endpoint'] = $bridge['endpoint'] ?? '';

            $bridge['is_valid'] =
                $bridge['is_valid'] &&
                !empty($bridge['endpoint']) &&
                !empty($bridge['scope']) &&
                !empty($bridge['credential']);

            $validated[] = $bridge;
        }

        return $validated;
    }

    /**
     * Performs a request against the backend to check the connexion status.
     *
     * @param string $backend Target backend name.
     * @params WP_REST_Request $request Current REST request.
     *
     * @return array Ping result.
     */
    protected function do_ping($backend, $request)
    {
        [$credential] = self::validate_credentials(
            [$request['credential']],
            [$backend]
        );
        if (empty($credential)) {
            return ['success' => false];
        }

        self::temp_register_credentials($credential);

        $bridge = new Zoho_Form_Bridge(
            [
                'credential' => $credential['name'],
                'endpoint' => '/crm/v7',
                'scope' => 'ZohoCRM.settings.ALL',
            ],
            'zoho'
        );

        return ['success' => $bridge->check_credentials()];
    }

    /**
     * Performs a GET request against the backend endpoint and retrive the response data.
     *
     * @param string $backend Target backend name.
     * @param string $endpoint Target endpoint name.
     * @params WP_REST_Request $request Current REST request.
     *
     * @return array Fetched records.
     */
    protected function do_fetch($backend, $endpoint, $request)
    {
        [$credential] = self::validate_credentials(
            [$request['credential']],
            [$backend]
        );
        if (empty($credential)) {
            return ['success' => false];
        }

        self::temp_register_credentials($credential);

        $bridge = new Zoho_Form_Bridge(
            [
                'credential' => $credential['name'],
                'endpoint' => $endpoint,
                'scope' => 'ZohoCRM.modules.ALL',
                'method' => 'GET',
            ],
            'zoho'
        );

        $response = $bridge->submit([]);
        if (is_wp_error($response)) {
            return [];
        }

        return $response['data'];
    }

    /**
     * Performs an introspection of the backend endpoint and returns API fields
     * and accepted content type.
     *
     * @param string $backend Target backend name.
     * @param string $endpoint Target endpoint name.
     * @params WP_REST_Request $request Current REST request.
     *
     * @return array List of fields and content type of the endpoint.
     */
    protected function get_schema($backend, $endpoint, $request)
    {
        [$credential] = self::validate_credentials(
            [$request['credential']],
            [$backend]
        );
        if (empty($credential)) {
            return ['success' => false];
        }

        self::temp_register_credentials($credential);

        $bridge = new Zoho_Form_Bridge(
            [
                'credential' => $credential['name'],
                'endpoint' => $endpoint,
                'scope' => 'ZohoCRM.settings.layouts.READ',
            ],
            'zoho'
        );

        return $bridge->api_fields;
    }

    private static function temp_register_credentials($data)
    {
        add_filter(
            'forms_bridge_zoho_credential',
            static function ($credential, $name) use ($data) {
                if ($credential instanceof Zoho_Credential) {
                    return $credential;
                }

                if ($name === $data['name']) {
                    return new Zoho_Credential($data);
                }
            },
            90,
            2
        );
    }
}

Zoho_Addon::setup();

<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once 'class-zoho-credential.php';
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

                return array_merge($credentials, self::credentials());
            },
            10,
            1
        );

        add_filter(
            'forms_bridge_zoho_credential',
            static function ($credential, $name) {
                if ($credential instanceof Zoho_Credential) {
                    return $credential;
                }

                $credentials = self::credentials();
                foreach ($credentials as $credential) {
                    if ($credential->name === $name) {
                        return $credential;
                    }
                }
            },
            10,
            2
        );
    }

    /**
     * Addon credentials' instances getter.
     *
     * @return array List with available credentials instances.
     */
    protected static function credentials()
    {
        return array_map(
            static function ($data) {
                return new Zoho_Credential($data);
            },
            self::setting()->credentials ?: []
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
                            'name' => ['type' => 'string'],
                            'organization_id' => ['type' => 'string'],
                            'client_id' => ['type' => 'string'],
                            'client_secret' => ['type' => 'string'],
                            'backend' => ['type' => 'string'],
                        ],
                        'required' => [
                            'name',
                            'organization_id',
                            'client_id',
                            'client_secret',
                            'backend',
                        ],
                    ],
                ],
                'bridges' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'credential' => ['type' => 'string'],
                            'endpoint' => ['type' => 'string'],
                            'scope' => ['type' => 'string'],
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
        $backends =
            \HTTP_BRIDGE\Settings_Store::setting('general')->backends ?: [];

        $data['credentials'] = self::validate_credentials(
            $data['credentials'],
            $backends
        );

        $data['bridges'] = self::validate_bridges(
            $data['bridges'],
            $data['credentials']
        );

        return $data;
    }

    /**
     * Credentials setting field validation. Filters inconsistent keys
     * based on the Http_Bridge's backends store state.
     *
     * @param array $credentials Collection of credentials data.
     * @param array $backends
     *
     * @return array Validated credentials.
     */
    private static function validate_credentials($credentials, $backends)
    {
        if (!wp_is_numeric_array($credentials)) {
            return [];
        }

        $backend_names = array_map(function ($backend) {
            return $backend['name'];
        }, $backends);

        $uniques = [];
        $validated = [];
        foreach ($credentials as $credential) {
            if (empty($credential['name'])) {
                continue;
            }

            if (in_array($credential['name'], $uniques)) {
                continue;
            }

            if (!in_array($credential['backend'] ?? null, $backend_names)) {
                $credential['backend'] = '';
            }

            $credential['organization_id'] =
                $credential['organization_id'] ?? '';
            $credential['client_id'] = $credential['client_id'] ?? '';
            $credential['client_secret'] = $credential['client_secret'] ?? '';

            $validated[] = $credential;
            $uniques[] = $credential['name'];
        }

        return $validated;
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
                !empty($bridge['endpoint']);

            $validated[] = $bridge;
        }

        return $validated;
    }
}

Zoho_Addon::setup();

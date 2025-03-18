<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once 'class-zoho-form-bridge.php';
require_once 'class-zoho-form-bridge-template.php';

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
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    protected static $bridge_class = '\FORMS_BRIDGE\Zoho_Form_Bridge';

    /**
     * Registers the setting and its fields.
     *
     * @return array Addon's settings configuration.
     */
    protected static function setting_config()
    {
        return [
            self::$api,
            [
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
                            'name' => ['type' => 'string'],
                            'backend' => ['type' => 'string'],
                            'form_id' => ['type' => 'string'],
                            'endpoint' => ['type' => 'string'],
                            'scope' => ['type' => 'string'],
                            'mappers' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'properties' => [
                                        'from' => ['type' => 'string'],
                                        'to' => ['type' => 'string'],
                                        'cast' => [
                                            'type' => 'string',
                                            'enum' => [
                                                'boolean',
                                                'string',
                                                'integer',
                                                'float',
                                                'json',
                                                'csv',
                                                'concat',
                                                'null',
                                            ],
                                        ],
                                    ],
                                    'required' => ['from', 'to', 'cast'],
                                ],
                            ],
                            'template' => ['type' => 'string'],
                            'is_valid' => ['type' => 'boolean'],
                        ],
                        'required' => [
                            'name',
                            'backend',
                            'form_id',
                            'endpoint',
                            'scope',
                            'mappers',
                            'is_valid',
                        ],
                    ],
                ],
            ],
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
            \HTTP_BRIDGE\Settings_Store::setting('general')->backends ?: []
        );

        return $data;
    }

    /**
     * Validate bridge settings. Filters bridges with inconsistencies with
     * current store state.
     *
     * @param array $bridges Array with bridge configurations.
     * @param array $backends Array with backends data.
     *
     * @return array Array with valid bridge configurations.
     */
    private static function validate_bridges($bridges, $backends)
    {
        if (!wp_is_numeric_array($bridges)) {
            return [];
        }

        $form_ids = array_map(function ($form) {
            return $form['_id'];
        }, apply_filters('forms_bridge_forms', []));

        $backend_names = array_map(function ($backend) {
            return $backend['name'];
        }, $backends);

        $uniques = [];
        $validated = [];
        foreach ($bridges as $bridge) {
            if (empty($bridge['name'])) {
                continue;
            }

            if (in_array($bridge['name'], $uniques)) {
                continue;
            } else {
                $uniques[] = $bridge['name'];
            }

            if (!in_array($bridge['form_id'] ?? null, $form_ids)) {
                $bridge['form_id'] = '';
            }

            if (!in_array($bridge['backend'] ?? null, $backend_names)) {
                $bridge['backend'] = '';
            }

            $bridge['scope'] = $bridge['scope'] ?? '';
            $bridge['endpoint'] = $bridge['endpoint'] ?? '';

            $bridge['mappers'] = array_values(
                array_filter((array) $bridge['mappers'], function ($pipe) {
                    return !(
                        empty($pipe['from']) ||
                        empty($pipe['to']) ||
                        empty($pipe['cast'])
                    );
                })
            );

            $is_valid = true;
            unset($bridge['is_valid']);
            foreach ($bridge as $field => $value) {
                if ($field === 'mappers') {
                    continue;
                }

                $is_valid = $is_valid && !empty($value);
            }

            $bridge['is_valid'] = $is_valid;
            $validated[] = $bridge;
        }

        return $validated;
    }
}

Zoho_Addon::setup();

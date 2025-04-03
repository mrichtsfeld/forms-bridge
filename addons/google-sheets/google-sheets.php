<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once 'vendor/autoload.php';

require_once 'class-gs-store.php';
require_once 'class-gs-client.php';
require_once 'class-gs-rest-controller.php';
require_once 'class-gs-ajax-controller.php';
require_once 'class-gs-service.php';
require_once 'class-gs-form-bridge.php';
require_once 'class-gs-form-bridge-template.php';

/**
 * Google Sheets addon class.
 */
class Google_Sheets_Addon extends Addon
{
    /**
     * Handles the addon name.
     *
     * @var string
     */
    protected static $name = 'Google Sheets';

    /**
     * Handles the addon's API name.
     *
     * @var string
     */
    protected static $api = 'google-sheets';

    /**
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    protected static $bridge_class = '\FORMS_BRIDGE\Google_Sheets_Form_Bridge';

    /**
     * Handles the addon's custom form bridge template class.
     *
     * @var string
     */
    protected static $bridge_template_class = '\FORMS_BRIDGE\Google_Sheets_Form_Bridge_Template';

    /**
     * Addon constructor. Inherits from the abstract addon and sets up interceptors
     * and custom hooks.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);

        self::setting_hooks();

        // Discard attachments for google sheets submissions
        add_filter(
            'forms_bridge_attachments',
            static function ($attachments, $bridge) {
                if ($bridge->api === self::$api) {
                    return [];
                }

                return $attachments;
            },
            90,
            2
        );
    }

    /**
     * Intercept setting hooks and add authorized attribute.
     */
    private static function setting_hooks()
    {
        // Patch authorized state on the setting default value
        add_filter(
            'wpct_setting_default',
            static function ($data, $name) {
                if ($name !== self::setting_name()) {
                    return $data;
                }

                return array_merge($data, [
                    'authorized' => Google_Sheets_Service::is_authorized(),
                ]);
            },
            10,
            2
        );

        add_filter(
            'wpct_validate_setting',
            static function ($data, $setting) {
                if ($setting->full_name() !== self::setting_name()) {
                    return $data;
                }

                unset($data['authorized']);
                return $data;
            },
            9,
            2
        );

        add_filter(
            'option_' . self::setting_name(),
            static function ($value) {
                if (!is_array($value)) {
                    return $value;
                }

                $value['authorized'] = Google_Sheets_Service::is_authorized();
                return $value;
            },
            9,
            1
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
            self::$api,
            self::merge_setting_config([
                'bridges' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'spreadsheet' => ['type' => 'string'],
                            'tab' => ['type' => 'string'],
                        ],
                        'required' => ['spreadsheet', 'tab'],
                    ],
                ],
            ]),
            [
                'bridges' => [],
            ],
        ];
    }

    /**
     * Sanitizes the setting value before updates.
     *
     * @param array $data Setting data.
     * @param Setting $setting Setting instance.
     *
     * @return array Sanitized data.
     */
    protected static function validate_setting($data, $setting)
    {
        $data['bridges'] = self::validate_bridges($data['bridges']);
        return $data;
    }

    /**
     * Validate bridge settings. Filters bridges with inconsistencies with
     * current store state.
     *
     * @param array $bridges Array with bridge configurations.
     *
     * @return array Array with valid bridge configurations.
     */
    private static function validate_bridges($bridges)
    {
        if (!wp_is_numeric_array($bridges)) {
            return [];
        }

        $uniques = [];
        $validated = [];
        foreach ($bridges as $bridge) {
            $bridge = self::validate_bridge($bridge, $uniques);

            if (!$bridge) {
                continue;
            }

            $bridge['spreadsheet'] = $bridge['spreadsheet'] ?? '';
            $bridge['tab'] = $bridge['tab'] ?? '';

            $bridge['is_valid'] =
                $bridge['is_valid'] &&
                !empty($bridge['spreadsheet']) &&
                !empty($bridge['tab']);

            $validated[] = $bridge;
        }

        return $validated;
    }
}

Google_Sheets_Addon::setup();

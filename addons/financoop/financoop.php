<?php

namespace FORMS_BRIDGE;

use HTTP_BRIDGE\Http_Backend;
use WP_Error;
use WP_REST_Server;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/rest-api/rest-api.php';

require_once 'class-financoop-form-bridge.php';
require_once 'class-financoop-form-bridge-template.php';

/**
 * FinanCoop Addon class.
 */
class Finan_Coop_Addon extends Rest_Addon
{
    /**
     * Handles the addon name.
     *
     * @var string
     */
    protected static $name = 'FinanCoop';

    /**
     * Handles the addon's API name.
     *
     * @var string
     */
    protected static $api = 'financoop';

    /**
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    protected static $bridge_class = '\FORMS_BRIDGE\Finan_Coop_Form_Bridge';

    /**
     * Handles the addon's custom form bridge template class.
     *
     * @var string
     */
    protected static $bridge_template_class = '\FORMS_BRIDGE\Finan_Coop_Form_Bridge_Template';

    /**
     * Addon constructor. Inherits from the abstract addon and initializes the
     * addon's rest api.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);

        add_action('rest_api_init', static function () {
            $namespace = REST_Settings_Controller::namespace();
            $version = REST_Settings_Controller::version();

            register_rest_route(
                "{$namespace}/v{$version}",
                '/financoop/campaigns',
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => static function ($request) {
                        return self::fetch_campaigns($request);
                    },
                    'permission_callback' => static function () {
                        return REST_Settings_Controller::permission_callback();
                    },
                ]
            );

            register_rest_route(
                "{$namespace}/v{$version}",
                'financoop/campaigns/(<?P<campaign_id>\d+)',
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => static function ($request) {
                        return self::fetch_campaigns($request);
                    },
                    'permission_callback' => static function () {
                        return REST_Settings_Controller::permission_callback();
                    },
                ]
            );
        });
    }

    /**
     * Backend instance getter. If backend isn't registered, add a
     * ephemeral entry on the backends registry.
     *
     * @param array $params Backend data.
     *
     * @return Http_Backend
     */
    private static function get_backend($params)
    {
        if (isset($params['backend'])) {
            $backend = apply_filters(
                'http_bridge_backend',
                null,
                $params['backend']
            );
        } else {
            $base_url = filter_var(
                $params['base_url'] ?? null,
                FILTER_VALIDATE_URL
            );
            $database = sanitize_text_field($params['database'] ?? null);
            $username = sanitize_text_field($params['username'] ?? null);
            $api_key = sanitize_text_field($params['api_key'] ?? null);

            if (
                empty($base_url) ||
                empty($database) ||
                empty($username) ||
                empty($api_key)
            ) {
                return;
            }

            $backend_data = [
                'name' => '__financoop-' . time(),
                'base_url' => $base_url,
                'headers' => [
                    [
                        'name' => 'X-Odoo-Db',
                        'value' => $database,
                    ],
                    [
                        'name' => 'X-Odoo-Username',
                        'value' => $username,
                    ],
                    [
                        'name' => 'X-Odoo-Api-Key',
                        'value' => $api_key,
                    ],
                ],
            ];

            add_filter(
                'wpct_setting_data',
                static function ($setting_data, $name) use ($backend_data) {
                    if ($name !== 'http-bridge_general') {
                        return $setting_data;
                    }

                    $index = array_search(
                        $backend_data['name'],
                        array_column($setting_data['backends'], 'name')
                    );
                    if ($index === false) {
                        $setting_data['backends'][] = $backend_data;
                    }

                    return $setting_data;
                },
                20,
                2
            );

            return new Http_Backend($backend_data['name']);
        }

        return $backend;
    }

    private static function fetch_campaigns($request)
    {
        $params = $request->get_json_params();
        $backend = self::get_backend($params);

        if (empty($backend)) {
            return new WP_Error(
                'bad_request',
                __('Backend is unkown', 'forms-bridge'),
                ['params' => $params]
            );
        }

        $endpoint = '/api/campaign';

        if ($campaign_id = $request['campaign_id']) {
            $endpoint .= '/' . (int) $campaign_id;
        }

        $response = $backend->get($endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['data']['data'];
    }
}

Finan_Coop_Addon::setup();

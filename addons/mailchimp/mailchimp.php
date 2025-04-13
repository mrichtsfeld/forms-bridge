<?php

namespace FORMS_BRIDGE;

use WP_REST_Server;
use HTTP_BRIDGE\Http_Backend;
use WP_Error;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/rest-api/rest-api.php';

require_once 'class-mailchimp-form-bridge.php';
require_once 'class-mailchimp-form-bridge-template.php';

/**
 * Mapchimp Addon class.
 */
class Mailchimp_Addon extends Rest_Addon
{
    /**
     * Handles the addon name.
     *
     * @var string
     */
    protected static $name = 'Mailchimp';

    /**
     * Handles the addon's API name.
     *
     * @var string
     */
    protected static $api = 'mailchimp';

    /**
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    protected static $bridge_class = '\FORMS_BRIDGE\Mailchimp_Form_Bridge';

    /**
     * Handles the addon's custom form bridge template class.
     *
     * @var string
     */
    protected static $bridge_template_class = '\FORMS_BRIDGE\Mailchimp_Form_Bridge_Template';

    /**
     * Addon constructor. Inherits from the abstrac addon constructor and initializes
     * REST API endpoints.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);

        add_action(
            'rest_api_init',
            static function () {
                $namespace = REST_Settings_Controller::namespace();
                $version = REST_Settings_Controller::version();

                register_rest_route(
                    "{$namespace}/v{$version}",
                    '/mailchimp/lists',
                    [
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => static function ($request) {
                            $params = $request->get_json_params();
                            return self::fetch_lists($params);
                        },
                        'permission_callback' => static function () {
                            return REST_Settings_Controller::permission_callback();
                        },
                    ]
                );
            },
            10,
            0
        );
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
        if (isset($params['name'])) {
            $backend = apply_filters(
                'http_bridge_backend',
                null,
                $params['name']
            );

            if ($backend) {
                return $backend;
            }
        }

        $base_url = filter_var(
            $params['base_url'] ?? null,
            FILTER_VALIDATE_URL
        );

        if (!$base_url) {
            $index = array_search(
                'datacenter',
                array_column($params['headers'], 'name')
            );
            if ($index === false) {
                return;
            }

            $datacenter = $params['headers'][$index]['value'];
            $params['base_url'] = "https://{$datacenter}.api.mailchimp.com";
        }

        $params['name'] = $params['name'] ?? '__mailchimp-' . time();
        return new Http_Backend($params);
    }

    private static function api_fetch($endpoint, $backend_params)
    {
        $backend = self::get_backend($backend_params);

        if (empty($backend)) {
            return new WP_Error(
                'bad_request',
                __('Backend is unkown', 'forms-bridge'),
                ['params' => $backend_params]
            );
        }

        $headers = $backend->headers;
        $api_key = $headers['api-key'] ?? null;

        if (empty($api_key)) {
            return new WP_Error(
                'unauthorized',
                __('Invalid Brevo API credentials', 'forms-bridge'),
                ['api_key' => $api_key]
            );
        }

        add_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Mailchimp_Form_Bridge::basic_auth',
            10,
            1
        );

        $response = $backend->get(
            $endpoint,
            [],
            [
                'api-key' => $api_key,
                'accept' => 'application/json',
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['data'];
    }

    private static function fetch_lists($backend_params)
    {
        $data = self::api_fetch('/3.0/lists', $backend_params);
        if (is_wp_error($data)) {
            return [];
        }

        return $data['lists'];
    }
}

Mailchimp_Addon::setup();

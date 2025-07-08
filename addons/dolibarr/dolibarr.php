<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/rest-api/rest-api.php';

require_once 'class-dolibarr-form-bridge.php';
require_once 'hooks.php';
require_once 'api.php';

/**
 * Dolibarr Addon class.
 */
class Dolibarr_Addon extends Rest_Addon
{
    /**
     * Handles the addon's title.
     *
     * @var string
     */
    public const title = 'Dolibarr';

    /**
     * Handles the addon's name.
     *
     * @var string
     */
    public const name = 'dolibarr';

    /**
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    public const bridge_class = '\FORMS_BRIDGE\Dolibarr_Form_Bridge';

    protected function do_ping($backend, $credential)
    {
        $bridge = new Dolibarr_Form_Bridge([
            'name' => '__dolibarr-' . time(),
            'endpoint' => '/api/index.php/status',
            'method' => 'GET',
            'backend' => $backend,
        ]);

        $response = $bridge->submit();
        if (is_wp_error($response)) {
            return ['success' => false];
        }

        $code = $response['data']['success']['code'] ?? null;
        return $code === 200;
    }

    /**
     * Performs a GET request against the backend endpoint and retrive the response data.
     *
     * @param string $backend Target backend name.
     * @param string $endpoint Target endpoint name.
     * @param null $credential Credential data.
     *
     * @return array|WP_Error
     */
    protected function do_fetch($backend, $endpoint, $credential)
    {
        $bridge = new Dolibarr_Form_Bridge([
            'name' => '__dolibarr-' . time(),
            'endpoint' => $endpoint,
            'backend' => $backend,
            'method' => 'GET',
        ]);

        return $bridge->submit();
    }

    /**
     * Performs an introspection of the backend endpoint and returns API fields
     * and accepted content type.
     *
     * @param string $backend Target backend name.
     * @param string $endpoint Target endpoint name.
     * @param null $credential Credential data.
     *
     * @return array List of fields and content type of the endpoint.
     */
    protected function get_endpint_schema($backend, $endpoint, $credential)
    {
        $bridge = new Dolibarr_Form_Bridge([
            'name' => '__dolibarr-' . time(),
            'endpoint' => $endpoint,
            'backend' => $backend,
            'method' => 'GET',
        ]);

        return $bridge->endpoint_schema;
    }
}

Dolibarr_Addon::setup();

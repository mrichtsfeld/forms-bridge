<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/rest-api/rest-api.php';

require_once 'class-holded-form-bridge.php';
require_once 'hooks.php';
require_once 'api.php';

/**
 * REST API Addon class.
 */
class Holded_Addon extends Rest_Addon
{
    /**
     * Handles the addon's title.
     *
     * @var string
     */
    public const title = 'Holded';

    /**
     * Handles the addon's name.
     *
     * @var string
     */
    public const name = 'holded';

    /**
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    public const bridge_class = '\FORMS_BRIDGE\Holded_Form_Bridge';

    /**
     * Performs a request against the backend to check the connexion status.
     *
     * @param string $backend Target backend name.
     * @param null $credential Credential data.
     *
     * @return array Ping result.
     */
    protected function do_ping($backend, $credential)
    {
        $bridge = new Holded_Form_Bridge([
            'name' => '__holded-' . time(),
            'endpoint' => '/api/invoicing/v1/contacts',
            'method' => 'GET',
            'backend' => $backend,
        ]);

        $response = $bridge->submit(['limit' => 1]);
        return ['success' => !is_wp_error($response)];
    }

    /**
     * Performs a GET request against the backend endpoint and retrive the response data.
     *
     * @param string $backend Target backend name.
     * @param string $endpoint Target endpoint name.
     * @param null $credential Credential data.
     *
     * @return array Fetched records.
     */
    protected function do_fetch($backend, $endpoint, $credential)
    {
        $bridge = new Holded_Form_Bridge([
            'name' => '__holded-' . time(),
            'endpoint' => $endpoint,
            'backend' => $backend,
            'method' => 'GET',
        ]);

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
     * @params null $credential Credential data.
     *
     * @return array List of fields and content type of the endpoint.
     */
    protected function get_schema($backend, $endpoint, $credential)
    {
        $bridge = new Holded_Form_Bridge([
            'name' => '__holded-' . time(),
            'endpoint' => $endpoint,
            'backend' => $backend,
            'method' => 'POST',
        ]);

        return $bridge->endpoint_schema;
    }
}

Holded_Addon::setup();

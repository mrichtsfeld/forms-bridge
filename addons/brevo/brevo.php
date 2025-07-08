<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/rest-api/rest-api.php';

require_once 'class-brevo-form-bridge.php';
require_once 'hooks.php';
require_once 'api.php';

/**
 * REST API Addon class.
 */
class Brevo_Addon extends Rest_Addon
{
    /**
     * Handles the addon's title.
     *
     * @var string
     */
    public const title = 'Brevo';

    /**
     * Handles the addon's name.
     *
     * @var string
     */
    public const name = 'brevo';

    /**
     * Handles the addom's custom bridge class.
     *
     * @var string
     */
    public const bridge_class = '\FORMS_BRIDGE\Brevo_Form_Bridge';

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
        $bridge = new Brevo_Form_Bridge([
            'name' => '__brevo-' . time(),
            'endpoint' => '/v3/contacts/lists',
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
        $bridge = new Brevo_Form_Bridge([
            'name' => '__brevo-' . time(),
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
    protected function get_endpoint_schema($backend, $endpoint, $credential)
    {
        $bridge = new Brevo_Form_Bridge([
            'name' => '__brevo-' . time(),
            'endpoint' => $endpoint,
            'backend' => $backend,
            'method' => 'GET',
        ]);

        return $bridge->endpoint_schema;
    }
}

Brevo_Addon::setup();

<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the Bigin API protocol.
 */
class Bigin_Form_Bridge extends Zoho_Form_Bridge
{
    /**
     * Handles bridge class API name.
     *
     * @var string
     */
    public const addon = 'bigin';

    /**
     * Handles the zoho oauth service name.
     *
     * @var string
     */
    protected const zoho_oauth_service = 'ZohoBigin';

    /**
     * Handles the oauth access token transient name.
     *
     * @var string
     */
    protected const token_transient = 'forms-bridge-bigin-oauth-access-token';

    public static function schema()
    {
        $schema = parent::schema();
        $schema['properties']['scope']['default'] = 'ZohoBigin.modules.ALL';
        return $schema;
    }

    /**
     * Bridge's endpoint fields schema getter.
     *
     * @param null $endpoint Layout metadata endpoint.
     *
     * @return array
     */
    protected function endpoint_schema($endpoint = null)
    {
        return parent::endpoint_schema('/bigin/v2/settings/layouts');
    }
}

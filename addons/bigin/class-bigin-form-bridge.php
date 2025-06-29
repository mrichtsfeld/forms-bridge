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
    protected $api = 'bigin';

    /**
     * Handles the zoho oauth service name.
     *
     * @var string
     */
    protected static $zoho_oauth_service = 'ZohoBigin';

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

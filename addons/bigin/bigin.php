<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/zoho/zoho.php';

require_once 'class-bigin-form-bridge.php';
require_once 'class-bigin-form-bridge-template.php';

require_once 'api-functions.php';

/**
 * Bigin Addon class.
 */
class Bigin_Addon extends Zoho_Addon
{
    /**
     * Handles the addon name.
     *
     * @var string
     */
    protected static $name = 'Bigin';

    /**
     * Handles the addon's API name.
     *
     * @var string
     */
    protected static $api = 'bigin';

    /**
     * Handles the addon's custom bridge class.
     *
     * @var string
     */
    protected static $bridge_class = '\FORMS_BRIDGE\Bigin_Form_Bridge';

    /**
     * Handles the addon's custom form bridge template class.
     *
     * @var string
     */
    protected static $bridge_template_class = '\FORMS_BRIDGE\Bigin_Form_Bridge_Template';

    protected static function custom_hooks()
    {
        add_filter(
            'forms_bridge_bigin_credentials',
            static function ($credentials) {
                if (!wp_is_numeric_array($credentials)) {
                    $credentials = [];
                }

                return array_merge(
                    $credentials,
                    self::setting()->credentials ?: []
                );
            },
            10,
            1
        );

        add_filter(
            'forms_bridge_bigin_credential',
            static function ($credential, $name) {
                if ($credential) {
                    return $credential;
                }

                $credentials = self::setting()->credentials ?: [];
                foreach ($credentials as $credential) {
                    if ($credential['name'] === $name) {
                        return $credential;
                    }
                }
            },
            10,
            2
        );
    }

    /**
     * Performs a request against the backend to check the connexion status.
     *
     * @param string $backend Target backend name.
     * @params array $credential Current REST request.
     *
     * @return array Ping result.
     */
    protected function do_ping($backend, $credential = [])
    {
        return ['success' => true];
    }
}

Bigin_Addon::setup();

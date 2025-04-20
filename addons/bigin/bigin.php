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
     * Handles the zoho oauth service name.
     *
     * @var string
     */
    protected static $zoho_oauth_service = 'ZohoBigin';

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
}

Bigin_Addon::setup();

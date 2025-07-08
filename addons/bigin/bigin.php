<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

require_once FORMS_BRIDGE_ADDONS_DIR . '/zoho/zoho.php';

require_once 'class-bigin-form-bridge.php';
require_once 'hooks.php';
require_once 'api.php';

/**
 * Bigin Addon class.
 */
class Bigin_Addon extends Zoho_Addon
{
    /**
     * Handles the addon's title.
     *
     * @var string
     */
    public const title = 'Bigin';

    /**
     * Handles the addon's name.
     *
     * @var string
     */
    public const name = 'bigin';

    /**
     * Handles the zoho oauth service name.
     *
     * @var string
     */
    protected const zoho_oauth_service = 'ZohoBigin';

    /**
     * Handles the addon's custom bridge class.
     *
     * @var string
     */
    public const bridge_class = '\FORMS_BRIDGE\Bigin_Form_Bridge';
}

Bigin_Addon::setup();

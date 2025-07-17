<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Zoho_Credential extends Oauth_Credential
{
    protected const zoho_oauth_app = 'ZohoCRM';

    protected const transient = 'forms-bridge-zoho-credential';

    protected function oauth_service_url($verb)
    {
        $base = "https://accounts.{$this->region}/oauth/v2";

        if ($verb === 'revoke') {
            return $base . '/token/revoke';
        }

        return "{$base}/{$verb}";
    }
}

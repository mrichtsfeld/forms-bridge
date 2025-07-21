<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class RPC_Credential extends Credential
{
    public static function schema($addon = null)
    {
        $schema = parent::schema();

        $schema['title'] = 'rpc-credential';

        $schema['properties']['schema']['enum'] = ['RPC'];
        $schema['properties']['schema']['value'] = 'RPC';

        $schema['properties']['client_id']['name'] = __(
            'Username',
            'forms-bridge'
        );
        $schema['properties']['client_secret']['name'] = __(
            'Password',
            'forms-bridge'
        );

        $schema['required'][] = 'realm';

        if (!$addon) {
            return $schema;
        }

        return apply_filters('forms_bridge_credential_schema', $schema, $addon);
    }

    public function login()
    {
        return [$this->realm, $this->client_id, $this->client_secret];
    }
}

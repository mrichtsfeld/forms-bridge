<?php

$setting_names = ['rest-api', 'odoo', 'financoop', 'google-sheets'];

foreach ($setting_names as $setting_name) {
    $setting = get_option('forms-bridge_' . $setting_name, []);

    if (isset($setting['form_hooks'])) {
        $setting['bridges'] = $setting['form_hooks'];
        unset($setting['form_hooks']);
        update_option('forms-bridge_' . $setting_name, $setting);
    }
}

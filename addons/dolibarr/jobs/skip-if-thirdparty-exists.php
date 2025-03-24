<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_skip_thirdparty($payload, $bridge)
{
    $payload = forms_bridge_dolibarr_search_thirdparty($payload, $bridge);

    if (is_wp_error($payload)) {
        return $payload;
    }

    if (isset($payload['socid'])) {
        return;
    }

    return $payload;
}

return [
    'title' => __('Skip if thirdparty exists', 'forms-bridge'),
    'description' => __(
        'Aborts form submission if a contact with same email exists.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_skip_thirdparty',
    'input' => ['email'],
    'output' => [],
];

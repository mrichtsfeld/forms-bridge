<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_skip_contact($payload, $bridge)
{
    $payload = forms_bridge_dolibarr_search_contact($payload, $bridge);

    if (is_wp_error($payload)) {
        return $payload;
    }

    if (isset($payload['contact_id'])) {
        return;
    }

    return $payload;
}

return [
    'title' => __('Skip if contact exists', 'forms-bridge'),
    'description' => __(
        'Aborts form submission if a contact with same email exists.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_skip_contact',
    'input' => ['email'],
    'output' => [],
];

<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_owner_by_email($payload, $bridge)
{
    $backend = $bridge->backend;
    $dolapikey = $bridge->api_key->key;

    $owner_email = filter_var(
        $payload['owner_email'] ?? '',
        FILTER_SANITIZE_EMAIL
    );

    if (empty($owner_email)) {
        return $payload;
    }

    $response = $backend->get(
        '/api/index.php/users',
        [
            'limit' => '1',
            'sqlfilters' => "(t.email:=:'{$owner_email}')",
        ],
        ['DOLAPIKEY' => $dolapikey]
    );

    if (is_wp_error($response)) {
        do_action('forms_bridge_on_failure', $bridge, $response, $payload);
        return;
    }

    $payload['userownerid'] = $response['data'][0]['id'];
    unset($payload['owner_email']);

    return $payload;
}

return [
    'title' => __('Owner ID by email', 'forms-bridge'),
    'description' => __(
        'Search for user ID by email and sets it value as the "userownerid" of the payload.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_owner_by_email',
    'input' => ['owner_email*'],
    'output' => ['userownerid'],
];

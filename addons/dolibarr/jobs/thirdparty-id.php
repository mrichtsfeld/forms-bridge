<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_thirdparty_id($payload, $bridge)
{
    $result = forms_bridge_dolibarr_search_thirdparty($payload, $bridge);

    if (is_wp_error($result)) {
        return $result;
    }

    if (isset($result['socid'])) {
        return array_merge($payload, [
            'socid' => $result['socid'],
        ]);
    }

    $backend = $bridge->backend;
    $dolapikey = $bridge->api_key->key;

    $payload = forms_bridge_dolibarr_next_code_client($payload, $bridge);
    if (is_wp_error($payload)) {
        return $payload;
    }

    $thirdparty = [
        'status' => $payload['status'] ?? '1',
        'typent_id' => $payload['typent_id'] ?? '8',
        'client' => $payload['client'] ?? '2',
        'code_client' => $payload['code_client'],
        'stcomm_id' => $payload['stcomm_id'] ?? '0',
    ];

    $thirdparty_fields = [
        'name',
        'firstname',
        'lastname',
        'idprof1',
        'address',
        'zip',
        'town',
        'country_id',
    ];

    foreach ($thirdparty_fields as $field) {
        if (isset($payload[$field])) {
            $thirdparty[$field] = $payload[$field];
        }
    }

    $response = $backend->post('/api/index.php/thirdparties', $thirdparty, [
        'DOLAPIKEY' => $dolapikey,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    foreach ($thirdparty_fields as $field) {
        if (isset($payload[$field])) {
            unset($payload[$field]);
        }
    }

    $payload['socid'] = $response['body'];
    return $payload;
}

return [
    'title' => __('Thirdparty ID', 'forms-bridge'),
    'description' => __(
        'Gets the ID of a third party and creates a new thirparty if it doesn\'t exists',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_thirdparty_id',
    'input' => ['email', 'firstname', 'lastname'],
    'output' => ['contact_id'],
];

<?php

function forms_bridge_dolibarr_next_code_client($payload, $bridge)
{
    $backend = $bridge->backend;
    $dolapikey = $bridge->api_key->key;

    $response = $backend->get(
        '/api/index.php/thirdparties',
        [
            'sortfield' => 't.rowid',
            'sortorder' => 'DESC',
            'limit' => 1,
        ],
        ['DOLAPIKEY' => $dolapikey]
    );

    if (is_wp_error($response)) {
        return $response;
    }

    $previus_code_client = $response['data'][0]['code_client'];

    [$prefix, $number] = explode('-', $previus_code_client);

    $next = strval($number + 1);
    while (strlen($next) < strlen($number)) {
        $next = '0' . $next;
    }

    $payload['code_client'] = $prefix . '-' . $next;
    return $payload;
}

return [
    'title' => __('Next code client', 'forms-brige'),
    'description' => __(
        'Query for the next valid thirdparty code client',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_next_code_client',
    'input' => [],
    'output' => ['code_client'],
];

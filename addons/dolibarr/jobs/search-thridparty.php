<?php

function forms_bridge_dolibarr_search_thirdparty($payload, $bridge)
{
    $backend = $bridge->backend;
    $dolapikey = $bridge->api_key->key;

    $sqlfilters = [];
    $search_fields = [
        'typent_id' => 'fk_typent',
        'idprof1' => 'siren',
        'name' => 'name',
        'email' => 'email',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
    ];

    foreach ($search_fields as $field => $db_field) {
        if (isset($payload[$field])) {
            $query =
                gettype($payload[$field]) === 'string'
                    ? "(t.{$db_field}:like:'{$payload[$field]}')"
                    : "(t.{$db_field}:=:{$payload[$field]})";

            $sqlfilters[] = $query;
        }
    }

    if (empty($sqlfilters)) {
        return $payload;
    }

    $sqlfilters = implode(' and ', $sqlfilters);

    $response = $backend->get(
        '/api/index.php/thirdparties',
        [
            'sortfield' => 't.rowid',
            'sortorder' => 'DESC',
            'limit' => '1',
            'sqlfilters' => $sqlfilters,
        ],
        ['DOLAPIKEY' => $dolapikey]
    );

    if (is_wp_error($response)) {
        $error_data = $response->get_error_data();
        $response_code = $error_data['response']['response']['code'];

        if ($response_code !== 404) {
            return $response;
        }
    }

    if (is_wp_error($response)) {
        return $payload;
    }

    foreach (array_keys($search_fields) as $field) {
        if (isset($payload[$field])) {
            unset($payload[$field]);
        }
    }

    return array_merge($payload, [
        'socid' => $response['data'][0]['id'],
    ]);
}

return [
    'title' => __('Search thirdparty', 'forms-bridge'),
    'description' => __(
        'Search for a thirdparty ID by multple fields',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_search_thirdparty',
    'input' => [
        'typent_id',
        'idprof1',
        'name',
        'firstname',
        'lastname',
        'email',
    ],
    'output' => [
        'socid',
        'typent_id',
        'idprof1',
        'name',
        'firstname',
        'lastname',
        'email',
    ],
];

<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_search_contact($payload, $bridge)
{
    $backend = $bridge->backend;
    $dolapikey = $bridge->api_key->key;

    $sqlfilters = [];
    $search_fields = [
        'email' => 'email',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'socid' => 'fk_soc',
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
        '/api/index.php/contacts',
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
        'contact_id' => $response['data'][0]['id'],
    ]);
}

return [
    'title' => __('Search contact', 'forms-bridge'),
    'description' => __(
        'Search for contact by multiple fields',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_search_contact',
    'input' => ['email', 'firstname', 'lastname'],
    'output' => ['contact_id'],
];

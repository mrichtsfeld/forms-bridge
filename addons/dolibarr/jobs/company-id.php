<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_company_id($payload, $bridge)
{
    if (!isset($payload['name'])) {
        return $payload;
    }

    $company = [
        'name' => $payload['name'],
        'status' => $payload['status'] ?? '1',
        'typent_id' => $payload['typent_id'] ?? '2',
        'client' => $payload['client'] ?? '2',
        'stcomm_id' => $payload['stcomm_id'] ?? '0',
    ];

    $company_fields = ['idprof1', 'address', 'zip', 'town', 'country_id'];

    foreach ($company_fields as $field) {
        if (isset($payload[$field])) {
            $company[$field] = $payload[$field];
        }
    }

    $result = forms_bridge_dolibarr_thirdparty_id($company, $bridge);

    if (is_wp_error($result)) {
        return $result;
    }

    foreach (array_keys($company) as $field) {
        unset($payload[$field]);
    }

    return array_merge($payload, [
        'socid' => $result['socid'],
    ]);
}

return [
    'title' => __('Company ID', 'forms-bridge'),
    'description' => __(
        'Gets the ID of a company and creates a new thirdparty if it doesn\'t exists',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_company_id',
    'input' => [
        'status',
        'typent_id',
        'client',
        'stcomm_id',
        'name*',
        'address',
        'zip',
        'town',
        'country_id',
    ],
    'output' => ['socid'],
];

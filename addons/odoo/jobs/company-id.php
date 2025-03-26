<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_company_id_by_vat($payload, $bridge)
{
    $response = $bridge
        ->patch([
            'name' => 'odoo-rpc-search-company-by-vat-id',
            'template' => null,
            'method' => 'search',
            'model' => 'res.partner',
        ])
        ->submit([['vat', '=', $payload['vat']], ['is_company', '=', true]]);

    if (is_wp_error($response)) {
        $company = [
            'is_company' => true,
            'name' => $payload['company_name'],
            'vat' => $payload['vat'],
        ];

        $company_fields = ['street', 'city', 'zip', 'state', 'country_code'];

        foreach ($company_fields as $field) {
            if (isset($payload[$field])) {
                $company[$field] = $payload[$field];
            }
        }

        $response = $bridge
            ->patch([
                'name' => 'odoo-rpc-create-company-contact',
                'template' => null,
                'model' => 'res.partner',
            ])
            ->submit($company);

        if (is_wp_error($response)) {
            return $response;
        }

        $company_id = $response['data']['result'];
    } else {
        $company_id = $response['data']['result'][0];
    }

    $payload['partner_id'] = $company_id;
    return $payload;
}

return [
    'title' => __('Company ID', 'forms-bridge'),
    'description' => __(
        'Search for a company type partner by vat or creates a new and sets its ID as the payload partner_id',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_company_id_by_vat',
    'input' => [
        [
            'name' => 'vat',
            'type' => 'string',
            'required' => true,
        ],
        [
            'name' => 'company_name',
            'type' => 'string',
            'required' => true,
        ],
        [
            'name' => 'street',
            'type' => 'string',
        ],
        [
            'name' => 'city',
            'type' => 'string',
        ],
        [
            'name' => 'zip',
            'type' => 'string',
        ],
        [
            'name' => 'state',
            'type' => 'string',
        ],
        [
            'name' => 'country_code',
            'type' => 'string',
        ],
    ],
    'output' => [
        [
            'name' => 'partner_id',
            'type' => 'integer',
        ],
    ],
];

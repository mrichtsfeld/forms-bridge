<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_contact_company_id($payload, $bridge)
{
    $result = forms_bridge_odoo_company_id_by_vat($payload, $bridge);
    $payload['parent_id'] = $result['partner_id'];
    return $payload;
}

return [
    'title' => __('Contact company ID', 'forms-bridge'),
    'description' => __(
        'Search for a company type partner by vat or creates a new and sets its ID as the partner parent ID',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_contact_company_id',
    'input' => [
        [
            'name' => 'vat',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'company_name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'street',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'city',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'zip',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'state',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'country_code',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'parent_id',
            'schema' => ['type' => 'integer'],
        ],
    ],
];

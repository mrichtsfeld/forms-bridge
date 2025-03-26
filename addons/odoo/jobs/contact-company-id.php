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
            'name' => 'parent_id',
            'type' => 'integer',
        ],
    ],
];

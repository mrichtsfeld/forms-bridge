<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_crm_company_contact($payload, $bridge)
{
    $payload['email_from'] = $payload['email'];

    $contact = $payload;
    $contact['parent_id'] = $contact['partner_id'];

    $result = forms_bridge_odoo_contact_id_by_email($contact, $bridge);

    if (is_wp_error($result)) {
        return $result;
    }

    return $payload;
}

return [
    'title' => __('CRM lead company contact', 'forms-bridge'),
    'description' => __(
        'Search for a partner by email and parent id or creates a new and sets its email as the email_from payload attribute',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_crm_company_contact',
    'input' => [
        [
            'name' => 'email',
            'type' => 'string',
            'required' => true,
        ],
        [
            'name' => 'contact_name',
            'type' => 'string',
            'required' => true,
        ],
        [
            'name' => 'partner_id',
            'type' => 'string',
        ],
        [
            'name' => 'phone',
            'type' => 'string',
        ],
        [
            'name' => 'function',
            'type' => 'string',
        ],
    ],
    'output' => [
        [
            'name' => 'email_from',
            'type' => 'string',
        ],
        [
            'name' => 'partner_id',
            'type' => 'string',
        ],
    ],
];

<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_crm_lead_contact($payload, $bridge)
{
    $payload['email_from'] = $payload['email'];

    $contact = $payload;

    $result = forms_bridge_odoo_contact_id_by_email($contact, $bridge);

    if (is_wp_error($result)) {
        return $result;
    }

    return $payload;
}

return [
    'title' => __('CRM lead contact', 'forms-bridge'),
    'description' => __(
        'Search for a partner by email or creates a new and sets its email as the email_from payload attribute',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_crm_lead_contact',
    'input' => [
        [
            'name' => 'email',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'contact_name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'parent_id',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'phone',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'function',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'email_from',
            'schema' => ['type' => 'string'],
        ],
    ],
];

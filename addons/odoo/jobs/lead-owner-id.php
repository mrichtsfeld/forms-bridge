<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_lead_owner_id($payload, $bridge)
{
    $query = ['email' => $payload['owner_email']];

    $user = forms_bridge_odoo_search_user_by_email($query, $bridge);

    if (is_wp_error($user)) {
        return $user;
    }

    $payload['user_id'] = $user['id'];
    return $payload;
}

return [
    'title' => __('Lead owner ID', 'forms-bridge'),
    'description' => __(
        'Search for a user by email and sets its ID as the lead owner ID',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_lead_owner_id',
    'input' => [
        [
            'name' => 'owner_email',
            'type' => 'string',
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'user_id',
            'type' => 'integer',
        ],
    ],
];

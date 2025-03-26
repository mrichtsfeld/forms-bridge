<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_lead_team_id($payload, $bridge)
{
    $response = $bridge
        ->patch([
            'name' => 'odoo-rpc-search-lead-team-owner',
            'template' => null,
            'method' => 'search',
            'model' => 'crm.team',
        ])
        ->submit([['name', '=', $payload['team']]]);

    if (is_wp_error($response)) {
        return $response;
    }

    $payload['team_id'] = $response['data']['result'][0];
    return $payload;
}

return [
    'title' => __('Team owner ID', 'forms-bridge'),
    'description' => __(
        'Search for a team by name and sets its ID as the lead owenr ID',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_lead_team_id',
    'input' => [
        [
            'name' => 'team',
            'type' => 'string',
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'team_id',
            'type' => 'integer',
        ],
    ],
];

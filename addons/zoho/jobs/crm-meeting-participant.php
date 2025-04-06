<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_crm_meeting_participant($payload, $bridge)
{
    $lead = forms_bridge_zoho_crm_create_lead($payload, $bridge);

    if (is_wp_error($lead)) {
        return $lead;
    }

    $payload['Participants'] = $payload['Participants'] ?? [];
    $payload['Participants'][] = [
        'type' => 'lead',
        'participant' => $lead['id'],
    ];

    return $payload;
}

return [
    'title' => __('CRM meeting participant', 'forms-bridge'),
    'description' => __(
        'Search for a lead or creates a new one and sets its ID as meeting participant',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_crm_meeting_participant',
    'input' => [
        [
            'name' => 'Email',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'First_Name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'Last_Name',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'Lead_Source',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Lead_Status',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Phone',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'Description',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'Participants',
            'schema' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => ['type' => 'string'],
                        'participant' => ['type' => 'string'],
                    ],
                ],
                'additionalItems' => true,
            ],
        ],
    ],
];

<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_payload',
    function ($payload, $bridge) {
        if ($bridge->template !== 'odoo-crm-team-leads') {
            return $payload;
        }

        $team = $payload['team'];

        add_filter(
            'forms_bridge_rpc_payload',
            function ($payload, $bridge) use ($team) {
                if ($bridge->template !== 'odoo-crm-team-leads') {
                    return $payload;
                }

                $data = $payload['params']['args'][5] ?? null;
                if (empty($data)) {
                    return $payload;
                }

                if (isset($data['name']) && $data['name'] === $team) {
                    $payload['params']['args'][3] = 'crm.team';
                    $payload['params']['args'][4] = 'search';
                    $payload['params']['args'][5] = [['name', '=', $team]];
                }

                return $payload;
            },
            20,
            2
        );

        $response = $bridge->submit(['name' => $payload['team']]);

        if (is_wp_error($response)) {
            do_action('forms_bridge_on_failure', $bridge, $response, $payload);

            return;
        }

        $team_id = $response['data']['result'][0];
        $payload['team_id'] = $team_id;
        unset($payload['team']);

        $contact = [
            'name' => $payload['contact_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? '',
        ];

        add_filter(
            'forms_bridge_rpc_payload',
            function ($payload, $bridge) use ($contact) {
                if ($bridge->template !== 'odoo-crm-team-leads') {
                    return $payload;
                }

                $data = $payload['params']['args'][5] ?? null;
                if (empty($data)) {
                    return $payload;
                }

                $name = $data['name'] ?? null;
                $email = $data['email'] ?? null;

                if (
                    $email === $contact['email'] &&
                    $name === $contact['name']
                ) {
                    $payload['params']['args'][3] = 'res.partner';
                }

                return $payload;
            },
            20,
            2
        );

        $response = $bridge->submit($contact);

        if (is_wp_error($response)) {
            do_action('forms_bridge_on_failure', $bridge, $response, $payload);

            return;
        }

        unset($payload['contact_name']);
        unset($payload['email']);
        unset($payload['phone']);

        $partner_id = $response['data']['result'];
        $payload['partner_id'] = $partner_id;

        return $payload;
    },
    10,
    2
);

return [
    'title' => __('CRM Team Leads', 'forms-bridge'),
    'fields' => [
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('CRM Team Leads', 'forms-bridge'),
        ],
        [
            'ref' => '#form/fields[]',
            'name' => 'team',
            'label' => __('Owner team', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#form/fields[]',
            'name' => 'name',
            'label' => __('Lead name', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
            'default' => __('Web Lead', 'forms-bridge'),
        ],
        [
            'ref' => '#form/fields[]',
            'name' => 'priority',
            'label' => __('Priority', 'forms-bridge'),
            'type' => 'number',
            'min' => 0,
            'max' => 3,
            'required' => true,
            'default' => 1,
        ],
    ],
    'bridge' => [
        'model' => 'crm.lead',
        'mappers' => [
            [
                'from' => 'priority',
                'to' => 'priority',
                'cast' => 'string',
            ],
        ],
    ],
    'form' => [
        'fields' => [
            [
                'name' => 'name',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'name' => 'team',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'name' => 'priority',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'name' => 'type',
                'type' => 'hidden',
                'value' => 'opportunity',
                'required' => true,
            ],
            [
                'label' => __('Your name', 'forms-bridge'),
                'name' => 'contact_name',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Your email', 'forms-bridge'),
                'name' => 'email',
                'type' => 'email',
                'required' => true,
            ],
            [
                'label' => __('Your phone', 'forms-bridge'),
                'name' => 'phone',
                'type' => 'text',
            ],
            [
                'label' => __('Comments', 'forms-bridge'),
                'name' => 'description',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ],
];

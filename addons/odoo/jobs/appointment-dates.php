<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_appointment_dates($payload)
{
    $time = forms_bridge_odoo_date_to_time($payload);

    if (is_wp_error($time)) {
        return $time;
    }

    $duration = $payload['duration'] ?? 1;

    $payload['start'] = date('Y-m-d H:i:s', $time);

    $end = $duration * 3600 + $time;
    $payload['stop'] = date('Y-m-d H:i:s', $end);

    return $payload;
}

return [
    'title' => __('Appointment dates', 'forms-bridge'),
    'description' => __('', 'forms-bridge'),
    'method' => 'forms_bridge_odoo_appointment_dates',
    'input' => [
        [
            'name' => 'date',
            'required' => true,
            'type' => 'string',
        ],
        [
            'name' => 'hour',
            'type' => 'string',
        ],
        [
            'name' => 'minute',
            'type' => 'string',
        ],
        [
            'name' => 'duration',
            'type' => 'number',
        ],
    ],
    'output' => [
        [
            'name' => 'start',
            'type' => 'string',
        ],
        [
            'name' => 'stop',
            'type' => 'string',
        ],
    ],
];

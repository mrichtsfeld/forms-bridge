<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_appointment_dates($payload)
{
    $payload['datep'] = (int) $payload['timestamp'];
    $payload['duration'] = floatval($payload['duration'] ?? 1);
    $payload['datef'] = intval($payload['duration'] * 3600 + $payload['datep']);

    return $payload;
}

return [
    'title' => __('Appointment dates', 'forms-bridge'),
    'description' => __(
        'Sets appointment start, end time and duration from "timestamp" and "duration" fields.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_appointment_dates',
    'input' => [
        [
            'name' => 'timestamp',
            'required' => true,
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'duration',
            'schema' => ['type' => 'number'],
        ],
    ],
    'output' => [
        [
            'name' => 'datep',
            'schema' => ['type' => 'integer'],
        ],
        [
            'name' => 'datef',
            'schema' => ['type' => 'integer'],
        ],
        [
            'name' => 'duration',
            'schema' => ['type' => 'number'],
        ],
    ],
];

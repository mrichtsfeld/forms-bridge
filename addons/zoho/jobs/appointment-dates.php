<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_zoho_appointment_dates($payload)
{
    $duration = $payload['duration'] ?? 1;

    $payload['Start_DateTime'] = date('c', $payload['timestamp']);
    $payload['End_DateTime'] = date(
        'c',
        $payload['timestamp'] + 3600 * $duration
    );

    return $payload;
}

return [
    'title' => __('Appointment dates', 'forms-bridge'),
    'description' => __(
        'Sets appointment start and end time from "timestamp" and "duration" fields.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_zoho_appointment_dates',
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
            'name' => 'Start_DateTime',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'End_DateTime',
            'schema' => ['type' => 'string'],
        ],
    ],
];

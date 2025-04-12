<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_bigin_appointment_dates($payload)
{
    $duration = $payload['duration'] ?? 1;

    $time = strtotime($payload['date']);
    $payload['Start_DateTime'] = date('c', $time);
    $payload['End_DateTime'] = date('c', $time + 3600 * $duration);

    return $payload;
}

return [
    'title' => __('Appointment dates', 'forms-bridge'),
    'description' => __(
        'Sets appointment start and end time from "date" and "duration" fields',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_bigin_appointment_dates',
    'input' => [
        [
            'name' => 'date',
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

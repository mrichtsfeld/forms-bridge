<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_appointment_dates($payload)
{
    $timestamp = strtotime($payload['date']);
    if ($timestamp === false) {
        return new WP_Error(
            'invalid-date',
            __('Invalid date time value', 'forms-bridge')
        );
    }

    $duration = floatval($payload['duration'] ?? 1);

    $payload['start'] = date('Y-m-d H:i:s', $timestamp);

    $end = $duration * 3600 + $timestamp;
    $payload['stop'] = date('Y-m-d H:i:s', $end);

    return $payload;
}

return [
    'title' => __('Appointment dates', 'forms-bridge'),
    'description' => __(
        'Sets appointment start and stop time from "timestamp" and "duration" fields.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_appointment_dates',
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
            'name' => 'start',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'stop',
            'schema' => ['type' => 'string'],
        ],
    ],
];

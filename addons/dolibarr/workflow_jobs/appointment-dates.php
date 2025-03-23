<?php

function forms_bridge_dolibarr_appointment_dates($payload, $bridge)
{
    $payload = forms_bridge_dolibarr_format_date($payload, $bridge);

    $payload['datep'] = $payload['date'];
    unset($payload['date']);

    $payload['duration'] = floatval($payload['duration'] ?? 1);

    $payload['datef'] = $payload['duration'] * 3600 + $payload['datep'];

    return $payload;
}

return [
    'title' => __('Appointment dates', 'forms-bridge'),
    'description' => __(
        'Sets appointment start and end time from "date", "hour", "minute" and "duration" fields.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_appointment_dates',
    'input' => ['date*', 'hour', 'minute', 'duration'],
    'outpiut' => ['datep', 'datef', 'duration'],
];

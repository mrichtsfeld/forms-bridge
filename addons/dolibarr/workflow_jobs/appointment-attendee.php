<?php

function forms_bridge_dolibarr_appointment_dates($payload, $bridge)
{
    $payload = forms_bridge_dolibarr_contact_by_email($payload, $bridge);

    $payload['socpeopleassigned'] = $payload['socpeopleassigned'] ?? [];
    $payload['socpeopleassigned'][$payload['contact_id']] = [
        'id' => $payload['contact_id'],
        'mandatory' => 0,
        'answer_status' => 0,
        'transparency' => 0,
    ];

    unset($payload['contact_id']);
    return $payload;
}

return [
    'title' => __('Appointment attendee', 'forms-bridge'),
    'description' => __(
        'Sets appointment start and end time from "date", "hour", "minute" and "duration" fields.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_appointment_dates',
    'input' => ['email*', 'firstname', 'lastname'],
    'output' => ['contact_id'],
];

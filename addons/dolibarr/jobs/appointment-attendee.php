<?php

function forms_bridge_dolibarr_appointment_attendee($payload, $bridge)
{
    $payload = forms_bridge_dolibarr_thirdparty_id($payload, $bridge);

    $payload['socpeopleassigned'] = $payload['socpeopleassigned'] ?? [];
    $payload['socpeopleassigned'][$payload['socid']] = [
        'id' => $payload['socid'],
        'mandatory' => 0,
        'answer_status' => 0,
        'transparency' => 0,
    ];

    unset($payload['socid']);

    unset($payload['firstname']);
    unset($payload['lastname']);
    unset($payload['email']);

    return $payload;
}

return [
    'title' => __('Appointment attendee', 'forms-bridge'),
    'description' => __(
        'Gets the thirdparty ID and sets it as and attendee.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_appointment_attendee',
    'input' => ['email*', 'firstname', 'lastname'],
    'output' => ['socpeopleassigned'],
];

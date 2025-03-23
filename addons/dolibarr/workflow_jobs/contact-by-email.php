<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_contact_by_email($payload, $bridge)
{
    $backend = $bridge->backend;
    $dolapikey = $bridge->api_key->key;

    $payload['firstname'] = $payload['firstname'] ?? '';
    $payload['lastname'] = $payload['lastname'] ?? '';

    $sqlfilters = "(t.email:=:'{$payload['email']}')";
    if (!empty($payload['firstname'])) {
        $sqlfilters .= " and (t.firstname:like:'{$payload['firstname']}')";
    }
    if (!empty($payload['lastname'])) {
        $sqlfilters .= " and (t.lastname:like:'{$payload['lastname']}')";
    }

    $response = $backend->get(
        '/api/index.php/contacts',
        [
            'limit' => '1',
            'sqlfilters' => $sqlfilters,
        ],
        ['DOLAPIKEY' => $dolapikey]
    );

    if (is_wp_error($response)) {
        $error_data = $response->get_error_data();
        $response_code = $error_data['response']['response']['code'];

        if ($response_code !== 404) {
            do_action('forms_bridge_on_failure', $bridge, $response, $payload);

            return;
        }
    }

    if (is_wp_error($response)) {
        $name = trim("{$payload['firstname']} {$payload['lastname']}");
        $response = $backend->post(
            '/api/index.php/contacts',
            [
                'name' => $name,
                'firstname' => $payload['firstname'] ?? '',
                'lastname' => $payload['lastname'] ?? '',
                'email' => $payload['email'],
            ],
            ['DOLAPIKEY' => $dolapikey]
        );

        if (is_wp_error($response)) {
            do_action('forms_bridge_on_failure', $bridge, $response, $payload);

            return;
        }

        $contact_id = $response['body'];
    } else {
        $contact_id = $response['data'][0]['id'];
    }

    $payload['contact_id'] = $contact_id;

    unset($payload['firstname']);
    unset($payload['lastname']);
    unset($payload['email']);

    return $payload;
}

return [
    'title' => __('Contact ID by email', 'forms-bridge'),
    'description' => __(
        'Search for contact ID by email. If no contacts found, then creates a new contact. Sets the ID of the contact as the "contact_id" field of the payload.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_contact_by_email',
    'input' => ['email*', 'firstname', 'lastname'],
    'output' => ['contact_id'],
];

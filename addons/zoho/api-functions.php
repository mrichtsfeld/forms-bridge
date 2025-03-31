<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_zoho_bigin_contact_id($payload, $bridge)
{
    $contact = [];
    $contact_fields = [];

    foreach ($contact_fields as $field) {
        if (isset($payload[$field])) {
            $contact[$field] = $payload[$field];
        }
    }

    $response = $bridge
        ->patch([
            'name' => 'zoho-bigin-contact-id',
            'endpoint' => '/bigin/v2/Contacts',
            'template' => null,
        ])
        ->submit($contact);

    if (is_wp_error($response)) {
        $data = json_decode(
            $response->get_error_data()['response']['body'],
            true
        );

        if ($data['data'][0]['code'] !== 'DUPLICATE_DATA') {
            return $response;
        }

        $contact_id = $data['data'][0]['details']['duplicate_record']['id'];
    } else {
        $contact_id = $response['data'][0]['details']['id'];
    }

    return $contact_id;
}

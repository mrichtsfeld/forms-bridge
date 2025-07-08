<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Country phone code', 'forms-bridge'),
    'description' => __(
        'Get a country by name and adds its phone prefix as the "countryCode" field on the payload',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_brevo_country_phone_prefix',
    'input' => [
        [
            'name' => 'country',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'country',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'countryCode',
            'schema' => ['type' => 'string'],
        ],
    ],
];

function forms_bridge_brevo_country_phone_prefix($payload)
{
    global $forms_bridge_country_phone_codes;
    global $forms_bridge_iso2_countries;
    global $forms_bridge_iso3_countries;

    $countries = [];
    foreach ($forms_bridge_country_phone_codes as $phone_code => $country) {
        $countries[$country] = $phone_code;
    }

    $country = $payload['country'];

    if (isset($forms_bridge_iso2_countries[$country])) {
        $country = $forms_bridge_iso2_countries[$country];
    } elseif (isset($forms_bridge_iso3_countries)) {
        $country = $forms_bridge_iso3_countries[$country];
    }

    if (isset($countries[$country])) {
        $payload['countryCode'] = $countries[$country];
    } else {
        $payload['countryCode'] = null;
    }

    return $payload;
}

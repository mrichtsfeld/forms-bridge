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

    $countries = array_reduce(
        array_keys($forms_bridge_country_phone_codes),
        function ($countries, $phone_code) {
            global $forms_bridge_country_phone_codes;
            $name = $forms_bridge_country_phone_codes[$phone_code];
            $countries[$name] = $phone_code;
            return $countries;
        },
        []
    );

    $country = $payload['country'];
    if (isset($countries[$country])) {
        $payload['countryCode'] = $countries[$country];
    }

    return $payload;
}

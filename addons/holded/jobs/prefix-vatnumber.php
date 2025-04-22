<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_holded_prefix_vatnumber($payload)
{
    global $forms_bridge_iso2_countries;

    $prefixed = preg_match(
        '/^[A-Z]{2}/',
        strtoupper($payload['vatnumber']),
        $matches
    );

    $country_code = strtoupper($payload['countryCode'] ?? '');

    if ($prefixed) {
        $vat_prefix = $matches[0];
    } elseif ($country_code) {
        $vat_prefix = $country_code;
    } else {
        $vat_prefix = strtoupper(explode('_', get_locale())[0]);
    }

    if (!isset($forms_bridge_iso2_countries[$vat_prefix])) {
        if (
            !$country_code ||
            !isset($forms_bridge_iso2_countries[$country_code])
        ) {
            return new WP_Error(
                'invalid_country_code',
                __('The vatnumber prefix is invalid', 'forms-bridge')
            );
        }

        $prefixed = false;
        $vat_prefix = $country_code;
    }

    if (!$prefixed) {
        $payload['vatnumber'] = $vat_prefix . $payload['vatnumber'];
    }

    return $payload;
}

return [
    'title' => __('Vatnumber prefix', 'forms-bridge'),
    'description' => __(
        'Prefix the vat with country code, or the current locale, if it isn\'t prefixed',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_holded_prefix_vatnumber',
    'input' => [
        [
            'name' => 'vatnumber',
            'schema' => ['type' => 'string'],
            'required' => true,
        ],
        [
            'name' => 'countryCode',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'vatnumber',
            'schema' => ['type' => 'string'],
            'touch' => true,
        ],
        [
            'name' => 'countryCode',
            'schema' => ['type' => 'string'],
            'forward' => true,
        ],
    ],
];

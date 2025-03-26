<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_country_code($payload)
{
    global $forms_bridge_odoo_countries;
    $country_code = strtoupper($payload['country']);

    if (!isset($forms_bridge_odoo_countries[$country_code])) {
        $countries_by_label = array_reduce(
            array_keys($forms_bridge_odoo_countries),
            function ($labels, $country_code) {
                global $forms_bridge_odoo_countries;
                $label = $forms_bridge_odoo_countries[$country_code];
                $labels[$label] = $country_code;
                return $labels;
            },
            []
        );

        $payload['country_code'] = $countries_by_label[$payload['country']];
    } else {
        $payload['country_code'] = $country_code;
    }

    return $payload;
}

return [
    'title' => __('Odoo country code', 'forms-bridge'),
    'description' => __(
        'Gets the ISO2 country code from country names and replace its value',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_country_code',
    'input' => [
        [
            'name' => 'country',
            'type' => 'string',
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'country_code',
            'type' => 'string',
            'required' => true,
        ],
    ],
];

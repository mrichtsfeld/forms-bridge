<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_dolibarr_country_id($payload)
{
    global $forms_bridge_dolibarr_countries;
    if (!isset($forms_bridge_dolibarr_countries[$payload['country_id']])) {
        $countries_by_label = array_reduce(
            array_keys($forms_bridge_dolibarr_countries),
            function ($countries, $country_id) {
                global $forms_bridge_dolibarr_countries;
                $label = $forms_bridge_dolibarr_countries[$country_id];
                $countries[$label] = $country_id;
                return $countries;
            },
            []
        );

        if (isset($countries_by_label[$payload['country_id']])) {
            $payload['country_id'] =
                $countries_by_label[$payload['country_id']];
        } else {
            return forms_bridge_dolibarr_iso2_country_id(
                $payload,
                $countries_by_label
            );
        }
    }

    return $payload;
}

function forms_bridge_dolibarr_iso2_country_id($payload, $country_ids)
{
    global $forms_bridge_iso2_countries;

    $countries_by_label = array_reduce(
        array_keys($forms_bridge_iso2_countries),
        function ($countries, $country_code) use ($country_ids) {
            global $forms_bridge_iso2_countries;
            $country = $forms_bridge_iso2_countries[$country_code];

            if (isset($country_ids[$country])) {
                $countries[$country_code] = $country_ids[$country];
            }

            return $countries;
        },
        []
    );

    if (!isset($forms_bridge_iso2_countries[$payload['country_id']])) {
        if (isset($countries_by_label[$payload['country_id']])) {
            $payload['country_id'] =
                $countries_by_label[$payload['country_id']];
        } else {
            return new WP_Error('Unkown country', 'forms-bridge');
        }
    } else {
        $payload['country_id'] =
            $country_ids[$forms_bridge_iso2_countries[$payload['country_id']]];
    }

    return $payload;
}

return [
    'title' => __('Country ID', 'forms-bridge'),
    'description' => __(
        'Check if country id is valid or replace its value if a country name or country iso2 code is passed',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_country_id',
    'input' => [
        [
            'name' => 'country_id',
            'schema' => ['type' => 'integer'],
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'country_id',
            'schema' => ['type' => 'integer'],
            'touch' => true,
        ],
    ],
];

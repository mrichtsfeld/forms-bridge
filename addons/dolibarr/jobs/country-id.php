<?php

function forms_bridge_dolibarr_country_id($payload)
{
    if (!isset($payload['country_id'])) {
        return $payload;
    }

    global $forms_bridge_dolibarr_counties;

    if (!isset($forms_bridge_dolibarr_counties[$payload['country_id']])) {
        $countries_by_label = array_reduce(
            array_keys($forms_bridge_dolibarr_counties),
            function ($countries, $country_id) {
                global $forms_bridge_dolibarr_counties;
                $label = $forms_bridge_dolibarr_counties[$country_id];
                $countries[$label] = $country_id;
                return $countries;
            },
            []
        );

        $payload['country_id'] = $countries_by_label[$payload['country_id']];
    }

    return $payload;
}

return [
    'title' => __('Country ID', 'forms-bridge'),
    'description' => __('Ensure country id is a valid value', 'forms-bridge'),
    'method' => 'forms_bridge_dolibarr_country_id',
    'input' => ['country_id*'],
    'output' => ['country_id'],
];

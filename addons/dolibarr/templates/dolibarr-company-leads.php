<?php

if (!defined('ABSPATH')) {
    exit();
}

// global $forms_bridge_dolibarr_states;
// global $forms_bridge_dolibarr_countries;

$forms_bridge_dolibarr_company_contact_data = null;

add_filter(
    'forms_bridge_payload',
    function ($payload, $bridge) {
        if ($bridge->template !== 'dolibarr-company-leads') {
            return $payload;
        }

        $backend = $bridge->backend;
        $response = $backend->get('/api/index.php/thirdparties', [
            'sortfield' => 't.rowid',
            'sortorder' => 'DESC',
            'limit' => 1,
        ]);

        if (is_wp_error($response)) {
            do_action('forms_bridge_on_failure', $bridge, $response, $payload);

            return;
        }

        $previus_code_client = $response['data'][0]['code_client'];
        [$prefix, $number] = explode('-', $previus_code_client);

        $next = strval($number + 1);
        while (strlen($next) < strlen($number)) {
            $next = '0' . $next;
        }

        $payload['code_client'] = $prefix . '-' . $next;

        if (empty($payload['stcomm_id'])) {
            $payload['stcomm_id'] = '0';
        }

        $contact = [
            'firstname' => $payload['firstname'],
            'lastname' => $payload['lastname'],
            'email' => $payload['email'],
            'poste' => $payload['poste'],
        ];

        unset($payload['firstname']);
        unset($payload['lastname']);
        unset($payload['poste']);

        global $forms_bridge_dolibarr_company_contact_data;
        $forms_bridge_dolibarr_company_contact_data = $contact;

        // $reversed_states = [];
        // global $forms_bridge_dolibarr_states;
        // foreach ($forms_bridge_dolibarr_states as $code => $label) {
        //     $reversed_states[$label] = $code;
        // }

        // if (isset($reversed_states[$payload['state_id']])) {
        //     $payload['state_id'] = $reversed_states[$payload['state_id']];
        // }

        // $reversed_countries = [];
        // global $forms_bridge_dolibarr_countries;
        // foreach ($forms_bridge_dolibarr_countries as $code => $label) {
        //     $reversed_countries[$label] = $code;
        // }

        // if (isset($reversed_countries[$payload['country_id']])) {
        //     $payload['country_id'] = $reversed_countries[$payload['country_id']];
        // }

        return $payload;
    },
    9,
    2
);

add_action(
    'forms_bridge_submit',
    function ($bridge, $response) {
        if ($bridge->template !== 'dolibarr-company-leads') {
            return;
        }

        $company_id = $response['body'];

        global $forms_bridge_dolibarr_company_contact_data;
        $payload = $forms_bridge_dolibarr_company_contact_data;

        $payload['socid'] = $company_id;

        $backend = $bridge->backend;
        $response = $backend->post('/api/index.php/contacts', $payload);

        if (is_wp_error($response)) {
            do_action('forms_bridge_on_failure', $bridge, $response, $payload);
        }
    },
    10,
    2
);

return [
    'title' => __('Dolibarr Company Leads', 'forms-bridge'),
    'fields' => [
        [
            'ref' => '#bridge',
            'name' => 'endpoint',
            'label' => __('Endpoint', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
            'value' => '/api/index.php/thirdparties',
        ],
        [
            'ref' => '#form/fields[]',
            'name' => 'stcomm_id',
            'label' => __('Lead status', 'forms-bridge'),
            'required' => true,
            'type' => 'options',
            'options' => [
                [
                    'label' => __('Never contacted', 'forms-bridge'),
                    'value' => '0',
                ],
                [
                    'label' => __('To contact', 'forms-bridge'),
                    'value' => '1',
                ],
                [
                    'label' => __('Contact in progress', 'forms-bridge'),
                    'value' => '2',
                ],
                [
                    'label' => __('Contacted', 'forms-bridge'),
                    'value' => '3',
                ],
                [
                    'label' => __('Do not contact', 'forms-bridge'),
                    'value' => '-1',
                ],
            ],
            'default' => '0',
        ],
    ],
    'form' => [
        'title' => __('Dolibarr Leads', 'forms-bridge'),
        'fields' => [
            [
                'name' => 'status',
                'value' => '1',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'name' => 'client',
                'value' => '2',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'name' => 'stcomm_id',
                'type' => 'hidden',
                'required' => true,
            ],
            [
                'name' => 'firstname',
                'label' => __('First name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'lastname',
                'label' => __('Last name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'email',
                'label' => __('Email', 'forms-bridge'),
                'type' => 'email',
                'required' => true,
            ],
            [
                'name' => 'name',
                'label' => __('Company', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'poste',
                'label' => __('Job position', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'address',
                'label' => __('Address', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'zip',
                'label' => __('Postal code', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'town',
                'label' => __('City', 'forms-bridge'),
                'type' => 'text',
            ],
            // [
            //     'name' => 'state_id',
            //     'label' => __('State', 'forms-bridge'),
            //     'type' => 'options',
            //     'options' => $forms_bridge_dolibarr_states,
            // ],
            // [
            //     'name' => 'country_id',
            //     'label' => __('Country', 'forms-bridge'),
            //     'type' => 'options',
            //     'options' => $forms_bridge_dolibarr_countries,
            // ]
        ],
    ],
    'backend' => [
        'headers' => [
            'name' => 'Accept',
            'value' => 'application/json',
        ],
    ],
    'bridge' => [
        'endpoint' => '/api/index.php/thirdparties',
        'method' => 'POST',
        'mappers' => [
            [
                'from' => 'status',
                'to' => 'status',
                'cast' => 'string',
            ],
            [
                'from' => 'client',
                'to' => 'client',
                'cast' => 'string',
            ],
            [
                'from' => 'stcomm_id',
                'to' => 'stcomm_id',
                'cast' => 'string',
            ],
        ],
    ],
];

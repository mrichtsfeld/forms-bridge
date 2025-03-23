<?php

if (!defined('ABSPATH')) {
    exit();
}

global $forms_bridge_odoo_countries;

add_filter(
    'forms_bridge_payload',
    function ($payload, $bridge) {
        if ($bridge->template !== 'odoo-company-contacts') {
            return $payload;
        }

        global $forms_bridge_odoo_countries;
        $company_fields = [
            'company_name',
            'vat',
            'street',
            'city',
            'zip',
            'state',
            'country_code',
        ];

        if (isset($payload['country_code'])) {
            if (
                !isset($forms_bridge_odoo_countries[$payload['country_code']])
            ) {
                $countries_by_label = array_reduce(
                    array_keys($forms_bridge_odoo_countries),
                    function ($countries, $country_code) {
                        global $forms_bridge_odoo_countries;
                        $label = $forms_bridge_odoo_countries[$country_code];
                        $countries[$label] = $country_code;
                        return $countries;
                    },
                    []
                );

                $payload['country_code'] =
                    $countries_by_label[$payload['country_code']];
            }
        }

        $vat_locale = strtoupper(substr($payload['vat'], 0, 2));

        if (!isset($forms_bridge_odoo_countries[$vat_locale])) {
            $country_code =
                $payload['country_code'] ??
                strtoupper(explode('_', get_locale())[0]);
            $payload['vat'] = $country_code . $payload['vat'];
        }

        $response = $bridge
            ->patch([
                'name' => 'odoo-rpc-search-company-by-vat-id',
                'template' => null,
                'method' => 'search',
            ])
            ->submit([
                ['vat', '=', $payload['vat']],
                ['is_company', '=', true],
            ]);

        if (is_wp_error($response)) {
            $company = [
                'is_company' => true,
            ];

            foreach ($company_fields as $field) {
                if (isset($payload[$field])) {
                    $value = $payload[$field];
                    $field = preg_replace('/^company_/', '', $field);
                    $company[$field] = $value;
                }
            }

            $response = $bridge
                ->patch([
                    'name' => 'odoo-rpc-create-company-contact',
                    'template' => null,
                ])
                ->submit($company);

            if (is_wp_error($response)) {
                do_action(
                    'forms_bridge_on_failure',
                    $bridge,
                    $response,
                    $payload
                );

                return;
            }

            $company_id = $response['data']['result'];
        } else {
            $company_id = $response['data']['result'][0];

            $response = $bridge
                ->patch([
                    'name' => 'odoo-rpc-search-company-contact-by-email',
                    'template' => null,
                    'method' => 'search',
                ])
                ->submit([
                    ['email', '=', $payload['email']],
                    ['parent_id', '=', $company_id],
                ]);

            if (!is_wp_error($response)) {
                return;
            }
        }

        $payload['parent_id'] = $company_id;

        foreach (array_keys($company) as $field) {
            unset($payload[$field]);
        }

        return $payload;
    },
    90,
    2
);

return [
    'title' => __('Company Contacts', 'forms-bridge'),
    'fields' => [
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('Company Contacts', 'forms-bridge'),
        ],
    ],
    'bridge' => [
        'model' => 'res.partner',
    ],
    'form' => [
        'fields' => [
            [
                'label' => __('Company name', 'forms-bridge'),
                'name' => 'company_name',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Tax ID'),
                'name' => 'vat',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Address', 'forms-bridge'),
                'name' => 'street',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('City', 'forms-bridge'),
                'name' => 'city',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Zip code', 'forms-bridge'),
                'name' => 'zip',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Country', 'forms-bridge'),
                'name' => 'country_code',
                'type' => 'options',
                'options' => array_map(function ($country_code) {
                    global $forms_bridge_odoo_countries;
                    return [
                        'value' => $country_code,
                        'label' => $forms_bridge_odoo_countries[$country_code],
                    ];
                }, array_keys($forms_bridge_odoo_countries)),
                'required' => true,
            ],
            [
                'label' => __('Your name', 'forms-bridge'),
                'name' => 'name',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Job position', 'forms-bridge'),
                'name' => 'function',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Your email', 'forms-bridge'),
                'name' => 'email',
                'type' => 'email',
                'required' => true,
            ],
            [
                'label' => __('Your phone', 'forms-bridge'),
                'name' => 'phone',
                'type' => 'text',
            ],
        ],
    ],
];

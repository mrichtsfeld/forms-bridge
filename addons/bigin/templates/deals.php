<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_template_data',
    function ($data, $template_name) {
        if ($template_name === 'bigin-deals') {
            $index = array_search(
                'Tag',
                array_column($data['bridge']['custom_fields'], 'name')
            );

            if ($index !== false) {
                $field = &$data['bridge']['custom_fields'][$index];

                $tags = array_filter(
                    array_map('trim', explode(',', strval($field['value'])))
                );
                for ($i = 0; $i < count($tags); $i++) {
                    $data['bridge']['custom_fields'][] = [
                        'name' => "Tag[{$i}].name",
                        'value' => $tags[$i],
                    ];
                }

                array_splice($data['bridge']['custom_fields'], $index, 1);
                $data['bridge']['custom_fields'] = array_values(
                    $data['bridge']['custom_fields']
                );
            }
        }

        return $data;
    },
    10,
    2
);

return [
    'title' => __('Deals', 'forms-bridge'),
    'description' => __(
        'Creates new deals on your Bigin pipelines',
        'forms-bridge'
    ),
    'fields' => [
        [
            'ref' => '#backend',
            'name' => 'name',
            'default' => 'Zoho API',
        ],
        [
            'ref' => '#credential',
            'name' => 'organization_id',
            'label' => __('Organization ID', 'form-bridge'),
            'description' => __(
                'From your organization dashboard, expand the profile sidebar and click on the copy user ID icon to get your organization ID.',
                'forms-bridge'
            ),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#credential',
            'name' => 'client_id',
            'description' => __(
                'You have to create a Self-Client Application on the Zoho Developer Console and get the Client ID',
                'forms-bridge'
            ),
        ],
        [
            'ref' => '#credential',
            'name' => 'client_secret',
            'label' => __('Client Secret', 'forms-bridge'),
            'description' => __(
                'You have to create a Self-Client Application on the Zoho Developer Console and get the Client Secret',
                'forms-bridge'
            ),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge',
            'name' => 'endpoint',
            'label' => __('Endpoint', 'forms-bridge'),
            'type' => 'string',
            'value' => '/bigin/v2/Pipelines',
        ],
        [
            'ref' => '#bridge',
            'name' => 'scope',
            'label' => __('Scope', 'forms-bridge'),
            'type' => 'string',
            'value' =>
                'ZohoBigin.modules.contacts.CREATE,ZohoBigin.modules.accounts.CREATE,ZohoBigin.modules.pipelines.CREATE',
        ],
        [
            'ref' => '#form',
            'name' => 'title',
            'default' => __('Deals', 'forms-bridge'),
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'Owner.id',
            'label' => __('Owner ID', 'forms-bridge'),
            'descritpion' => __(
                'ID of the owner user of the deal',
                'forms-bridge'
            ),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'Deal_Name',
            'label' => __('Deal name', 'forms-bridge'),
            'description' => __('Name of the pipeline deals', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'Stage',
            'label' => __('Deal stage', 'forms-bridge'),
            'type' => 'options',
            'options' => [
                [
                    'value' => 'Qualification',
                    'label' => __('Qualification', 'forms-bridge'),
                ],
                [
                    'value' => 'Needs Analysis',
                    'label' => __('Needs Analysis', 'forms-bridge'),
                ],
                [
                    'value' => 'Proposal/Price Quote',
                    'label' => __('Proposal/Price Quote', 'forms-bridge'),
                ],
                [
                    'value' => 'Negotation/Review',
                    'label' => __('Negotiation/Review', 'forms-bridge'),
                ],
                [
                    'value' => 'Closed Won',
                    'label' => __('Closed Won', 'forms-bridge'),
                ],
                [
                    'value' => 'Closed Lost',
                    'label' => __('Closed Lost', 'forms-bridge'),
                ],
            ],
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'Sub_Pipeline',
            'label' => __('Pipeline name', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'Amount',
            'label' => __('Deal amount', 'forms-bridge'),
            'type' => 'number',
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'Tag',
            'label' => __('Deal tags', 'forms-bridge'),
            'description' => __(
                'Tag names separated by commas',
                'forms-bridge'
            ),
            'type' => 'string',
        ],
    ],
    'form' => [
        'fields' => [
            [
                'name' => 'Account_Name',
                'label' => __('Company name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'Billing_Street',
                'label' => __('Street', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'Billing_Code',
                'label' => __('Postal code', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'Billing_City',
                'label' => __('City', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'Billing_State',
                'label' => __('State', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'Billing_Country',
                'label' => __('Country', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'First_Name',
                'label' => __('First name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'Last_Name',
                'label' => __('Last name', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'Title',
                'label' => __('Title', 'forms-bridge'),
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'Email',
                'label' => __('Email', 'forms-bridge'),
                'type' => 'email',
                'required' => true,
            ],
            [
                'name' => 'Phone',
                'label' => __('Phone', 'forms-bridge'),
                'type' => 'text',
            ],
            [
                'name' => 'Description',
                'label' => __('Comments', 'forms-bridge'),
                'type' => 'textarea',
            ],
        ],
    ],
    'backend' => [
        'base_url' => 'https://www.zohoapis.com',
        'headers' => [
            [
                'name' => 'Accept',
                'value' => 'application/json',
            ],
        ],
    ],
    'bridge' => [
        'endpoint' => '/bigin/v2/Pipelines',
        'scope' =>
            'ZohoBigin.modules.contacts.CREATE,ZohoBigin.modules.accounts.CREATE,ZohoBigin.modules.pipelines.CREATE',
        'workflow' => ['bigin-account-name', 'bigin-contact-name'],
        'mutations' => [
            [
                [
                    'from' => 'Amount',
                    'to' => 'Amount',
                    'cast' => 'number',
                ],
            ],
        ],
    ],
];

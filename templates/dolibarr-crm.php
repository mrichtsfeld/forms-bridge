<?php

add_filter('forms_bridge_payload', function ($payload, $hook) {
    if ($hook->template === 'dolibarr-crm') {
        // do something
    }

    return $payload;
});

return [
    'title' => __('Dolibarr CRM', 'forms-bridge'),
    'fields' => [
        [
            'name' => 'campaign',
            'ref' => '#form/fields[]',
            'label' => __('Campaign', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
        ],
        [
            'name' => 'endpoint',
            'ref' => '#hook',
            'label' => __('Endpoint', 'forms-bridge'),
            'type' => 'string',
            'required' => true,
            'default' => '/api/index.php/products',
        ],
    ],
    'form' => [
        'title' => __('Dolibarr CRM Leads', 'forms-bridge'),
        'fields' => [
            [
                'label' => __('Email', 'forms-bridge'),
                'name' => 'email_from',
                'type' => 'text',
                'required' => true,
            ],
            [
                'label' => __('Message', 'forms-bridge'),
                'name' => 'message',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ],
];

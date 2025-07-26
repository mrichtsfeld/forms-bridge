<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Skip submission', 'forms-bridge'),
    'description' => __(
        'Skip submission if condition is not truthy',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_job_skip_if_not_condition',
    'input' => [
        [
            'name' => 'condition',
            'schema' => ['type' => 'boolean'],
            'required' => true,
        ],
    ],
    'output' => [],
];

function forms_bridge_job_skip_if_not_condition($payload)
{
    if (empty($payload['condition'])) {
        return;
    }

    return $payload;
}

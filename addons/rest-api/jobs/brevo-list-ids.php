<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_brevo_list_ids($payload)
{
    foreach (['listIds', 'includeListIds'] as $field) {
        if (!isset($payload[$field])) {
            continue;
        }

        $list = is_string($payload[$field])
            ? explode(',', $payload[$field])
            : (array) $payload[$field];

        $payload[$field] = array_filter(array_map('intval', $list));
    }

    return $payload;
}

return [
    'title' => __('Brevo list IDs', 'forms-bridge'),
    'description' => __(
        'Formats the submission payload listIds field as an array of integers.',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_brevo_list_ids',
    'input' => [
        [
            'name' => 'listIds',
            'schema' => ['type' => 'string'],
        ],
        [
            'name' => 'includeListIds',
            'schema' => ['type' => 'string'],
        ],
    ],
    'output' => [
        [
            'name' => 'listIds',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'additionalItems' => true,
            ],
            'touch' => true,
        ],
        [
            'name' => 'includeListIds',
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'integer'],
                'additionalItems' => true,
            ],
            'touch' => true,
        ],
    ],
];

<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Validate order', 'forms-bridge'),
    'description' => __(
        'Add a callback to the bridge submission to validate the order after its creation',
        'forms-bridge'
    ),
    'callbacks' => [
        'after' => 'forms_bridge_dolibarr_validate_order',
    ],
    'input' => [],
    'output' => [],
];

function forms_bridge_dolibarr_validate_order(
    $bridge,
    $response,
    $payload,
    $attachments
) {
    $order_id = intval($response['data'] ?? null);

    if (empty($order_id)) {
        return;
    }

    $response = $bridge
        ->patch([
            'name' => 'dolibarr-validate-order',
            'method' => 'POST',
            'endpoint' => "/api/index.php/orders/{$order_id}/validate",
        ])
        ->submit();

    if (is_wp_error($response)) {
        do_action(
            'forms_bridge_on_failure',
            $response,
            $bridge,
            $payload,
            $attachments
        );
    }
}

<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Sync woo products', 'forms-bridge'),
    'description' => __(
        'Synchronize WooCommerce orders products with the eCommerce module of Brevo',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_brevo_sync_woo_products',
    'input' => [
        [
            'name' => 'line_items',
            'schema' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer'],
                        'product' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'parent_id' => ['type' => 'integer'],
                                'sku' => ['type' => 'string'],
                                'name' => ['type' => 'string'],
                                'slug' => ['type' => 'string'],
                                'price' => ['type' => 'number'],
                                'sale_price' => ['type' => 'number'],
                                'regular_price' => ['type' => 'number'],
                                'stock_quantity' => ['type' => 'number'],
                                'stock_status' => ['type' => 'string'],
                            ],
                            'required' => ['id', 'name', 'price'],
                        ],
                    ],
                    'additionalProperties' => true,
                ],
                'additionalItems' => true,
            ],
            'required' => true,
        ],
    ],
    'output' => [
        [
            'name' => 'line_items',
            'schema' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer'],
                        'product' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'parent_id' => ['type' => 'integer'],
                                'sku' => ['type' => 'string'],
                                'name' => ['type' => 'string'],
                                'slug' => ['type' => 'string'],
                                'price' => ['type' => 'number'],
                                'sale_price' => ['type' => 'number'],
                                'regular_price' => ['type' => 'number'],
                                'stock_quantity' => ['type' => 'number'],
                                'stock_status' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'additionalProperties' => true,
                ],
                'additionalItems' => true,
            ],
        ],
    ],
];

function forms_bridge_brevo_sync_woo_products($payload, $bridge)
{
    $response = $bridge
        ->patch([
            'name' => 'brevo-search-products',
            'endpoint' => '/v3/products',
            'method' => 'GET',
        ])
        ->submit([
            'offset' => 0,
            'order' => 'desc',
        ]);

    if (is_wp_error($response)) {
        return $response;
    }

    foreach ($payload['line_items'] as $line_item) {
        $product = null;
        foreach ($response['data']['products'] as $candidate) {
            if ($candidate['id'] == $line_item['product_id']) {
                $product = $candidate;
                break;
            }
        }

        if (!$product) {
            $product = [
                'updateEnabled' => false,
                'id' => (string) $line_item['product_id'],
                'name' => $line_item['product']['name'],
                'price' => $line_item['product']['price'],
                'stock' => $line_item['product']['stock_quantity'],
            ];

            if (!empty($line_item['product']['parent_id'])) {
                $product['parent_id'] = $line_item['product']['parent_id'];
            }

            if (!empty($line_item['product']['sku'])) {
                $product['sku'] = $line_item['product']['sku'];
            }

            $product_response = $bridge
                ->patch([
                    'name' => 'brevo-sync-woo-product',
                    'method' => 'POST',
                    'endpoint' => '/v3/products',
                ])
                ->submit($product);

            if (is_wp_error($product_response)) {
                return $product_response;
            }
        }
    }

    return $payload;
}

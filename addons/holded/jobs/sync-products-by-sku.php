<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Sync woo products', 'forms-bridge'),
    'description' => __(
        'Search for products from the WooCommerce order by sku on Holded and creates new ones if someone does not exists',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_holded_sync_products_by_sku',
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
                            'required' => ['sku', 'name', 'price'],
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

function forms_bridge_holded_sync_products_by_sku($payload, $bridge)
{
    $product_refs = [];
    foreach ($payload['line_items'] as $line_item) {
        if (empty($line_item['product']['sku'])) {
            return new WP_Error(
                "SKU is required on product {$line_item['product']['name']}"
            );
        }

        $product_refs[] = $line_item['product']['sku'];
    }

    $response = $bridge
        ->patch([
            'name' => 'holded-search-products',
            'endpoint' => '/api/invoicing/v1/products',
            'method' => 'GET',
        ])
        ->submit();

    if (is_wp_error($response)) {
        return $response;
    }

    foreach ($payload['line_items'] as $line_item) {
        $product = null;
        foreach ($response['data'] as $candidate) {
            if ($candidate['sku'] === $line_item['product']['sku']) {
                $product = $candidate;
                break;
            }
        }

        if (!$product) {
            $product_response = $bridge
                ->patch([
                    'name' => 'holded-sync-product-by-sku',
                    'endpoint' => '/api/invoicing/v1/products',
                    'method' => 'POST',
                ])
                ->submit([
                    'kind' => 'simple',
                    'name' => $line_item['product']['name'],
                    'price' => $line_item['product']['price'],
                    'sku' => $line_item['product']['sku'],
                ]);

            if (is_wp_error($product_response)) {
                return $product_response;
            }
        }
    }
    return $payload;
}

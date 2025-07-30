<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Sync woo products', 'forms-bridge'),
    'description' => __(
        'Search for products from the WooCommerce order by sku on Odoo and creates new ones if someone does not exists',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_odoo_sync_products_by_ref',
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

function forms_bridge_odoo_sync_products_by_ref($payload, $bridge)
{
    $internal_refs = [];
    foreach ($payload['line_items'] as $line_item) {
        if (empty($line_item['product']['sku'])) {
            return new WP_Error(
                "SKU is required on product {$line_item['product']['name']}"
            );
        }

        $internal_refs[] = $line_item['product']['sku'];
    }

    $response = $bridge
        ->patch([
            'name' => 'odoo-search-products-by-ref',
            'endpoint' => 'product.product',
            'method' => 'search_read',
        ])
        ->submit(
            [['default_code', 'in', $internal_refs]],
            ['id', 'default_code']
        );

    if (is_wp_error($response)) {
        if ($response->get_error_code() !== 'not_found') {
            return $response;
        }

        $response = $response->get_error_data()['response'];
        $response['data']['result'] = [];
    }

    foreach ($payload['line_items'] as $line_item) {
        $product = null;
        foreach ($response['data']['result'] as $candidate) {
            if ($candidate['default_code'] === $line_item['product']['sku']) {
                $product = $candidate;
                break;
            }
        }

        if (!$product) {
            $product_response = $bridge
                ->patch([
                    'name' => 'odoo-sync-product-by-ref',
                    'endpoint' => 'product.product',
                    'method' => 'create',
                ])
                ->submit([
                    'name' => $line_item['product']['name'],
                    'list_price' => $line_item['product']['price'],
                    'default_code' => $line_item['product']['sku'],
                    'sale_ok' => true,
                    'purchase_ok' => false,
                ]);

            if (is_wp_error($product_response)) {
                return $product_response;
            }
        }
    }

    return $payload;
}

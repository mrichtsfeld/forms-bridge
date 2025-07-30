<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Sync woo products', 'forms-bridge'),
    'description' => __(
        'Search for products from the WooCommerce order by sku on Dolibarr and creates new ones if someone does not exists',
        'forms-bridge'
    ),
    'method' => 'forms_bridge_dolibarr_sync_products_by_ref',
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

function forms_bridge_dolibarr_sync_products_by_ref($payload, $bridge)
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

    $sqlfilters = [];
    foreach ($product_refs as $ref) {
        $ref = trim($ref);
        $sqlfilters[] = "(t.ref:=:'{$ref}')";
    }

    $response = $bridge
        ->patch([
            'name' => 'dolibarr-search-products-by-ref',
            'endpoint' => '/api/index.php/products',
            'method' => 'GET',
        ])
        ->submit([
            'properties' => 'id,ref',
            'sqlfilters' => implode(' or ', $sqlfilters),
        ]);

    if (is_wp_error($response)) {
        return $response;
    }

    foreach ($payload['line_items'] as $line_item) {
        $product = null;
        foreach ($response['data'] as $candidate) {
            if ($candidate['ref'] === $line_item['product']['sku']) {
                $product = $candidate;
                break;
            }
        }

        if (!$product) {
            $product_response = $bridge
                ->patch([
                    'name' => 'dolibarr-sync-product-by-ref',
                    'endpoint' => '/api/index.php/products',
                    'method' => 'POST',
                ])
                ->submit([
                    'label' => $line_item['product']['name'],
                    'ref' => $line_item['product']['sku'],
                    'status' => '1',
                    'type' => '0',
                    'price' => $line_item['product']['price'],
                ]);

            if (is_wp_error($product_response)) {
                return $product_response;
            }
        }
    }

    return $payload;
}

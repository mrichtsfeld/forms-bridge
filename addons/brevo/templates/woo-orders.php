<?php

if (!defined('ABSPATH')) {
    exit();
}

return [
    'title' => __('Woo Orders', 'forms-bridge'),
    'description' => __(
        'Sale order bridge template. The resulting bridge will synchronize WooCommerce with the Brevo eCommerce module.',
        'forms-bridge'
    ),
    'integrations' => ['woo'],
    'fields' => [
        [
            'ref' => '#form',
            'name' => 'title',
            'value' => __('Woo Checkout', 'forms-bridge'),
        ],
        [
            'ref' => '#bridge',
            'name' => 'endpoint',
            'value' => '/v3/orders/status',
        ],
        [
            'ref' => '#bridge/custom_fields[]',
            'name' => 'listIds',
            'label' => __('Segments', 'forms-bridge'),
            'type' => 'select',
            'options' => [
                'endpoint' => '/v3/contacts/lists',
                'finger' => [
                    'value' => 'lists[].id',
                    'label' => 'lists[].name',
                ],
            ],
            'is_multi' => true,
            'required' => true,
        ],
    ],
    'bridge' => [
        'method' => 'POST',
        'endpoint' => '/v3/orders/status',
        'custom_fields' => [
            [
                'name' => 'attributes.LANGUAGE',
                'value' => '$locale',
            ],
            [
                'name' => 'createdAt',
                'value' => '$utc_date',
            ],
            [
                'name' => 'updatedAt',
                'value' => '$utc_date',
            ],
        ],
        'mutations' => [
            [
                [
                    'from' => 'id',
                    'to' => 'order_id',
                    'cast' => 'string',
                ],
                [
                    'from' => 'parent_id',
                    'to' => 'parent_id',
                    'cast' => 'null',
                ],
                [
                    'from' => 'version',
                    'to' => 'version',
                    'cast' => 'null',
                ],
                [
                    'from' => 'prices_include_tax',
                    'to' => 'prices_include_tax',
                    'cast' => 'null',
                ],
                [
                    'from' => 'discount_total',
                    'to' => 'discount_total',
                    'cast' => 'null',
                ],
                [
                    'from' => 'discount_tax',
                    'to' => 'discount_tax',
                    'cast' => 'null',
                ],
                [
                    'from' => 'shipping_total',
                    'to' => 'shipping_total',
                    'cast' => 'null',
                ],
                [
                    'from' => 'shipping_tax',
                    'to' => 'shipping_tax',
                    'cast' => 'null',
                ],
                [
                    'from' => 'cart_total',
                    'to' => 'cart_total',
                    'cast' => 'null',
                ],
                [
                    'from' => 'cart_tax',
                    'to' => 'cart_tax',
                    'cast' => 'null',
                ],
                [
                    'from' => 'total',
                    'to' => 'amount',
                    'cast' => 'number',
                ],
                [
                    'from' => 'total_tax',
                    'to' => 'total_tax',
                    'cast' => 'null',
                ],
                [
                    'from' => 'customer_id',
                    'to' => 'ext_id',
                    'cast' => 'string',
                ],
                [
                    'from' => 'ext_id',
                    'to' => 'identifiers.ext_id',
                    'cast' => 'copy',
                ],
                [
                    'from' => 'order_key',
                    'to' => 'order_key',
                    'cast' => 'null',
                ],
                [
                    'from' => 'billing.first_name',
                    'to' => 'attributes.FNAME',
                    'cast' => 'string',
                ],
                [
                    'from' => '?billing.last_name',
                    'to' => 'attributes.LNAME',
                    'cast' => 'string',
                ],
                [
                    'from' => 'billing.email',
                    'to' => 'email',
                    'cast' => 'string',
                ],
                [
                    'from' => 'billing',
                    'to' => 'billing',
                    'cast' => 'null',
                ],
                [
                    'from' => 'shipping',
                    'to' => 'shipping',
                    'cast' => 'null',
                ],
                [
                    'from' => 'payment_method',
                    'to' => 'payment_method',
                    'cast' => 'null',
                ],
                [
                    'from' => 'payment_method_title',
                    'to' => 'payment_method_title',
                    'cast' => 'null',
                ],
                [
                    'from' => 'transaction_id',
                    'to' => 'transaction_id',
                    'cast' => 'null',
                ],
                [
                    'from' => 'customer_ip_address',
                    'to' => 'ip_signup',
                    'cast' => 'null',
                ],
                [
                    'from' => 'customer_user_agent',
                    'to' => 'customer_user_agent',
                    'cast' => 'null',
                ],
                [
                    'from' => 'created_via',
                    'to' => 'created_via',
                    'cast' => 'null',
                ],
                [
                    'from' => 'customer_note',
                    'to' => 'notes',
                    'cast' => 'null',
                ],
                [
                    'from' => 'date_completed',
                    'to' => 'date_completed',
                    'cast' => 'null',
                ],
                [
                    'from' => 'date_paid',
                    'to' => 'date_paid',
                    'cast' => 'null',
                ],
                [
                    'from' => 'cart_hash',
                    'to' => 'cart_hash',
                    'cast' => 'null',
                ],
                [
                    'from' => 'order_stock_reduced',
                    'to' => 'order_stock_reduced',
                    'cast' => 'null',
                ],
                [
                    'from' => 'download_permissions_granted',
                    'to' => 'download_permissions_granted',
                    'cast' => 'null',
                ],
                [
                    'from' => 'new_order_email_sent',
                    'to' => 'new_order_email_sent',
                    'cast' => 'null',
                ],
                [
                    'from' => 'recorded_sales',
                    'to' => 'recorded_sales',
                    'cast' => 'null',
                ],
                [
                    'from' => 'recorded_coupon_usage_counts',
                    'to' => 'recorded_coupon_usage_counts',
                    'cast' => 'null',
                ],
                [
                    'from' => 'number',
                    'to' => 'number',
                    'cast' => 'null',
                ],
                [
                    'from' => 'tax_lines',
                    'to' => 'tax_lines',
                    'cast' => 'null',
                ],
                [
                    'from' => 'shipping_lines',
                    'to' => 'shipping_lines',
                    'cast' => 'null',
                ],
                [
                    'from' => 'fee_lines',
                    'to' => 'fee_lines',
                    'cast' => 'null',
                ],
                [
                    'from' => 'coupon_lines',
                    'to' => 'coupon_lines',
                    'cast' => 'null',
                ],
                [
                    'from' => 'currency',
                    'to' => 'currency',
                    'cast' => 'null',
                ],
            ],
            [
                [
                    'from' => 'linkedContactsIds',
                    'to' => 'linkedContactsIds',
                    'cast' => 'null',
                ],
            ],
            [
                [
                    'from' => 'order_id',
                    'to' => 'id',
                    'cast' => 'string',
                ],
                [
                    'from' => 'line_items[].product_id',
                    'to' => 'products[].productId',
                    'cast' => 'string',
                ],
                [
                    'from' => 'line_items[].quantity',
                    'to' => 'products[].quantity',
                    'cast' => 'integer',
                ],
                [
                    'from' => 'line_items[].product.price',
                    'to' => 'products[].price',
                    'cast' => 'number',
                ],
                [
                    'from' => 'line_items',
                    'to' => 'line_items',
                    'cast' => 'null',
                ],
            ],
        ],
        'workflow' => ['linked-contact', 'sync-woo-products'],
    ],
];

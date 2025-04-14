<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_create_partner($payload, $bridge)
{
    $partner = [
        'name' => $payload['name'],
    ];

    $partner_fields = [
        'complete_name',
        'title',
        'parent_id',
        'parent_name',
        'lang',
        'vat',
        'website',
        'employee',
        'function',
        'street',
        'street2',
        'zip',
        'city',
        'country_code',
        'email',
        'phone',
        'mobile',
        'is_company',
        'is_public',
        'company_id',
        'company_name',
        'additional_info',
    ];

    foreach ($partner_fields as $field) {
        if (isset($payload[$field])) {
            $partner[$field] = $payload[$field];
        }
    }

    $query = [['name', '=', $payload['name']]];

    if (isset($partner['email'])) {
        $query[] = ['email', '=', $payload['email']];
    }

    $response = $bridge
        ->patch([
            'name' => 'odoo-search-partner',
            'template' => null,
            'method' => 'search_read',
            'model' => 'res.partner',
        ])
        ->submit($query);

    if (!is_wp_error($response)) {
        return $response['data']['result'][0];
    }

    $response = $bridge
        ->patch([
            'name' => 'odoo-create-partner',
            'method' => 'create',
            'model' => 'res.partner',
        ])
        ->submit($partner);

    if (is_wp_error($response)) {
        return $response;
    }

    $response = $bridge
        ->patch([
            'name' => 'odoo-get-partner-data',
            'method' => 'read',
            'model' => 'res.partner',
        ])
        ->submit([$response['data']['result']]);

    if (is_wp_error($response)) {
        return $response;
    }

    return $response['data']['result'][0];
}

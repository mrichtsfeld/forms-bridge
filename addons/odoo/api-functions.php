<?php

if (!defined('ABSPATH')) {
    exit();
}

function forms_bridge_odoo_search_user_by_email($payload, $bridge)
{
    $response = $bridge
        ->patch([
            'name' => 'odoo-rpc-search-user-by-email',
            'template' => null,
            'method' => 'search_read',
            'model' => 'res.users',
        ])
        ->submit([['email', '=', $payload['email']]]);

    if (is_wp_error($response)) {
        return $response;
    }

    return $response['data']['result'][0];
}

function forms_bridge_odoo_date_to_time($payload)
{
    $date = $payload['date'];
    $hour = $payload['hour'] ?? '00';
    $minute = $payload['minute'] ?? '00';

    $form_data = apply_filters('forms_bridge_form', null);
    $date_index = array_search(
        'date',
        array_column($form_data['fields'], 'name')
    );
    $date_format = $form_data['fields'][$date_index]['format'] ?? '';

    if (strstr($date_format, '-')) {
        $separator = '-';
    } elseif (strstr($date_format, '.')) {
        $separator = '.';
    } elseif (strstr($date_format, '/')) {
        $separator = '/';
    }

    switch (substr($date_format, 0, 1)) {
        case 'y':
            [$year, $month, $day] = explode($separator, $date);
            break;
        case 'm':
            [$month, $day, $year] = explode($separator, $date);
            break;
        case 'd':
            [$day, $month, $year] = explode($separator, $date);
            break;
    }

    $date = "{$year}-{$month}-{$day}";

    if (preg_match('/(am|pm)/i', $hour, $matches)) {
        $hour = (int) $hour;
        if (strtolower($matches[0]) === 'pm') {
            $hour += 12;
        }
    }

    $time = strtotime("{$date} {$hour}:{$minute}");

    if ($time === false) {
        return new WP_Error('Invalid date format');
    }

    return $time;
}

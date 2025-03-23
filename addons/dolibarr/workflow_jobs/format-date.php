<?php

function forms_bridge_dolibarr_format_date($payload, $bridge)
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
        do_action(
            'forms_bridge_on_failure',
            $bridge,
            new WP_Error('Invalid date format'),
            $payload
        );

        return;
    }

    $payload['date'] = (string) $time;

    unset($payload['hour']);
    unset($payload['minute']);

    return $payload;
}

return [
    'title' => __('Format date', 'forms-bridge'),
    'description' => __(
        'Builds a date string from date, hour and minute fields',
        'forms-brdige'
    ),
    'method' => 'forms_bridge_dolibarr_format_date',
    'input' => ['date', 'hour', 'minute'],
    'output' => ['date'],
];

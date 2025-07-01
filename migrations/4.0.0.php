<?php

if (!defined('ABSPATH')) {
    exit();
}

$setting_names = [
    'bigin',
    'brevo',
    'dolibarr',
    'financoop',
    'gsheets',
    'holded',
    'listmonk',
    'mailchimp',
    'odoo',
    'rest-api',
    'zoho',
];

foreach ($setting_names as $setting_name) {
    $option = 'forms-bridge_' . $setting_name;

    $data = get_option($option, []);

    if (!isset($data['bridges'])) {
        continue;
    }

    foreach ($data['bridges'] as &$bridge_data) {
        if (!isset($bridge_data['workflow'])) {
            continue;
        }

        $i = 0;
        while ($i < count($bridge_data['workflow'])) {
            $job_name = $bridge_data['workflow'][$i];

            if (strpos('forms-bridge-', $job_name) === 0) {
                $job_name = substr($job_name, 13);
            } elseif (strpos($job_name, $setting_name) === 0) {
                $job_name = substr($job_name, strlen($setting_name) + 1);
            }

            $bridge_data['workflow'][$i] = $job_name;
            $i++;
        }
    }

    update_option($option, $data);
}

$http = get_option('http-bridge_general', []);

if (isset($http['backends'])) {
    foreach ($http['backends'] as &$backend) {
        $header_names = array_column($backend['headers'], 'name');
        $user_index = array_search('api_user', $header_names);
        $token_index = array_search('token', $header_names);

        if ($user_index !== false && $token_index !== false) {
            $user = $backend['headers'][$user_index]['value'];
            $token = $backend['headers'][$user_index]['value'];

            $headers = [];
            foreach ($backend['headers'] as $header) {
                if (!in_array($header['name'], ['api_user', 'token'], true)) {
                    $headers[] = $header;
                }
            }

            $headers[] = [
                'name' => 'Authorization',
                'value' => "token {$user}:{$token}",
            ];

            $backend['headers'] = $headers;
        } elseif (strstr($backend['base_url'], 'api.mailchimp.com')) {
            $index = array_search(
                'api-key',
                array_column($backend['headers'], 'name')
            );

            if ($index !== false) {
                $key = $backend['headers'][$index]['value'];
                $backend['headers'][] = [
                    'name' => 'Authorization',
                    'value' => 'Basic ' . base64_encode("forms-bridge:{$key}"),
                ];

                array_splice($backend['headers'], $index, 1);
            }
        }
    }

    update_option('http-bridge_general', $http);
}

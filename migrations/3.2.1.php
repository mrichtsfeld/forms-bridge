<?php

$setting_names = [
    'rest-api',
    'dolibarr',
    'odoo',
    'financoop',
    'google-sheets',
    'zoho',
];

foreach ($setting_names as $setting_name) {
    $option = 'forms-bridge_' . $setting_name;

    $data = get_option($option, []);

    if (!isset($data['bridges'])) {
        continue;
    }

    foreach ($data['bridges'] as &$bridge_data) {
        $constants = $bridge_data['constants'] ?? [];
        $workflow = $bridge_data['workflow'] ?? [];
        $mutations = $bridge_data['mutations'] ?? [];

        if (in_array('forms-bridge-submission-id', $workflow)) {
            $index = array_search('forms-bridge-submission-id', $workflow);
            array_splice($workflow, $index, 1);
            $job_mutations = array_splice($mutations, $index, 1);
            $constants[] = [
                'name' => 'submission_id',
                'value' => '$submission_id',
            ];

            foreach ($job_mutations as $mapper) {
                $mutations[0][] = $mapper;
            }
        }

        if (in_array('forms-bridge-current-datetime', $workflow)) {
            $index = array_search('forms-bridge-current-datetime', $workflow);
            array_splice($workflow, $index, 1);
            $job_mutations = array_splice($mutations, $index, 1);
            $constants[] = ['name' => 'datetime', 'value' => '$datetime'];

            foreach ($job_mutations as $mapper) {
                $mutations[0][] = $mapper;
            }
        }

        if (in_array('forms-bridge-current-iso-time', $workflow)) {
            $index = array_search('forms-bridge-current-iso-time', $workflow);
            array_splice($workflow, $index, 1);
            $job_mutations = array_splice($mutations, $index, 1);
            $constants[] = ['name' => 'datetime', 'value' => '$iso_date'];

            foreach ($job_mutations as $mapper) {
                $mutations[0][] = $mapper;
            }
        }

        if (in_array('forms-bridge-referer', $workflow)) {
            $index = array_search('forms-bridge-referer', $workflow);
            array_splice($workflow, $index, 1);
            $job_mutations = array_splice($mutations, $index, 1);
            $constants[] = ['name' => 'referer', 'value' => '$referer'];

            foreach ($job_mutations as $mapper) {
                $mutations[0][] = $mapper;
            }
        }

        if (in_array('forms-bridge-current-locale', $workflow)) {
            $index = array_search('forms-bridge-current-locale', $workflow);
            array_splice($workflow, $index, 1);
            $job_mutations = array_splice($mutations, $index, 1);
            $constants[] = ['name' => 'locale', 'value' => '$locale'];

            foreach ($job_mutations as $mapper) {
                $mutations[0][] = $mapper;
            }
        }

        if (in_array('forms-bridge-ip-address', $workflow)) {
            $index = array_search('forms-bridge-ip-address', $workflow);
            array_splice($workflow, $index, 1);
            $job_mutations = array_splice($mutations, $index, 1);
            $constants[] = ['name' => 'IP', 'value' => '$ip_address'];

            foreach ($job_mutations as $mapper) {
                $mutations[0][] = $mapper;
            }
        }

        $bridge_data['workflow'] = $workflow;
        $bridge_data['mutations'] = $mutations;
        $bridge_data['constants'] = $constants;
    }

    update_option($option, $data);
}

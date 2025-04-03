<?php

if (!defined('ABSPATH')) {
    exit();
}

add_filter(
    'forms_bridge_workflow_jobs',
    function ($jobs) {
        if (!wp_is_numeric_array($jobs)) {
            $jobs = [];
        }

        $job = new \FORMS_BRIDGE\Workflow_Job(
            'ip-address',
            [
                'title' => __('IP address', 'forms-bridge'),
                'description' => __(
                    'Gets the source IP address of the form submission and place it as the IP attribute on the payload',
                    'forms-bridge'
                ),
                'method' => 'forms_bridge_workflow_job_ip_address',
                'input' => [],
                'output' => [
                    [
                        'name' => 'IP',
                        'schema' => ['type' => 'string'],
                    ],
                ],
            ],
            'forms-bridge'
        );

        if (!is_wp_error($job->config)) {
            $jobs[] = $job;
        }

        return $jobs;
    },
    20,
    1
);

function forms_bridge_workflow_job_ip_address($payload)
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $payload['IP'] = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $payload['IP'] = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }

    return $payload;
}

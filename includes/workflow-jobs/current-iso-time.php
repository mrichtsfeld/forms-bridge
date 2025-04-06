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
            'current-iso-time',
            [
                'title' => __('ISO date', 'forms-bridge'),
                'description' => __(
                    'Sets the submission date and time on the payload with format ISO 8601',
                    'forms-bridge'
                ),
                'method' => 'forms_bridge_workflow_job_iso_date',
                'input' => [],
                'output' => [
                    [
                        'name' => 'datetime',
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

function forms_bridge_workflow_job_iso_date($payload)
{
    $payload['datetime'] = date('c', time());
    return $payload;
}

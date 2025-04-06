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
            'current-datetime',
            [
                'title' => __('Date and time', 'forms-bridge'),
                'description' => __(
                    'Sets the submission date and time on the payload with format Y-m-d H:M:S',
                    'forms-bridge'
                ),
                'method' => 'forms_bridge_workflow_job_current_date',
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

function forms_bridge_workflow_job_current_date($payload)
{
    $payload['datetime'] = date('Y-m-d H:i:s', time());
    return $payload;
}

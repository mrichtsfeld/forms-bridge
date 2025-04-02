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
            'referer',
            [
                'title' => __('Referer', 'forms-bridge'),
                'description' => __(
                    'Gets the source URL of the form submission and place it as the referer attribute on the payload',
                    'forms-bridge'
                ),
                'method' => 'forms_bridge_workflow_job_referer',
                'input' => [],
                'output' => [
                    [
                        'name' => 'referer',
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

function forms_bridge_workflow_job_referer($payload)
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $payload['referer'] = sanitize_text_field($_SERVER['HTTP_REFERER']);
    }

    return $payload;
}

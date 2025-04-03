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
            'submission-id',
            [
                'title' => __('Submission ID', 'forms-bridge'),
                'description' => __(
                    'Adds the submission object ID to the payload',
                    'forms-bridge'
                ),
                'method' => 'forms_bridge_workflow_job_submission_id',
                'input' => [],
                'output' => [
                    [
                        'name' => 'submission_id',
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

function forms_bridge_workflow_job_submission_id($payload)
{
    $submission = apply_filters('forms_bridge_submission', [], true);

    if (isset($submission['id'])) {
        $payload['submission_id'] = (string) $submission['id'];
    } elseif (
        gettype($submission) === 'object' &&
        method_exists($submission, 'get_posted_data_hash')
    ) {
        $payload[
            'submission_id'
        ] = (string) $submission->get_posted_data_hash();
    } elseif (isset($submission['actions']['save']['sub_id'])) {
        $payload['submission_id'] =
            (string) $submission['actions']['save']['sub_id'];
    } elseif (isset($submission['entry_id'])) {
        $payload['submission_id'] = $submission['entry_id'];
    }

    return $payload;
}

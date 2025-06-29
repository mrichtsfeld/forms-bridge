<?php

if (!defined('ABSPATH')) {
    exit();
}

add_action('init', function () {
    register_post_type('fb-workflow-job', [
        'labels' => [
            'name' => __('Workflow jobs', 'forms-bridge'),
            'singluar_name' => __('Workflow job', 'forms-bridge'),
        ],
        'public' => false,
        'supports' => ['title', 'excerpt', 'custom-fields'],
    ]);
});

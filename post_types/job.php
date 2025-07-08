<?php

if (!defined('ABSPATH')) {
    exit();
}

add_action('init', function () {
    register_post_type('fb-job', [
        'labels' => [
            'name' => __('Jobs', 'forms-bridge'),
            'singular_name' => __('Job', 'forms-bridge'),
        ],
        'public' => false,
        'supports' => ['title', 'excerpt', 'custom-fields'],
        'capability_type' => 'post',
        'can_export' => false,
    ]);
});

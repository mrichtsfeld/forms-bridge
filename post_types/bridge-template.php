<?php

if (!defined('ABSPATH')) {
    exit();
}

add_action('init', function () {
    register_post_type('fb-bridge-template', [
        'labels' => [
            'name' => __('Bridge templates', 'forms-bridge'),
            'singular_name' => __('Bridge template', 'forms-bridge'),
        ],
        'public' => false,
        'supports' => ['title', 'excerpt', 'custom-fields'],
    ]);
});

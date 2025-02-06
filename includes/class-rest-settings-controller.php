<?php

namespace FORMS_BRIDGE;

use WP_Error;
use WP_REST_Server;
use WPCT_ABSTRACT\REST_Settings_Controller as Base_Controller;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Plugin REST API controller
 */
class REST_Settings_Controller extends Base_Controller
{
    /**
     * Inherits the parent initialized and register the post types route
     *
     * @param string $group Plugin settings group name.
     */
    protected static function init()
    {
        parent::init();
        self::register_forms_route();
        self::register_templates_route();
    }

    /**
     * Registers form API routes.
     */
    private static function register_forms_route()
    {
        // forms endpoint registration
        $namespace = self::namespace();
        $version = self::version();
        register_rest_route("{$namespace}/v{$version}", '/forms', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => static function () {
                return self::forms();
            },
            'permission_callback' => static function () {
                return self::permission_callback();
            },
        ]);
    }

    private static function register_templates_route()
    {
        $namespace = self::namespace();
        $version = self::version();
        register_rest_route(
            "{$namespace}/v{$version}",
            '/templates/(?P<name>[a-zA-Z0-9-]+)',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($request) {
                        return self::get_template($request);
                    },
                    'permission_callback' => static function () {
                        return self::permission_callback();
                    },
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => static function ($request) {
                        return self::post_template($request);
                    },
                    'permission_callback' => static function () {
                        return self::permission_callback();
                    },
                ],
            ]
        );
    }

    /**
     * GET requests forms endpoint callback.
     *
     * @return array Collection of array form representations.
     */
    private static function forms()
    {
        $forms = apply_filters('forms_bridge_forms', []);
        return array_map(static function ($form) {
            unset($form['hooks']);
            return $form;
        }, $forms);
    }

    private static function get_template($request)
    {
        $template_name = $request['name'];
        $template = Form_Hook::get_template($template_name);
        if (!$template) {
            return new WP_Error(
                'not_found',
                __('Template not found', 'forms-bridge'),
                ['name' => $template_name]
            );
        }

        return $template->to_json();
    }

    private static function post_template($request)
    {
        // TODO: Create the new form hook from the template
    }
}

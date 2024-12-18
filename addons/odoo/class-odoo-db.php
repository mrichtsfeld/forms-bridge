<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Odoo_DB
{
    private $data = null;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'backend':
                return $this->backend();
            case 'form_hooks':
                return $this->form_hooks();
            default:
                return isset($this->data[$name]) ? $this->data[$name] : null;
        }
    }

    private function backend()
    {
        return apply_filters(
            'http_bridge_backend',
            null,
            $this->data['backend']
        );
    }

    private function form_hooks()
    {
        $form_hooks = apply_filter('forms_bridge_form_hooks', []);
        return array_values(
            array_filter($form_hooks, function ($form_hook) {
                return $form_hook->api === 'odoo' &&
                    $form_hook->database === $this->data['name'];
            })
        );
    }
}

<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form hook implementation for the Odoo JSON-RPC api.
 */
class Odoo_Form_Hook extends Form_Hook
{
    /**
     * Handles the form hook's template class.
     *
     * @var string
     */
    protected static $template_class = '\FORMS_BRIDGE\Odoo_Form_Hook_Template';

    /**
     * Inherits the parent constructor and sets data constants.
     *
     * @param array $data Hook data.
     * @param string $api Form hook API name.
     */
    public function __construct($data, $api)
    {
        parent::__construct(
            array_merge($data, [
                'endpoint' => '/jsonrpc',
                'method' => 'POST',
            ]),
            $api
        );

        add_filter(
            'forms_bridge_hook_database',
            function ($name, $hook) {
                if ($name instanceof Odoo_DB) {
                    return $name;
                }

                if ($hook->name === $this->name) {
                    return $this->database($name);
                }

                return $name;
            },
            10,
            2
        );
    }

    /**
     * Returns json as static form hook content type.
     *
     * @return string.
     */
    protected function content_type()
    {
        return 'application/json';
    }

    /**
     * Intercepts backend access and returns it from the database.
     *
     * @return Http_Backend|null
     */
    protected function backend()
    {
        return $this->database->backend;
    }

    /**
     * Form hook's database private getter.
     *
     * @return Odoo_DB|null
     */
    private function database($name)
    {
        $dbs = Forms_Bridge::setting('odoo')->databases;
        foreach ($dbs as $db) {
            if ($db['name'] === $name) {
                return new Odoo_DB($db);
            }
        }
    }
}

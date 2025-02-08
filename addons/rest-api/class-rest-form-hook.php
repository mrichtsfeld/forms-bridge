<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form hook implamentation for the REST API protocol.
 */
class Rest_Form_Hook extends Form_Hook
{
    /**
     * Inherits the parent constructor and sets its api name.
     *
     * @param array $data Hook data.
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->api = 'rest-api';
    }
}

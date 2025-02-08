<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form hook implamentation for the FinanCoop REST API.
 */
class Finan_Coop_Form_Hook extends Form_Hook
{
    /**
     * Inherits the parent constructor and sets its api name.
     *
     * @param array $data Hook data.
     */
    public function __construct($data)
    {
        $data['method'] = 'POST';
        parent::__construct($data);
        $this->api = 'financoop';
    }
}

<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form hook implementation for the Google Sheets service.
 */
class Google_Sheets_Form_Hook extends Form_Hook
{
    /**
     * Handles the form hook's template class.
     *
     * @var string
     */
    protected static $template_class = '\FORMS_BRIDGE\Google_Sheets_Form_Hook_Template';

    /**
     * Inherits the parent constructor and sets its api name.
     *
     * @param array $data Hook data.
     */
    public function __construct($data)
    {
        $this->api = 'google-sheets';
        parent::__construct($data);
    }
}

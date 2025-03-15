<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

use WP_Error;

/**
 * Form bridge implamentation for the Dolibarr REST API.
 */
class Dolibarr_Form_Bridge extends Form_Bridge
{
    /**
     * Handles the form bridge's template class.
     *
     * @var string
     */
    protected static $template_class = '\FORMS_BRIDGE\Dolibarr_Form_Bridge_Template';

    /**
     * Performs an http request to Dolibarr's REST API.
     *
     * @param array $payload Payload data.
     * @param array $attachments Submission's attached files.
     *
     * @return array|WP_Error Http request response.
     */
    public function do_submit($payload, $attachments = [])
    {
        return $this->backend->post(
            $this->endpoint,
            $payload,
            [],
            $attachments
        );
    }
}

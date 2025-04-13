<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the Mailchimp API.
 */
class Mailchimp_Form_Bridge extends Rest_Form_Bridge
{
    /**
     * Gets bridge's default body encoding schema.
     *
     * @return string|null
     */
    protected function content_type()
    {
        return 'application/json';
    }

    /**
     * Performs an http request to backend's REST API.
     *
     * @param array $payload Payload data.
     * @param array $attachments Submission's attached files.
     *
     * @return array|WP_Error Http request response.
     */
    protected function do_submit($payload, $attachments = [])
    {
        add_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Mailchimp_Form_Bridge::basic_auth',
            10,
            1
        );

        $response = parent::do_submit($payload, $attachments);

        if (is_wp_error($response)) {
            $a = 1;
        }

        return $response;
    }

    protected function api_fields()
    {
        if (strstr($this->endpoint, '/lists/') !== false) {
            $fields = [
                'email_address',
                'status',
                'email_type',
                'interests',
                'language',
                'vip',
                'location',
                'marketing_permissions',
                'ip_signup',
                'ip_opt',
                'timestamp_opt',
                'tags',
                'merge_fields',
            ];

            $fields_endpoint = str_replace(
                '/members',
                '/merge-fields',
                $this->endpoint
            );
            $response = $this->patch([
                'name' => 'mailchimp-get-merge-fields',
                'endpoint' => $fields_endpoint,
                'method' => 'GET',
            ])->submit([]);

            if (is_wp_error($response)) {
                return [];
            }

            foreach ($response['data']['merge_fields'] as $field) {
                $fields[] = 'merge_fields.' . $field['tag'];
            }

            return $fields;
        }

        return [];
    }

    public static function basic_auth($args)
    {
        if (isset($args['headers']['Api-Key'])) {
            $basic_auth =
                'Basic ' .
                base64_encode('forms-bridge:' . $args['headers']['Api-Key']);
            unset($args['headers']['Api-Key']);
            $args['headers']['Authorization'] = $basic_auth;
        }

        remove_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Mailchimp_Form_Bridge::basic_auth',
            10,
            1
        );

        return $args;
    }
}

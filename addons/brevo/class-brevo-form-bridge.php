<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the REST API protocol.
 */
class Brevo_Form_Bridge extends Rest_Form_Bridge
{
    /**
     * Handles a custom http origin token to be unseted from headers
     * before submits.
     *
     * @var string
     */
    public const http_origin_token = 'brevo-http-origin';

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
            '\FORMS_BRIDGE\Brevo_Form_Bridge::decorate_headers',
            10,
            1
        );

        $response = parent::do_submit($payload, $attachments);

        if (is_wp_error($response)) {
            $error_response = $response->get_error_data()['response'];
            if (
                $error_response['response']['code'] !== 425 &&
                $error_response['response']['code'] !== 400
            ) {
                return $response;
            }

            $data = json_decode($error_response['body'], true);
            if ($data['code'] !== 'duplicate_parameter') {
                return $response;
            }

            if (
                !isset($payload['email']) ||
                strstr($this->endpoint, '/v3/contacts') === false
            ) {
                return $response;
            }

            $update_response = $this->patch([
                'name' => 'brevo-update-contact-by-email',
                'endpoint' => "/v3/contacts/{$payload['email']}?identifierType=email_id",
                'method' => 'PUT',
            ])->submit($payload);

            if (is_wp_error($update_response)) {
                return $update_response;
            }

            return $this->patch([
                'name' => 'brevo-search-contact-by-email',
                'endpoint' => "/v3/contacts/{$payload['email']}",
                'method' => 'GET',
            ])->submit(['identifierType' => 'email_id']);
        }

        return $response;
    }

    protected function api_fields()
    {
        if ($this->method === 'POST') {
            return [
                'email',
                'name',
                'status',
                'lists',
                'list_uuids',
                'preconfirm_subscriptions',
                'attribs',
            ];
        }

        return [];
    }

    public static function decorate_headers($args)
    {
        if (isset($args['headers']['Api-Key'])) {
            $args['headers']['api-key'] = $args['headers']['Api-Key'];
            unset($args['headers']['Api-Key']);
        }

        remove_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Brevo_Form_Bridge::decorate_headers',
            10,
            1
        );

        return $args;
    }
}

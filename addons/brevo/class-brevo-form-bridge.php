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
     * @var array<string>
     */
    public const api_headers = ['Accept', 'Content-Type', 'Api-Key'];

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
            '\FORMS_BRIDGE\Brevo_Form_Bridge::prepare_headers',
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
        if (strstr($this->endpoint, 'contacts')) {
            $response = $this->patch([
                'name' => 'brevo-contacts-attributes',
                'endpoint' => '/v3/contacts/attributes',
                'method' => 'GET',
            ])->submit([]);

            if (is_wp_error($response)) {
                return [];
            }

            if ($this->endpoint === '/v3/contacts/doubleOptinConfirmation') {
                $fields = [
                    'email',
                    'includeListIds',
                    'excludeListIds',
                    'templateId',
                    'redirectionUrl',
                    'attributes',
                ];
            } else {
                $fields = [
                    'email',
                    'ext_id',
                    'emailBlacklisted',
                    'smsBlacklisted',
                    'listIds',
                    'updateEnabled',
                    'smtpBlacklistSender',
                    'attributes',
                ];
            }

            foreach ($response['data']['attributes'] as $attribute) {
                $fields[] = 'attributes.' . $attribute['name'];
            }

            return $fields;
        } else {
            preg_match('/\/([a-z]+)$/', $this->endpoint, $matches);
            $module = $matches[1];
            $response = $this->patch([
                'endpoint' => "brevo-{$module}-attributes",
                'endpoint' => "/v3/crm/attributes/{$module}",
                'method' => 'GET',
            ])->submit([]);

            if (is_wp_error($response)) {
                return [];
            }

            if ($module === 'companies') {
                $fields = [
                    'name',
                    'countryCode',
                    'linkedContactsIds',
                    'linkedDealsIds',
                    'attributes',
                ];
            } elseif ($module === 'deals') {
                $fields = [
                    'name',
                    'linkedDealsIds',
                    'linkedCompaniesIds',
                    'attributes',
                ];
            }

            foreach ($response['data'] as $attribute) {
                $fields[] = 'attributes.' . $attribute['internalName'];
            }

            return $fields;
        }
    }

    public static function prepare_headers($args)
    {
        if (isset($args['headers']['Api-Key'])) {
            $api_headers = [];
            foreach ($args['headers'] as $name => $value) {
                if (in_array($name, self::api_headers)) {
                    $api_headers[strtolower($name)] = $value;
                }
            }

            $args['headers'] = $api_headers;
        }

        remove_filter(
            'http_request_args',
            '\FORMS_BRIDGE\Brevo_Form_Bridge::prepare_headers',
            10,
            1
        );

        return $args;
    }
}

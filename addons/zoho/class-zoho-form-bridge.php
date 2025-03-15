<?php

namespace FORMS_BRIDGE;

use TypeError;
use WP_Error;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Form bridge implamentation for the Zoho API protocol.
 */
class Zoho_Form_Bridge extends Form_Bridge
{
    /**
     * Handles a custom http origin token to be unseted from headers
     * before submits.
     *
     * @var string
     */
    public const http_origin_token = 'zoho-http-origin';

    /**
     * Handles the oauth credentials transient name.
     *
     * @var string
     */
    private const credentials_transient = 'forms-bridge-zoho-oauth-credentials';

    /**
     * Handles the form bridge's template class.
     *
     * @var string
     */
    protected static $template_class = '\FORMS_BRIDGE\Zoho_Form_Bridge_Template';

    /**
     * Performs an authentication request to the zoho oauth server using
     * the bridge credentials.
     */
    private function get_access_token()
    {
        $credentials = get_transient(self::credentials_transient);

        if ($credentials) {
            try {
                $credentials = json_decode($credentials, true);
            } catch (TypeError) {
                $credentials = false;
            }
        }

        if (
            is_array($credentials) &&
            isset($credentials['access_token'], $credentials['expires_at'])
        ) {
            if (
                $credentials['expires_at'] < time() - 10 &&
                $credentials['scope'] === $this->scope
            ) {
                return $credentials['access_token'];
            }
        }

        $base_url = $this->backend->base_url;

        $host = parse_url($base_url)['host'] ?? null;
        if (!$host) {
            return;
        }

        $region = null;
        if (preg_match('/\.([a-z]{2,3}(\.[a-z]{2})?)$/', $host, $matches)) {
            $region = $matches[1];
        } else {
            Logger::log('Invalid Zoho API URL', Logger::ERROR);
            return;
        }

        $oauth_server = 'https://accounts.zoho.' . $region;
        $url = $oauth_server . '/oauth/v2/token';

        $headers = $this->backend->headers;
        $scope = $this->scope ?: 'ZohoCRM.';
        $service = explode('.', $scope)[0] ?? 'ZohoCRM';

        $query = http_build_query([
            'client_id' => $headers['client_id'] ?? '',
            'client_secret' => $headers['client_secret'] ?? '',
            'grant_type' => 'client_credentials',
            'scope' => $scope,
            'soid' => implode('.', [
                $service,
                $headers['organization_id'] ?? '',
            ]),
        ]);

        $response = http_bridge_post($url . '?' . $query);

        if (is_wp_error($response)) {
            Logger::log('Oauth response error', Logger::ERROR);
            Logger::log($response, Logger::ERROR);
            return;
        }

        set_transient(
            self::credentials_transient,
            $response['body'],
            $response['data']['expires_in'] - 30
        );

        return $response['data']['access_token'];
    }
    /**
     * Performs an http request to the Zoho API backend.
     *
     * @param array $payload Payload data.
     * @param array $attachments Submission's attached files.
     *
     * @return array|WP_Error Http request response.
     */
    public function do_submit($payload, $attachments = [])
    {
        $access_token = self::get_access_token();

        add_filter(
            'http_request_args',
            static function ($args) {
                $origin = $args['headers']['Origin'] ?? null;

                if ($origin === self::http_origin_token) {
                    unset($args['headers']['Origin']);
                    unset($args['headers']['Client-Id']);
                    unset($args['headers']['Client-Secret']);
                    unset($args['headers']['Organization-Id']);
                }

                return $args;
            },
            10,
            1
        );

        $payload = wp_is_numeric_array($payload) ? $payload : [$payload];

        return $this->backend->post(
            $this->endpoint,
            ['data' => $payload],
            [
                'Origin' => self::http_origin_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $access_token,
            ],
            $attachments
        );
    }
}

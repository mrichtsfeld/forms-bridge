<?php

namespace WPCT_ERP_FORMS;

use WPCT_ABSTRACT\Singleton;
use WPCT_HTTP\Http_Client as Wpct_Http_Client;
use Exception;

abstract class Integration extends Singleton
{
    /**
     * Retrive the current form.
     *
     * @return array $form_data Form data array representation.
     */
    abstract public function get_form();

    /**
     * Retrive form by ID.
     *
     * @since 3.0.0
     *
     * @return array $form_data Form data array representation.
     */
    abstract public function get_form_by_id($form_id);

    /**
     * Retrive available integration forms.
     *
     * @since 3.0.0
     *
     * @return array $forms Collection of form data array representations.
     */
    abstract public function get_forms();

    /**
     * Retrive the current form submission.
     *
     * @since 3.0.0
     *
     * @return array $submission Submission data array representation.
     */
    abstract public function get_submission();

    /**
     * Retrive the current submission uploaded files.
     *
     * @since 3.0.0
     *
     * @return array $files Collection of file array representations.
     */
    abstract public function get_uploads();

    /**
     * Serialize the current form submission data.
     *
     * @since 1.0.0
     *
     * @param any $submission Pair plugin submission handle.
     * @param array $form_data Source form data.
     * @return array $submission_data Submission data.
     */
    abstract public function serialize_submission($submission, $form);

    /**
     * Serialize the current form data.
     *
     * @since 1.0.0
     *
     * @param any $form Pair plugin form handle.
     * @return array $form_data Form data.
     */
    abstract public function serialize_form($form);

    /**
     * Get uploads from pair submission handle.
     *
     * @since 1.0.0
     *
     * @param any $submission Pair plugin submission handle.
     * @param array $form_data Current form data.
     * @return array $uploads Collection of file array representations.
     */
    abstract protected function submission_uploads($submission, $form_data);

    /**
     * Integration initializer to be fired on wp init.
     *
     * @since 0.0.1
     */
    abstract protected function init();

    /**
     * Bind integration initializer to wp init hook.
     *
     * @since 0.0.1
     */
    protected function __construct()
    {
        add_action('init', function () {
            $this->init();
        });
    }

    /**
     * Submit many requests with Wpct_Http_Client.
     *
     * @since 1.0.0
     *
     * @param array $requests Array of requests.
     * @return boolean $success Submit resolution.
     */
    private function submit($requests)
    {
        $success = true;
        foreach ($requests as $req) {
            if (!$success) {
                continue;
            }

            extract($req);
            if (empty($attachments)) {
                $response = Wpct_Http_Client::post($endpoint, $payload);
            } else {
                $response = Wpct_Http_Client::post_multipart(
                    $endpoint,
                    $payload,
                    $attachments
                );
            }

            $success =
                $success &&
                !is_wp_error($response) &&
                apply_filters(
                    '_wpct_erp_forms_validate_rpc_response',
                    true,
                    $response
                );
        }

        if (!$success) {
            $email = Settings::get_setting(
                'wpct-erp-forms',
                'general',
                'notification_receiver'
            );
            if (empty($email)) {
                return;
            }

            $to = $email;
            $subject = 'Wpct ERP Forms Error';
            $body = "Form ID: {$form_data['id']}\n";
            $body .= "Form title: {$form_data['title']}\n";
            $body .= 'Submission: ' . print_r($payload, true) . "\n";
            $body .=
                'Error: ' . print_r($response->get_error_data(), true) . "\n";
            $success = wp_mail($to, $subject, $body);
            if (!$success) {
                throw new Exception(
                    'Error while submitting form ' . $form_data['id']
                );
            }
        }

        return $success;
    }

    /**
     * Submit RPC form hooks.
     *
     * @since 2.0.0
     *
     * @param array $models Array of target models.
     * @param array $payload Submission data.
     * @param array $attachments Collection of attachment files.
     * @param array $form_data Source form data.
     * @return boolean $result Submit result.
     */
    private function submit_rpc($models, $payload, $attachments, $form_data)
    {
        $setting = Settings::get_setting('wpct-erp-forms', 'rpc-api');

        try {
            [$session_id, $user_id] = $this->rpc_login($setting['endpoint']);
        } catch (Exception) {
            return false;
        }

        $requests = array_map(function ($model) use (
            $setting,
            $payload,
            $attachments,
            $form_data,
            $session_id,
            $user_id
        ) {
            $pipes = [];
            foreach ($setting['hooks'] as $hook) {
                if (
                    $hook['form_id'] == $form_data['id'] &&
                    $hook['endpoint'] === $endpoint
                ) {
                    $pipes = $hook['pipes'];
                    break;
                }
            }

            $payload = apply_filters(
                'wpct_erp_forms_rpc_payload',
                $this->rpc_payload($session_id, 'object', 'execute', [
                    $setting['database'],
                    $user_id,
                    $setting['password'],
                    $model,
                    'create',
                    $this->apply_pipes($payload, $pipes),
                ]),
                $attachments,
                $form_data
            );

            return [
                'endpoint' => $setting['endpoint'],
                'payload' => $payload,
                'attachments' => $attachments,
                'form_data' => $form_data,
            ];
        }, $models);

        $validation = fn($result, $res) => $this->rpc_response_validation($res);
        add_filter('_wpct_erp_forms_validate_rpc_response', $validation, 2, 10);

        $result = $this->submit($requests);

        remove_filter(
            '_wpct_erp_forms_validate_rpc_response',
            $validation,
            2,
            10
        );
        return $result;
    }

    /**
     * Submit REST from hooks.
     *
     * @since 2.0.0
     *
     * @param array $endpoints Array of target endpoints.
     * @param array $payload Submission data.
     * @param array $attachments Collection of attachment files.
     * @param array $form_data Source form data.
     * @return boolean $result Submit result.
     */
    private function submit_rest($endpoints, $payload, $attachments, $form_data)
    {
        ['form_hooks' => $hooks] = Settings::get_setting(
            'wpct-erp-forms',
            'rest-api'
        );
        $requests = array_map(function ($endpoint) use (
            $payload,
            $attachments,
            $form_data,
            $hooks
        ) {
            $pipes = [];
            foreach ($hooks as $hook) {
                if (
                    $hook['form_id'] == $form_data['id'] &&
                    $hook['endpoint'] === $endpoint
                ) {
                    $pipes = $hook['pipes'];
                    break;
                }
            }

            return [
                'endpoint' => $endpoint,
                'payload' => $this->apply_pipes($payload, $pipes),
                'attachments' => $attachments,
                'form_data' => $form_data,
            ];
        }, $endpoints);

        return $this->submit($requests);
    }

    /**
     * Form hooks submission subroutine.
     *
     * @since 1.0.0
     *
     * @param any $submission Pair plugin submission handle.
     * @param any $form Pair plugin form handle.
     */
    public function do_submission($submission, $form)
    {
        $form_data = $this->serialize_form($form);
        if ($form_data['ref'] === null) {
            return;
        }

        $uploads = $this->submission_uploads($submission, $form_data);
        $uploads = array_reduce(
            array_keys($uploads),
            function ($carry, $name) use ($uploads) {
                if ($uploads[$name]['is_multi']) {
                    for ($i = 1; $i <= count($uploads[$name]['path']); $i++) {
                        $carry[$name . '_' . $i] =
                            $uploads[$name]['path'][$i - 1];
                    }
                } else {
                    $carry[$name] = $uploads[$name]['path'];
                }

                return $carry;
            },
            []
        );

        $attachments = apply_filters(
            'wpct_erp_forms_attachments',
            $uploads,
            $form_data
        );

        $submission = $this->serialize_submission($submission, $form_data);
        $this->cleanup_empties($submission);
        $payload = apply_filters(
            'wpct_erp_forms_payload',
            $submission,
            $attachments,
            $form_data
        );

        $endpoints = apply_filters(
            'wpct_erp_forms_endpoints',
            $this->get_form_endpoints($form_data['id']),
            $payload,
            $attachments,
            $form_data
        );
        $models = apply_filters(
            'wpct_erp_forms_models',
            $this->get_form_models($form_data['id']),
            $payload,
            $attachments,
            $form_data
        );

        do_action(
            'wpct_erp_forms_before_submission',
            $payload,
            $attachments,
            $form_data
        );

        $success = $this->submit_rest(
            $endpoints,
            $payload,
            $attachments,
            $form_data
        );
        $success =
            $success &&
            $this->submit_rpc($models, $payload, $attachments, $form_data);

        if ($success) {
            do_action(
                'wpct_erp_forms_after_submission',
                $payload,
                $attachments,
                $form_data
            );
        } else {
            do_action(
                'wpct_erp_forms_on_failure',
                $payload,
                $attachments,
                $form_data
            );
        }
    }

    /**
     * Apply cast pipes to the submission data.
     *
     * @since 3.0.0
     *
     * @param array $payload Submission data.
     * @param array $form_data Form data.
     */
    private function apply_pipes($payload, $form_data)
    {
        ['form_hooks' => $rest_hooks] = Settings::get_setting(
            'wpct-erp-forms',
            'rest-api'
        );
        ['form_hooks' => $rpc_hooks] = Settings::get_setting(
            'wpct-erp-forms',
            'rpc-api'
        );

        foreach (array_merge($rest_hooks, $rpc_hooks) as $hook) {
            if ($hook['form_id'] == $form_data['id']) {
            }
        }
    }

    /**
     * Clean up submission empty fields.
     *
     * @since 1.0.0
     *
     * @param array $submission_data Submission data.
     * @return array $submission_data Submission data without empty fields.
     */
    private function cleanup_empties(&$submission_data)
    {
        foreach ($submission_data as $key => $val) {
            if (empty($val)) {
                unset($submission_data[$key]);
            }
        }

        return $submission_data;
    }

    /**
     * Get form RPC bounded models.
     *
     * @since 2.0.0
     *
     * @param int $form_id Form ID.
     * @return array $models Array of model names.
     */
    private function get_form_models($form_id)
    {
        $rpc_forms = Settings::get_setting(
            'wpct-erp-forms',
            'rpc-api',
            'forms'
        );
        return array_unique(
            array_map(
                function ($form) {
                    return $form['model'];
                },
                array_filter($rpc_forms, function ($form) use ($form_id) {
                    return (string) $form['form_id'] === (string) $form_id &&
                        !empty($form['model']);
                })
            )
        );
    }

    /**
     * Get form REST bounded endpoints.
     *
     * @since 2.0.0
     *
     * @param int $form_id Form ID.
     * @return array $endpoints Array of endpoints.
     */
    private function get_form_endpoints($form_id)
    {
        $rest_forms = Settings::get_setting(
            'wpct-erp-forms',
            'rest-api',
            'forms'
        );
        return array_unique(
            array_map(
                function ($form) {
                    return $form['endpoint'];
                },
                array_filter($rest_forms, function ($form) use ($form_id) {
                    return (string) $form['form_id'] === (string) $form_id &&
                        !empty($form['endpoint']);
                })
            )
        );
    }

    /**
     * JSON RPC login request.
     *
     * @since 2.0.0
     *
     * @param string $endpoint Target endpoint.
     * @return array $credentials Tuple with $session_id and $user_id.
     */
    private function rpc_login($endpoint)
    {
        $session_id = time();
        $setting = Settings::get_setting('wpct-erp-forms', 'rpc-api');

        $payload = apply_filters(
            'wpct_erp_forms_rpc_login',
            $this->rpc_payload($session_id, 'common', 'login', [
                $setting['database'],
                $setting['user'],
                $setting['password'],
            ])
        );

        $res = Wpct_Http_Client::post($endpoint, $payload);

        if (is_wp_error($res)) {
            throw new Exception('Error while establish RPC session');
        }

        $login = (array) json_decode($res['body'], true);
        if (isset($login['error'])) {
            throw new Exception('RPC login error');
        }
        $user_id = $login['result'];
        return [$session_id, $user_id];
    }

    /**
     * RPC payload decorator.
     *
     * @since 2.0.0
     *
     * @param int $session_id RPC session ID.
     * @param string $service RPC service name.
     * @param string $method RPC method name.
     * @param array $args RPC request arguments.
     * @return array $payload RPC conformant payload.
     */
    private function rpc_payload($session_id, $service, $method, $args)
    {
        return [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'id' => $session_id,
            'params' => [
                'service' => $service,
                'method' => $method,
                'args' => $args,
            ],
        ];
    }

    /**
     * RPC does not work on HTTP standard. This method validate RPC responses
     * on the application layer.
     *
     * @since 2.0.0
     *
     * @param array $res RPC response.
     * @return boolean $result RPC response result.
     */
    private function rpc_response_validation($res)
    {
        $payload = (array) json_decode($res['body'], true);
        if (isset($payload['error'])) {
            return false;
        }

        return true;
    }
}

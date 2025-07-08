<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Credential
{
    public static function schema($addon = null)
    {
        return apply_filters(
            'forms_bridge_credential_schema',
            [
                '$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'form-bridge-schema',
                'type' => 'object',
                'properties' => [
                    'name' => [
                        'name' => __('Name', 'forms-bridge'),
                        'description' => __(
                            'Unique name of the credential',
                            'forms-bridge'
                        ),
                        'type' => 'string',
                        'minLength' => 1,
                    ],
                    'auth_url' => [
                        'name' => __('Auth URL', 'forms-bridge'),
                        'description' => __(
                            'OAuth authrization URL',
                            'forms-bridge'
                        ),
                        'type' => 'string',
                    ],
                ],
                'required' => ['name'],
                'additionalProperties' => false,
            ],
            $addon
        );
    }

    protected $data;

    protected $id;

    protected $addon;

    public function __construct($data, $addon)
    {
        $this->addon = $addon;
        $this->data = wpct_plugin_sanitize_with_schema(
            $data,
            static::schema($addon)
        );

        if ($this->is_valid) {
            $this->id = $this->addon . '-' . $data['name'];
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'id':
                return $this->id;
            case 'addon':
                return $this->addon;
            case 'is_valid':
                return !is_wp_error($this->data) &&
                    Addon::addon($this->addon) !== null;
            default:
                if (!$this->is_valid) {
                    return;
                }

                return $this->data[$name] ?? null;
        }
    }

    public function data()
    {
        if (!$this->is_valid) {
            return;
        }

        return array_merge(
            [
                'id' => $this->id,
                'name' => $this->name,
                'addon' => $this->addon,
            ],
            $this->data
        );
    }

    public function save()
    {
        if (!$this->is_valid) {
            return false;
        }

        $setting = Forms_Bridge::setting($this->addon);
        if (!$setting) {
            return false;
        }

        $credentials = $setting->credentials;
        if (!wp_is_numeric_array($credentials)) {
            return false;
        }

        $index = array_search($this->name, array_column($credentials, 'name'));

        if ($index === false) {
            $credentials[] = $this->data;
        } else {
            $credentials[$index] = $this->data;
        }

        $setting->credentials = $credentials;

        return true;
    }

    public function delete()
    {
        if ($this->is_valid) {
            return false;
        }

        $setting = Forms_Bridge::setting($this->addon);
        if (!$setting) {
            return false;
        }

        $credentials = $setting->credentials;
        if (!wp_is_numeric_array($credentials)) {
            return false;
        }

        $index = array_search($this->name, array_column($credentials, 'name'));

        if ($index === false) {
            return false;
        }

        array_splice($credentials, $index, 1);
        $setting->credentials = $credentials;

        return true;
    }
}

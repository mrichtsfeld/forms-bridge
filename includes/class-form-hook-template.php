<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Form_Hook_Template
{
    private $api;
    private $file;
    private $name;
    private $config;

    private static $default = [
        'fields' => [
            [
                'ref' => '#form',
                'name' => 'title',
                'label' => 'Form title',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#hook',
                'name' => 'name',
                'label' => 'Hook name',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#hook',
                'name' => 'backend',
                'label' => 'Backend',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#hook',
                'name' => 'form_id',
                'label' => 'Form',
                'type' => 'string',
                'required' => true,
            ],
            [
                'ref' => '#hook',
                'name' => 'endpoint',
                'label' => 'Endpoint',
                'type' => 'string',
                'required' => true,
            ],
        ],
        'hook' => [
            'name' => '',
            'backend' => '',
            'form_id' => '',
            'endpoint' => '',
        ],
    ];

    private static $schema = [
        'title' => ['type' => 'string'],
        'integrations' => [
            'type' => 'array',
            'items' => ['type' => 'string'],
            'uniqueItems' => true,
        ],
        'fields' => [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'ref' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'type' => ['type' => 'string'],
                    'required' => ['type' => 'boolean'],
                    'const' => [],
                    'default' => [],
                    'options' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    'enum' => [
                        'type' => 'array',
                        'items' => [],
                        'uniqueItems' => true,
                    ],
                    'attributes' => ['type' => 'object'],
                ],
                'required' => ['ref', 'name', 'label', 'type'],
                'additionalProperties' => false,
            ],
        ],
        'hook' => [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'backend' => ['type' => 'string'],
                'form_id' => ['type' => 'string'],
                'endpoint' => ['type' => 'string'],
            ],
            'required' => ['name', 'backend', 'form_id', 'endpoint'],
            'additionalProperties' => false,
        ],
        'form' => [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'fields' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'name' => ['type' => 'string'],
                            'type' => ['type' => 'string'],
                            'required' => ['type' => 'boolean'],
                            'options' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [],
                                ],
                            ],
                            'is_file' => ['type' => 'boolean'],
                            'is_multi' => ['type' => 'boolean'],
                        ],
                        'required' => ['label', 'name', 'type'],
                    ],
                    'minItems' => 1,
                ],
            ],
            'required' => ['title', 'fields'],
            'additionalProperties' => false,
        ],
    ];

    private static function validate_config($config)
    {
        $schema = [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => static::$schema,
            'required' => ['title', 'integrations', 'fields', 'form', 'hook'],
        ];

        $config = self::with_defaults($config, static::$default);

        $is_valid = rest_validate_value_from_schema($config, $schema);
        if (is_wp_error($is_valid)) {
            return $is_valid;
        }

        return rest_sanitize_value_from_schema($config, $schema);
    }

    private static function with_defaults($config)
    {
        $config = self::merge_array($config, static::$default, [
            'type' => 'object',
            'properties' => static::$schema,
        ]);

        return array_merge($config, [
            'integrations' => array_keys(Integration::integrations()),
        ]);
    }

    private static function merge_list($list, $default)
    {
        return array_values(array_unique(array_merge($list, $default)));
    }

    private static function merge_collection($collection, $default, $schema)
    {
        if (!in_array($schema['type'], ['array', 'object'])) {
            return self::merge_list($collection, $default);
        }

        if ($schema['type'] === 'object') {
            // TODO: Not harcoded column name
            foreach ($default as $item) {
                if (
                    !in_array($item['name'], array_column($collection, 'name'))
                ) {
                    $collection[] = $item;
                }
            }

            $items = [];
            foreach ($collection as $item) {
                $default_item = array_filter($default, function ($value) use (
                    $item
                ) {
                    return $value['name'] === $item['name'];
                });

                if (!empty($default_item)) {
                    $items[] = self::merge_array(
                        $item,
                        $default_item[0],
                        $schema
                    );
                } else {
                    $items[] = $item;
                }
            }

            $collection = $items;
        } elseif ($schema['type'] === 'array') {
            // TODO: Handle matrix case
        }

        return $collection;
    }

    private static function merge_array($array, $default, $schema)
    {
        foreach ($default as $key => $default_value) {
            if (empty($array[$key])) {
                $array[$key] = $default_value;
            } else {
                $value = $array[$key];
                if ($schema['properties'][$key]['type'] === 'object') {
                    if (!is_array($value) || wp_is_numeric_array($value)) {
                        $array[$key] = $default_value;
                    } else {
                        $array[$key] = self::merge_array(
                            $value,
                            $default_value,
                            $schema['properties'][$key]
                        );
                    }
                } elseif ($schema['properties'][$key]['type'] === 'array') {
                    if (!wp_is_numeric_array($value)) {
                        $array[$key] = $default_value;
                    } else {
                        $array[$key] = self::merge_collection(
                            $value,
                            $default_value,
                            $schema['properties'][$key]['items']
                        );
                    }
                }
            }
        }

        foreach ($array as $key => $value) {
            if (!isset($schema['properties'][$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    public function __construct($file, $config)
    {
        $this->api = 'rest-api';
        $this->file = $file;
        $this->name = pathinfo(basename($file))['filename'];
        $this->config = self::validate_config($config);

        add_filter(
            'forms_bridge_templates',
            function ($templates, $addon = 'rest-api') {
                if (!wp_is_numeric_array($templates)) {
                    $templates = [];
                }

                if ($addon && $addon !== $this->api) {
                    return $templates;
                }

                if (is_wp_error($this->config)) {
                    return $templates;
                }

                return array_merge($templates, [$this->name]);
            },
            10,
            2
        );
    }

    public function __get($name)
    {
        switch ($name) {
            case 'name':
                return $this->name;
            case 'file':
                return $this->file;
            case 'config':
                return $this->config;
        }

        return $this->config[$name] ?? null;
    }

    public function to_json()
    {
        return [
            'fields' => $this->config['fields'],
            'title' => $this->config['title'],
            'name' => $this->name,
        ];
    }
}

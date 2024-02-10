<?php

namespace WPCT_ERP_FORMS\WPCF7\Fields\Conditional;

use Exception;
use WPCT_ERP_FORMS\Abstract\Field as BaseField;
use WPCF7_FormTag;

require 'class-rule.php';

class Field extends BaseField
{
    static private $tag_callbacks = [
        [
            'tags' => [
                'text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*',
            ],
            'function' => 'wpcf7_text_form_tag_handler'
        ],
        [
            'tags' => [
                'textarea', 'textarea*'
            ],
            'function' => 'wpcf7_textarea_form_tag_handler'
        ],
        [
            'tags' => [
                'checkbox', 'checkbox*', 'radio'
            ],
            'function' => 'wpcf7_checkbox_form_tag_handler'
        ],
        [
            'tags' => [
                'select', 'select*'
            ],
            'function' => 'wpcf7_select_form_tag_handler'
        ],
        [
            'tags' => [
                'file', 'file*'
            ],
            'function' => 'wpcf7_file_form_tag_handler'
        ],
        [
            'tags' => [
                'date', 'date*'
            ],
            'function' => 'wpcf7_date_form_tag_handler'
        ],
        [
            'tags' => [
                'number', 'number*', 'range', 'range*'
            ],
            'function' => 'wpcf7_number_form_tag_handler'
        ],
        [
            'tags' => [
                'hidden'
            ],
            'function' => 'wpcf7_hidden_form_tag_handler'
        ],
        [
            'tags' => [
                'count'
            ],
            'function' => 'wpcf7_count_form_tag_handler'
        ],
        [
            'tags' => [
                'iban', 'iban*'
            ],
            'function' => 'wpct7_iban_form_tag_handler'
        ]
    ];

    protected function __construct()
    {
        add_filter('wpcf7_validate_conditional', [$this, 'validate_tag'], 5, 2);
        add_filter('wpcf7_validate_conditional*', [$this, 'validate_required'], 5, 2);

        add_action('wpcf7_swv_create_schema', [$this, 'add_rules'], 20, 2);

        add_filter('wpcf7_swv_available_rules', function ($rules) {
            $rules['conditional'] = 'WCPT_WPCF7_Conditional_Rule';
            return $rules;
        });
    }

    public function init()
    {
        if (!function_exists('wpcf7_add_form_tag')) return;

        wpcf7_add_form_tag(
            ['conditional', 'conditional*'],
            [$this, 'handler'],
            ['name-attr' => true]
        );
    }

    public function handler($tag)
    {
        $data = array_merge([], (array) $tag);

        $tag_type = $this->get_tag_type($tag);
        $conditions = $this->get_tag_conditions($tag);
        $standard_options = [];
        foreach ($tag->options as $option) {
            if (strstr($option, 'type:') || strstr($option, 'conditions:')) {
                continue;
            } else {
                array_push($standard_options, $option);
            }
        }

        $data['options'] = $standard_options;
        $data['type'] = $tag_type;
        $data['basetype'] = preg_replace('/\*$/', '', $tag_type);

        $base_tag = new WPCF7_FormTag($data);
        $callback = array_values(array_map(function ($tag_callback) {
            return $tag_callback['function'];
        }, array_filter(Field::$tag_callbacks, function ($tag_callback) use ($base_tag) {
            return in_array($base_tag->type, $tag_callback['tags'], true);
        })));
        if (count($callback) > 0) $callback = $callback[0];

        $html_atts = [
            'class' => 'wpcf7-form-control wpcf7-form-control-conditional',
            'aria-required' => 'false',
            'aria-invalid' => 'false',
            'data-conditions' => $conditions,
            'type' => $tag_type,
            'name' => $tag->name
        ];

        $input = call_user_func($callback, $base_tag);
        $meta = sprintf('<span hidden aria-hidden="true" class="wpcf7-form-control-conditional" data-name="%s" %s ></span>', $tag->name, wpcf7_format_atts($html_atts));
        return $input . $meta;
    }

    private function get_tag_conditions($tag)
    {
        $tag = (object) $tag;

        if ($tag->basetype !== 'conditional') return null;

        $conditions = null;
        foreach ($tag->options as $option) {
            if (strstr($option, 'conditions:')) {
                $conditions = substr($option, 11);
                break;
            }
        }

        return $conditions;
    }

    private function get_tag_basetype($tag)
    {
        $type = $this->get_tag_type($tag);
        return preg_replace('/\*$/', '', $type);
    }

    private function get_tag_type($tag)
    {
        $tag = (object) $tag;

        if ($tag->basetype !== 'conditional') return null;

        $type = null;
        foreach ($tag->options as $option) {
            if (strstr($option, 'type:')) {
                $type = substr($option, 5);
                break;
            }
        }

        return $type;
    }

    public function validate_tag($result, $tag, $required = false)
    {
        $value = $_POST[$tag->name];
        try {
            if (strlen($value) === 0 && $required) throw new Exception('Please fill out this field.');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $result->invalidate($tag, __($msg, 'wpct-erp-forms'));
        }

        return $result;
    }

    public function validate_required($result, $tag)
    {
        return $this->validate_tag($result, $tag, true);
    }

    public function add_rules($schema, $form)
    {
        $tags = $form->scan_form_tags([
            'basetype' => 'conditional'
        ]);

        $available_rules = wpcf7_swv_available_rules();
        foreach ($tags as $tag) {
            $base_type = $this->get_tag_basetype($tag);
            if ($tag->is_required()) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_required'),
                        'type' => $base_type,
                        'rule' => $available_rules['required'],
                    ])
                );
            }

            if ($base_type === 'email') {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_email'),
                        'type' => $base_type,
                        'rule' => $available_rules['email'],

                    ])
                );
            }

            if ('url' === $base_type) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_url'),
                        'type' => $base_type,
                        'rule' => $available_rules['url'],
                    ])
                );
            }

            if ('tel' === $base_type) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_tel'),
                        'type' => $base_type,
                        'rule' => $available_rules['tel'],
                    ])
                );
            }

            if ($minlength = $tag->get_minlength_option()) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'threshold' => absint($minlength),
                        'error' => wpcf7_get_message('invalid_too_short'),
                        'type' => $base_type,
                        'rule' => $available_rules['minlength'],
                    ])
                );
            }

            if ($maxlength = $tag->get_maxlength_option()) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'threshold' => absint($maxlength),
                        'error' => wpcf7_get_message('invalid_too_long'),
                        'type' => $base_type,
                        'rule' => $available_rules['maxlength'],
                    ])
                );
            }

            if ('date' === $base_type) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_date'),
                        'type' => $base_type,
                        'rule' => $available_rules['date'],
                    ])
                );

                $min = $tag->get_date_option('min');
                $max = $tag->get_date_option('max');

                if (false !== $min) {
                    $schema->add_rule(
                        wpcf7_swv_create_rule('conditional', [
                            'field' => $tag->name,
                            'threshold' => $min,
                            'error' => wpcf7_get_message('date_too_early'),
                            'type' => $base_type,
                            'rule' => $available_rules['mindate'],
                        ])
                    );
                }

                if (false !== $max) {
                    $schema->add_rule(
                        wpcf7_swv_create_rule('conditional', [
                            'field' => $tag->name,
                            'threshold' => $max,
                            'error' => wpcf7_get_message('date_too_late'),
                            'type' => $base_type,
                            'rule' => $available_rules['maxdate']
                        ])
                    );
                }
            }

            if ('file' === $base_type) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'accept' => explode(',', wpcf7_acceptable_filetypes(
                            $tag->get_option('filetypes'),
                            'attr'
                        )),
                        'error' => wpcf7_get_message('upload_file_type_invalid'),
                        'type' => $base_type,
                        'rule' => $available_rules['file'],
                    ])
                );

                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'threshold' => $tag->get_limit_option(),
                        'error' => wpcf7_get_message('upload_file_too_large'),
                        'type' => $base_type,
                        'rule' => $available_rules['maxfilesize'],
                    ])
                );
            }

            if ('number' === $base_type) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('conditional', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_number'),
                        'type' => $base_type,
                        'rule' => $available_rules['number'],
                    ])
                );

                $min = $tag->get_option('min', 'signed_num', true);
                $max = $tag->get_option('max', 'signed_num', true);

                if ('range' === $tag->basetype) {
                    if (!wpcf7_is_number($min)) {
                        $min = '0';
                    }

                    if (!wpcf7_is_number($max)) {
                        $max = '100';
                    }
                }

                if (wpcf7_is_number($min)) {
                    $schema->add_rule(
                        wpcf7_swv_create_rule('conditional', [
                            'field' => $tag->name,
                            'threshold' => $min,
                            'error' => wpcf7_get_message('number_too_small'),
                            'type' => $base_type,
                            'rule' => $available_rules['minnumber'],
                        ])
                    );
                }

                if (wpcf7_is_number($max)) {
                    $schema->add_rule(
                        wpcf7_swv_create_rule('conditional', [
                            'field' => $tag->name,
                            'threshold' => $max,
                            'error' => wpcf7_get_message('number_too_large'),
                            'type' => $base_type,
                            'rule' => $available_rules['maxnumber'],
                        ])
                    );
                }
            }
        }
    }
}

add_action('wpcf7_swv_create_schema', function (&$schema, $form) {
}, 60, 2);

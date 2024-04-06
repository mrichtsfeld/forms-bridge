<?php

namespace WPCT_ERP_FORMS\WPCF7\Fields\Files;

use WPCT_ERP_FORMS\Abstract\Field as BaseField;

require_once 'class-rule.php';

class Field extends BaseField
{
    protected function __construct()
    {
        add_action('wpcf7_swv_create_schema', [$this, 'add_rules'], 20, 2);

        add_filter('wpcf7_swv_available_rules', function ($rules) {
            $rules['files'] = 'WPCT_WPCF7_Files_Rule';
            return $rules;
        });

        add_filter('wpcf7_display_message', function ($msg, $status) {
            if ($msg) {
                return $msg;
            }

            if ($status === 'upload_files_max' || $status === 'upload_files_min') {
                return __($status, 'wpct-erp-forms');
            }
        }, 40, 2);
    }

    public function init()
    {
        wpcf7_add_form_tag(
            ['files', 'files*'],
            [$this, 'handler'],
            [
                'name-attr' => true,
                'file-uploading' => true,
            ]
        );
    }

    public function handler($tag)
    {
        if (!$tag->name) {
            return '';
        }

        $validation_error = wpcf7_get_validation_error($tag->name);

        $class = wpcf7_form_controls_class($tag->type);
        if ($validation_error) {
            $class .= ' wpcf7-not-valid';
        }

        $atts = [
            'class' => $tag->get_class_option($class),
            'id' => $tag->get_id_option(),
            'accept' => wpcf7_acceptable_filetypes(
                $tag->get_option('filetypes'),
                'attr'
            ),
            'capture' => $tag->get_option('capture', '(user|environment)', true),
            'tabindex' => $tag->get_option('tabindex', 'signed_int', true),
            'name' => $tag->name,
            'multiple' => 'multiple',
            'type' => 'file',
        ];

        if ($tag->is_required()) {
            $atts['aria-required'] = 'true';
        }

        if ($validation_error) {
            $atts['aria-invalid'] = 'true';
            $atts['aria-describedby'] = wpcf7_get_validation_error_reference(
                $tag->name
            );
        } else {
            $atts['aria-invalid'] = 'false';
        }

        $html = sprintf(
            '<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s />%3$s</span>',
            esc_attr($tag->name),
            wpcf7_format_atts($atts),
            $validation_error
        );

        return $html;
    }

    public function add_rules($schema, $form)
    {
        $tags = $form->scan_form_tags([
            'basetype' => ['files'],
        ]);

        foreach ($tags as $tag) {
            if ($tag->is_required()) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('requiredfile', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_required'),
                    ])
                );
            }

            $schema->add_rule(
                wpcf7_swv_create_rule('files', [
                    'field' => $tag->name,
                    'accept' => explode(',', wpcf7_acceptable_filetypes(
                        $tag->get_option('filetypes'),
                        'attr'
                    )),
                    'error' => wpcf7_get_message('upload_file_type_invalid'),
                    'minfiles' => (int) $tag->get_option('minfiles', 'int', true),
                    'maxfiles' => (int) $tag->get_option('maxfiles', 'int', true),
                ])
            );

            $schema->add_rule(
                wpcf7_swv_create_rule('maxfilesize', [
                    'field' => $tag->name,
                    'threshold' => $tag->get_limit_option(),
                    'error' => wpcf7_get_message('upload_file_too_large'),
                ])
            );
        }
    }
}

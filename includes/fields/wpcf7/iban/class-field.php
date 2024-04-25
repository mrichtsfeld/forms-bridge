<?php

namespace WPCT_ERP_FORMS\WPCF7\Fields\Iban;

use WPCT_ERP_FORMS\Abstract\Field as BaseField;

require_once 'class-rule.php';

class Field extends BaseField
{
    protected function __construct()
    {
        add_action('wpcf7_swv_create_schema', [$this, 'add_rules'], 20, 2);

        add_filter('wpcf7_swv_available_rules', function ($rules) {
            $rules['iban'] = 'WPCT_WPCF7_Iban_Rule';
            return $rules;
        });

        add_filter('wpcf7_display_message', function ($msg, $status) {
            if ($msg) {
                return $msg;
            }

            if ($status === 'invalid_iban') {
                return __($status, 'wpct-erp-forms');
            }
        }, 40, 2);
    }

    public function init()
    {
        wpcf7_add_form_tag(
            ['iban', 'iban*'],
            [$this, 'handler'],
            ['name-attr' => true]
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
            'tabindex' => $tag->get_option('tabindex', 'signed_int', true),
            'name' => $tag->name,
            'type' => 'text',
        ];

        if ($tag->is_required()) {
            $atts['aria-required'] = 'true';
        }

        if ($validation_error) {
            $atts['aria-invalid'] = 'true';
            $atts['aria-describedby'] = wpcf7_get_validation_error_reference(
                $tag->name
            );
        }

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s />%3$s</span>',
			esc_attr($tag->name),
			wpcf7_format_atts($atts),
			$validation_error,
		);

        return $html . $this->add_script($tag);
    }

    public function add_rules($schema, $form)
    {
        $tags = $form->scan_form_tags([
            'basetype' => 'iban'
        ]);

        foreach ($tags as $tag) {
            $schema->add_rule(
                wpcf7_swv_create_rule('iban', [
                    'field' => $tag->name,
                    'error' => wpcf7_get_message('invalid_iban')
                ])
            );

            if ($tag->is_required()) {
                $schema->add_rule(
                    wpcf7_swv_create_rule('required', [
                        'field' => $tag->name,
                        'error' => wpcf7_get_message('invalid_required')
                    ])
                );
            }
        }
    }

	private function add_script($tag)
	{
		ob_start();
        ?>
		<script>
		const input = document.currentScript.parentElement.querySelector('input[name="<?= $tag->name ?>"]');
		input.addEventListener("input", ({ target }) => {
			const value = String(target.value);
			const chars = value.split("").filter((c) => c !== " ");
			target.value = chars.reduce((repr, char, i) => {
				if (i % 4 === 0) {
					char = " " + char;
				}
				return repr + char;
			});
		});
		</script>
		<?php
        return ob_get_clean();

	}
}

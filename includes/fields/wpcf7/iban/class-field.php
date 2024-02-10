<?php

namespace WPCT_ERP_FORMS\WPCF7\Fields\Iban;

use WPCT_ERP_FORMS\Abstract\Field as BaseField;
use Exception;

class Field extends BaseField
{
    /**
     * @var array @type Country codes
     */
    private $_countries = [
        'al' => 28,
        'ad' => 24,
        'at' => 20,
        'az' => 28,
        'bh' => 22,
        'be' => 16,
        'ba' => 20,
        'br' => 29,
        'bg' => 22,
        'cr' => 21,
        'hr' => 21,
        'cy' => 28,
        'cz' => 24,
        'dk' => 18,
        'do' => 28,
        'ee' => 20,
        'fo' => 18,
        'fi' => 18,
        'fr' => 27,
        'ge' => 22,
        'de' => 22,
        'gi' => 23,
        'gr' => 27,
        'gl' => 18,
        'gt' => 28,
        'hu' => 28,
        'is' => 26,
        'ie' => 22,
        'il' => 23,
        'it' => 27,
        'jo' => 30,
        'kz' => 20,
        'kw' => 30,
        'lv' => 21,
        'lb' => 28,
        'li' => 21,
        'lt' => 20,
        'lu' => 20,
        'mk' => 19,
        'mt' => 31,
        'mr' => 27,
        'mu' => 30,
        'mc' => 27,
        'md' => 24,
        'me' => 22,
        'nl' => 18,
        'no' => 15,
        'pk' => 24,
        'ps' => 29,
        'pl' => 28,
        'pt' => 25,
        'qa' => 29,
        'ro' => 24,
        'sm' => 27,
        'sa' => 24,
        'rs' => 22,
        'sk' => 24,
        'si' => 19,
        'es' => 24,
        'se' => 24,
        'ch' => 21,
        'tn' => 24,
        'tr' => 26,
        'ae' => 23,
        'gb' => 22,
        'vg' => 24,
    ];

    /**
     * @var array $type Char codes
     */
    private $_chars = [
        'a' => 10,
        'b' => 11,
        'c' => 12,
        'd' => 13,
        'e' => 14,
        'f' => 15,
        'g' => 16,
        'h' => 17,
        'i' => 18,
        'j' => 19,
        'k' => 20,
        'l' => 21,
        'm' => 22,
        'n' => 23,
        'o' => 24,
        'p' => 25,
        'q' => 26,
        'r' => 27,
        's' => 28,
        't' => 29,
        'u' => 30,
        'v' => 31,
        'w' => 32,
        'x' => 33,
        'y' => 34,
        'z' => 35
    ];

    protected function __construct()
    {
        add_filter('wpcf7_validate_iban*', [$this, 'validate'], 20, 2);
        add_filter('wpcf7_validate_iban', [$this, 'validate'], 20, 2);
        add_action('wpcf7_swv_create_schema', [$this, 'add_rules'], 20, 2);
    }

    public function init()
    {
        if (!function_exists('wpcf7_add_form_tag')) return;

        wpcf7_add_form_tag(
            ['iban', 'iban*'],
            [$this, 'handler'],
            ['name-attr' => true]
        );
    }

    public function handler($tag)
    {
        return Field::static_handler($tag);
    }

    static public function static_handler($tag)
    {
        $atts = [
            'class' => 'wpcf7-form-control',
            'aria-required' => 'true',
            'aria-invalid' => 'false',
            'type' => 'text',
            'name' => $tag->name,
        ];

        $input = sprintf('<span class="wpcf7-form-control-wrap" data-name="%s"><input %s />', $tag->name, wpcf7_format_atts($atts));
        return $input;
    }

    public function validate_required($result, $tag)
    {
        return $this->validate($result, $tag, true);
    }

    public function validate($result, $tag, $required = false)
    {
        $err_msg = 'Invalid IBAN format.';
        $value = $_POST[$tag->name];
        try {
            if (strlen($value) === 0 && $required) throw new Exception('Please fill out this field.');

            if (strlen($value) < 5) throw new Exception($err_msg);
            $value = strtolower(str_replace(' ', '', $value));


            $country_exists = array_key_exists(substr($value, 0, 2), $this->_countries);
            $country_conform = strlen($value) == $this->_countries[substr($value, 0, 2)];

            if (!($country_exists && $country_conform)) throw new Exception($err_msg);

            $moved_char = substr($value, 4) . substr($value, 0, 4);
            $move_char_array = str_split($moved_char);
            $new_string = '';

            foreach ($move_char_array as $key => $val) {
                if (!is_numeric($move_char_array[$key])) {
                    if (!isset($this->_chars[$val])) throw new Exception($err_msg);
                    $move_char_array[$key] = $this->_chars[$val];
                }

                $new_string .= $move_char_array[$key];
            }

            if (bcmod($new_string, '97') != 1) {
                throw new Exception($err_msg);
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $result->invalidate($tag, __($msg, 'wpct-erp-forms'));
        }

        return $result;
    }

    public function add_rules($schema, $form)
    {
        $tags = $form->scan_form_tags([
            'basetype' => 'iban'
        ]);

        foreach ($tags as $tag) {
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
}

function wpcf7_iban_form_tag_handler($tag)
{
    return Field::static_handler($tag);
}

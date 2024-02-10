<?php

class WCPT_WPCF7_Conditional_Rule extends WPCF7_SWV_Rule
{
    const rule_name = 'conditional';

    static private $empty_values = [
        'date' => '0001-01-01',
        'tel' => '+000000000',
        'text' => 'wpct-empty',
        'number' => -1234567890,
        'email' => 'wpct-empty@mail.com',
        'url' => 'https://wpct-empty.com',
        'checkbox' => ['wpct-empty'],
        'select' => ['wpct-empty'],
        'radio' => ['wpct-empty']
    ];

    public function matches($context)
    {
        if (false === parent::matches($context)) {
            return false;
        }

        return true;
    }

    public function validate($context)
    {
        $field = $this->get_property('field');
        $type = $this->get_property('type');
        $input = isset($_POST[$field]) ? $_POST[$field] : '';
        $input = wpcf7_array_flatten($input);
        $input = wpcf7_exclude_blank($input);

        $is_empty = true;
        foreach ($input as $i) {
            $is_empty = $this->is_empty($i, $type);
        }

        if ($is_empty) return true;

        $rule_class = $this->get_property('rule');
        $props = $this->to_array();
        $rule = new $rule_class($props);

        return $rule->validate($context);
    }

    private function is_empty($value, $type)
    {
        if (is_array($value)) return $this->is_empty_array($value, $type);

        $empty = static::$empty_values[$type];
        return $empty === $value;
    }

    private function is_empty_array($values, $type)
    {
        $empties = (array) static::$empty_values[$type];
        if (count($values) !== count($empties)) {
            return false;
        }

        $is_empty = true;
        for ($i = 0; $i < count($values); $i++) {
            $is_empty = $is_empty && $values[$i] === $empties[$i];
        }

        return $is_empty;
    }
}

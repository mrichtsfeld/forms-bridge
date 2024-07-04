<?php

namespace WPCT_ERP_FORMS;

use WPCT_ABSTRACT\Settings as BaseSettings;

class Settings extends BaseSettings
{
    public static function get_forms()
    {
        global $wpdb;
        if (apply_filters('wpct_is_plugin_active', false, 'contact-form-7/wp-contact-form-7.php')) {
            return $wpdb->get_results("SELECT id, post_title title FROM {$wpdb->prefix}posts WHERE post_type = 'wpcf7_contact_form' AND post_status = 'publish'");
        } elseif (apply_filters('wpct_is_plugin_active', false, 'gravityforms/gravityforms.php')) {
            return $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}gf_form WHERE is_active = 1 AND is_trash = 0");
        }
    }

    public function register()
    {
        $setting_name = $this->group_name . '_general';
        $this->register_setting($setting_name, [
            'notification_receiver' => 'admin@' . parse_url(get_bloginfo('url'))['host'],
            'api_protocol' => 'JSON-RPC',
        ]);

        $setting_name = $this->group_name . '_api';
        $this->register_setting($setting_name, [
            'endpoints' => [
                [
                    'endpoint' => null,
                    'form_id' => null,
                    'ref' => null
                ]
            ]
        ]);
    }

    protected function input_render($setting, $field, $value)
    {
        if (preg_match('/^endpoints.*form_id$/', $field)) {
            return $this->render_forms_dropdown($setting, $field, $value);
        } elseif ($field === 'api_protocol') {
            return $this->render_protocol_radio($setting, $field, $value);
        }

        return parent::input_render($setting, $field, $value);
    }

    private function render_protocol_radio($setting, $field, $value)
    {
        $options = ['JSON REST API', 'JSON-RPC'];
        $fieldset = '<fieldset class="structure-selection">';
        foreach ($options as $option) {
            $fieldset .= "<div class='row'><input type='radio' id=name='{$setting}[{$field}]' name='{$setting}[{$field}]' value={$option}><div><label for=name='{$setting}[{$field}]'>Description</div></div>";
        }

        $fieldset .= '</fieldset>';
        return $fieldset;
    }

    private function render_forms_dropdown($setting, $field, $value)
    {
        $forms = self::get_forms();
        $options = array_map(function ($form) use ($value) {
            $selected = $form->id == $value ? 'selected' : '';
            return "<option value='{$form->id}' {$selected}>{$form->title}</option>";
        }, $forms);
        return "<select name='{$setting}[{$field}]'>" . implode('', $options) . '</select>';
    }
}

<?php

add_filter('gform_field_value_source_xml_id', 'wpct_forms_ce_populate_source_xml_id', 10, 3);
function wpct_forms_ce_populate_source_xml_id($value, $field)
{
    if ($value) return $value;
    $form = GFAPI::get_form($field->formId);
    return wpct_forms_ce_option_getter('wpct_forms_ce_forms', $form['id']);
}

add_filter('gform_field_value_odoo_company_id', 'wpct_forms_ce_populate_odoo_company_id');
function wpct_forms_ce_populate_odoo_company_id($value)
{
    if ($value) return $value;
    return wpct_forms_ce_option_getter('wpct_forms_ce_general', 'coord_id');
}

add_filter('gform_field_value_current_lang', 'wpct_forms_ce_populate_current_lang');
function wpct_forms_ce_populate_current_lang($value)
{
    $current_lang = apply_filters("wpml_current_language", null);
    if (!$current_lang) $current_lang = 'ca';
    return $current_lang;
}

<?php



function wpct_erp_forms_add_cord_id($form_values)
{
    if (!isset($form_values['company_id']) || !$form_values['company_id']) {
        $form_values['company_id'] = wpct_erp_forms_option_getter('wpct_erp_forms_general', 'coord_id');
    }

    return $form_values;
}

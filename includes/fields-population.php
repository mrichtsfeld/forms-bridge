<?php

/* Placed on submissions.php to avoid overwritings of shortcodes params
/* add_filter('gform_field_value_odoo_company_id', 'wpct_forms_ce_populate_odoo_company_id'); */
/* function wpct_forms_ce_populate_odoo_company_id($value) */
/* { */
/*     if ($value) return $value; */
/*     return wpct_forms_ce_option_getter('wpct_forms_ce_general', 'coord_id'); */
/* } */

add_filter('gform_field_value_current_lang', 'wpct_forms_ce_populate_current_lang');
function wpct_forms_ce_populate_current_lang($value)
{
    if ($value) {
        $locale = wpct_forms_ce_format_current_lang($value);
    } else {
        $language = apply_filters('wpml_post_language_details', null);

        if ($language) {
            $locale = $language['locale'];
        } else {
            $locale = 'ca_ES';
        }
    }

    return $locale;
}

function wpct_forms_ce_format_current_lang($code)
{
    $languages = apply_filters('wpml_active_languages', null);
    if ($languages && isset($languages[$code])) {
        return $languages[$code]['default_locale'];
    }

    return $code;
}

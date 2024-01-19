<?php

/**
 * Plugin Name:     WPCT CRM Forms
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-crm-forms
 * Description:     Plugin to wire gravity forms submissions with Odoo CRM Leads module
 * Author:          Còdec Cooperativa
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     wpct-erp-forms
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         WPCT_ERP_Forms
 */

/* Settings */
require_once 'includes/options/index.php';

/* Webhooks */
require_once 'includes/attachments.php';

/* Fields population */
require_once 'includes/fields-population.php';

/* Integrations */
require_once 'includes/integrations/index.php';

/* Dependencies */
add_filter('wpct_dependencies_check', function ($dependencies) {
    // $dependencies['Gravity Forms'] = '<a href="https://www.gravityforms.com/">Gravity Forms</a>';
    $dependencies['Wpct Odoo Connect'] = '<a href="https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-odoo-connect">Wpct Odoo Connect</a>';
    return $dependencies;
});

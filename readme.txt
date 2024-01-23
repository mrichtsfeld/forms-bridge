=== Wpct ERP Forms ===
Contributors: codeccoop, coopdevs
Tags: forms, erp, crm
Requires at least: 6.3.1
Tested up to: 6.3.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Form submissions to ERP backends

== Description ==

Bridge WP form builder plugins' submissions to a ERP backend. The plugin should work with [WPCT Odoo Connect](https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-odoo-connect) to perform http requests.

The plugin has two integrations, with GravityForms and with Contactform7. Choose your preferred form builder.

== Hooks ==

Filters:
* wpct_erp_forms_before_submission (array $submission, array $form) -> array $submission: Filter form submission
* wpct_erp_forms_payload (array $payload) -> array $payload: Filter submission payload
* wpct_erp_forms_endpoints (array $endpoints) -> array $endpoints: Filter endpoints array

Actions:
* wpct_erp_forms_on_failure (array $submission, array $form): Fired on submission failure
* wpct_erp_forms_after_submission (array $submission, array $form): Fired on successfully submited

== Changelog ==

= 1.0.0 =
* Initial commit before fork from [WPCT Forms CE](https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-forms-ce)

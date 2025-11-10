<?php
/**
 * Class WPFormsTest
 *
 * @package forms-bridge-tests
 */

/**
 * WPForms integration test case.
 */
class WPFormsTest extends BaseIntegrationTest {
	protected static function get_forms() {
		return array_filter( (array) wpforms()->obj( 'form' )->get() );
	}

	protected static function add_form( $config ) {
	}

	protected static function delete_form( $form ) {
	}
}

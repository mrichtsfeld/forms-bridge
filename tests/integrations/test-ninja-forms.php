<?php
/**
 * Class NinjaFormsTest
 *
 * @package forms-bridge-tests
 */

/**
 * Ninja Forms integration test case.
 */
class NinjaFormsTest extends BaseIntegrationTest {
	protected static function get_forms() {
		return Ninja_Forms()->form()->get_forms();
	}

	protected static function add_form( $config ) {
	}

	protected static function delete_form( $form ) {
	}
}
